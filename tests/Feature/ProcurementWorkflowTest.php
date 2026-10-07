<?php

namespace Tests\Feature;

use App\Models\PurchaseOrder;
use App\Models\Requisition;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProcurementWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_supplier(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('suppliers.store'), [
                'number' => '3',
                'name' => 'BBVA México',
                'rfc' => 'bba830831lj2',
                'curp' => '',
                'address' => 'Av. Paseo de la Reforma',
                'city' => 'Ciudad de México',
                'phone' => '5550000000',
                'representative' => 'BBVA México, S.A.',
                'cellphone' => '5550000001',
                'economic_activity' => 'Servicios financieros',
                'iva_percentage' => '16',
                'status' => 'Activo',
                'it_capture' => 'CRODE de Chihuahua',
            ])
            ->assertRedirect(route('suppliers.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('suppliers', [
            'number' => '3',
            'name' => 'BBVA México',
            'rfc' => 'BBA830831LJ2',
            'iva_percentage' => '16.00',
        ]);
    }

    public function test_authenticated_user_can_save_requisition_and_purchase_order_documents(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $supplier = Supplier::create([
            'number' => '3',
            'name' => 'BBVA México',
            'rfc' => 'BBA830831LJ2',
            'iva_percentage' => 16,
            'status' => 'Activo',
        ]);

        $this->actingAs($user)
            ->post(route('orders.store'), [
                'requisition_folio' => 'REQ-2026-001',
                'requisition_created_on' => '2026-10-07',
                'requisition_requested_on' => '2026-10-07',
                'requisition_amount' => '3650.37',
                'item_number' => '33901',
                'requisition_sp' => 'SP-01',
                'requisition_ppp' => 'Observación',
                'requisition_sup' => 'Supervisión',
                'requisition_dir' => 'Dirección',
                'requisition_process' => 'Proceso',
                'requisition_rf' => 'RF',
                'budget_key' => 'REQ/IP/SPV/2026',
                'quantity' => '1',
                'unit' => 'Pieza',
                'unit_cost' => '3650.37',
                'description' => 'Equipo de oficina',
                'department' => 'Recursos Financieros',
                'needed_by' => '2026-10-26',
                'justification' => 'Necesidad del área',
                'supplier_id' => $supplier->id,
                'quote_email' => 'ventas@example.com',
                'quote_amount' => '3650.37',
                'delivery_location' => 'CRODE de Chihuahua',
                'receiver' => 'Persona receptora',
                'receiver_phone' => '5550000000',
                'budget_confirmed' => 'on',
                'review_confirmed' => 'on',
                'purchase_order_folio' => 'OC-2026-001',
                'purchase_order_date' => '2026-10-07',
                'purchase_order_partida' => '33901',
                'purchase_order_iva' => '16',
                'purchase_order_rms' => '21601',
                'purchase_order_rf' => 'Si',
                'requisition_pdf' => UploadedFile::fake()->create('requisition.pdf', 20, 'application/pdf'),
                'requisition_xml' => UploadedFile::fake()->create('requisition.xml', 1, 'text/xml'),
                'purchase_order_pdf' => UploadedFile::fake()->create('purchase-order.pdf', 20, 'application/pdf'),
                'purchase_order_xml' => UploadedFile::fake()->create('purchase-order.xml', 1, 'text/xml'),
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHas('success');

        $requisition = Requisition::where('folio', 'REQ-2026-001')->firstOrFail();
        $purchaseOrder = PurchaseOrder::where('folio', 'OC-2026-001')->firstOrFail();

        $this->assertSame($user->id, $requisition->user_id);
        $this->assertSame($requisition->id, $purchaseOrder->requisition_id);
        $this->assertSame($supplier->id, $purchaseOrder->supplier_id);
        $this->assertSame('3650.37', $requisition->amount);
        $this->assertStringStartsWith('requisitions/', $requisition->pdf_path);
        $this->assertStringStartsWith('requisitions/', $requisition->xml_path);
        $this->assertStringStartsWith('purchase-orders/', $purchaseOrder->pdf_path);
        $this->assertStringStartsWith('purchase-orders/', $purchaseOrder->xml_path);

        Storage::disk('local')->assertExists([
            $requisition->pdf_path,
            $requisition->xml_path,
            $purchaseOrder->pdf_path,
            $purchaseOrder->xml_path,
        ]);
    }

    public function test_home_and_supplier_catalog_are_separate_screens(): void
    {
        $supplier = Supplier::create([
            'number' => '3',
            'name' => 'BBVA México',
            'rfc' => 'BBA830831LJ2',
            'iva_percentage' => 16,
            'status' => 'Activo',
        ]);

        $this->actingAs(User::factory()->create([
            'department_id' => Department::where('slug', 'departments')->value('id'),
        ]))
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Catálogo de proveedores')
            ->assertDontSee('id="add-supplier-button"', false)
            ->assertSee('Crear nueva orden')
            ->assertSee('value="'.$supplier->id.'"', false);

        $this->get(route('suppliers.index'))
            ->assertOk()
            ->assertSee('Catálogo de proveedores')
            ->assertSee('Agregar proveedor')
            ->assertSee('BBVA México');
    }
}
