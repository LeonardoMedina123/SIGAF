<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\PurchaseOrder;
use App\Models\Requisition;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProcurementWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_supplier(): void
    {
        $this->from(route('suppliers.index'))
            ->actingAs(User::factory()->create())
            ->post(route('suppliers.store'), [
                'number' => '3',
                'name' => 'BBVA México',
                'rfc' => 'bba830831lj2',
                'person_type' => 'moral',
                'tax_regime' => '601',
                'cfdi_use' => 'G03',
                'contact_email' => 'compras@bbva.example',
                'curp' => '',
                'address' => 'Av. Paseo de la Reforma',
                'city' => 'Ciudad de México',
                'phone' => '5550000000',
                'representative' => 'Contacto BBVA',
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
            'person_type' => 'moral',
            'tax_regime' => '601',
            'cfdi_use' => 'G03',
            'contact_email' => 'compras@bbva.example',
            'iva_percentage' => '16.00',
        ]);
    }

    public function test_authenticated_user_can_save_requisition_and_purchase_order(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $supplier = Supplier::create([
            'number' => '3',
            'name' => 'BBVA México',
            'rfc' => 'BBA830831LJ2',
            'person_type' => 'moral',
            'tax_regime' => '601',
            'cfdi_use' => 'G03',
            'contact_email' => 'compras@bbva.example',
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
                'requisition_pdf' => UploadedFile::fake()->create('requisition.pdf', 20, 'application/pdf'),
                'supplier_id' => $supplier->id,
                'quote_email' => 'compras@bbva.example',
                'quote_amount' => '3650.37',
                'delivery_location' => 'CRODE de Chihuahua',
                'receiver' => 'Persona receptora',
                'receiver_phone' => '5550000000',
                'review_confirmed' => 'on',
                'purchase_order_folio' => 'OC-2026-001',
                'purchase_order_date' => '2026-10-07',
                'purchase_order_partida' => '33901',
                'purchase_order_iva' => '16',
                'purchase_order_rms' => '21601',
                'purchase_order_rf' => 'Si',
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
        $this->assertSame('2026-10-07', $requisition->created_on->format('Y-m-d'));
        $this->assertSame('2026-10-07', $requisition->requested_on->format('Y-m-d'));
        $this->assertSame('33901', $requisition->partida);
        $this->assertSame('Observación', $requisition->ppp);
        $this->assertStringStartsWith('requisitions/', $requisition->pdf_path);
        $this->assertStringStartsWith('purchase-orders/', $purchaseOrder->pdf_path);
        $this->assertStringStartsWith('purchase-orders/', $purchaseOrder->xml_path);

        Storage::disk('local')->assertExists([
            $requisition->pdf_path,
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
            'person_type' => 'moral',
            'tax_regime' => '601',
            'cfdi_use' => 'G03',
            'contact_email' => 'compras@bbva.example',
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
            ->assertSee('value="'.$supplier->id.'"', false)
            ->assertSee('data-purchase-order-paste', false)
            ->assertSee('Pega una fila copiada desde el campo Folio', false)
            ->assertSee('name="purchase_order_pdf"', false)
            ->assertSee('name="purchase_order_xml"', false);

        $this->get(route('suppliers.index'))
            ->assertOk()
            ->assertSee('Catálogo de proveedores')
            ->assertSee('Agregar proveedor')
            ->assertSee('BBVA México');
    }

    public function test_supplier_catalog_requires_and_saves_fiscal_and_contact_details(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('suppliers.store'), [
                'number' => '4',
                'name' => 'Proveedor con datos fiscales',
                'rfc' => 'ABC010203AB1',
                'person_type' => 'fisica',
                'tax_regime' => '612',
                'cfdi_use' => 'G03',
                'representative' => 'Ana Contacto',
                'contact_email' => 'ana@example.com',
                'iva_percentage' => '16',
                'status' => 'Activo',
            ])
            ->assertRedirect(route('suppliers.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('suppliers', [
            'number' => '4',
            'rfc' => 'ABC010203AB1',
            'person_type' => 'fisica',
            'tax_regime' => '612',
            'cfdi_use' => 'G03',
            'representative' => 'Ana Contacto',
            'contact_email' => 'ana@example.com',
        ]);
    }

    public function test_supplier_catalog_rejects_a_tax_regime_outside_the_sat_catalog(): void
    {
        $this->from(route('suppliers.index'))
            ->actingAs(User::factory()->create())
            ->post(route('suppliers.store'), [
                'number' => '5',
                'name' => 'Proveedor inválido',
                'rfc' => 'XYZ010203AB1',
                'person_type' => 'moral',
                'tax_regime' => '999',
                'cfdi_use' => 'G03',
                'representative' => 'Contacto',
                'contact_email' => 'contacto@example.com',
                'iva_percentage' => '16',
                'status' => 'Activo',
            ])
            ->assertRedirect(route('suppliers.index'))
            ->assertSessionHasErrors('tax_regime');

        $this->assertDatabaseMissing('suppliers', ['number' => '5']);
    }

    public function test_requisition_capture_form_contains_only_the_requested_fields(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->get(route('home'))
            ->assertOk();

        foreach ([
            'requisition_folio',
            'requisition_created_on',
            'requisition_requested_on',
            'requisition_amount',
            'item_number',
            'requisition_sp',
            'requisition_ppp',
            'requisition_sup',
            'requisition_dir',
            'requisition_process',
            'requisition_rf',
        ] as $field) {
            $response->assertSee('name="'.$field.'"', false);
        }

        foreach ([
            'budget_key',
            'quantity',
            'unit',
            'unit_cost',
            'description',
            'department',
            'needed_by',
            'justification',
            'budget_confirmed',
            'requisition_xml',
        ] as $field) {
            $response->assertDontSee('name="'.$field.'"', false);
        }

        $response->assertSee('name="requisition_pdf"', false);
    }

    public function test_supplier_email_is_available_to_autofill_the_quote_contact_field(): void
    {
        $supplier = Supplier::create([
            'number' => '6',
            'name' => 'Proveedor con correo',
            'rfc' => 'DEF010203AB1',
            'contact_email' => 'cotizaciones@example.com',
            'iva_percentage' => 16,
            'status' => 'Activo',
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('home'))
            ->assertOk()
            ->assertSee('data-email="cotizaciones@example.com"', false)
            ->assertSee('name="quote_email"', false);
    }
}
