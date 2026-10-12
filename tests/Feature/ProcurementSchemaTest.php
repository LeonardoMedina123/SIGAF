<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentComplement;
use App\Models\PurchaseOrder;
use App\Models\Requisition;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProcurementSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_payment_and_complement_are_connected_by_foreign_keys(): void
    {
        $user = User::factory()->create();
        $supplier = Supplier::create([
            'number' => '3',
            'name' => 'Proveedor de prueba',
            'rfc' => 'ABC010203AB1',
            'iva_percentage' => 16,
            'status' => 'Activo',
        ]);
        $requisition = Requisition::create([
            'folio' => 'REQ-SCHEMA-001',
            'created_on' => '2026-10-08',
            'requested_on' => '2026-10-08',
            'amount' => 1000,
            'partida' => '33901',
            'sp' => 'SP-001',
            'ppp' => 'PPP-001',
            'sup' => 'SUP-001',
            'dir' => 'DIR-001',
            'process' => 'Proceso de prueba',
            'rf' => 'RF-001',
            'pdf_path' => 'requisitions/test.pdf',
            'user_id' => $user->id,
        ]);
        $purchaseOrder = PurchaseOrder::create([
            'folio' => 'OC-SCHEMA-001',
            'order_date' => '2026-10-08',
            'requisition_id' => $requisition->id,
            'supplier_id' => $supplier->id,
            'amount' => 1000,
            'partida' => '33901',
            'iva_percentage' => 16,
            'contact_email' => 'proveedor@example.com',
            'delivery_location' => 'Almacén',
            'receiver' => 'Persona receptora',
            'receiver_phone' => '5550000000',
            'pdf_path' => 'purchase-orders/test.pdf',
            'xml_path' => 'purchase-orders/test.xml',
        ]);
        $invoice = Invoice::create([
            'folio' => 'FAC-SCHEMA-001',
            'supplier_id' => $supplier->id,
            'uuid' => '123e4567-e89b-12d3-a456-426614174000',
            'issuer_tax_regime' => '601',
            'receiver_tax_regime' => '603',
            'payment_form' => '03',
            'payment_method' => 'PUE',
            'cfdi_use' => 'G03',
        ]);
        $payment = Payment::create([
            'invoice_folio' => $invoice->folio,
        ]);
        $complement = PaymentComplement::create([
            'purchase_order_id' => $purchaseOrder->id,
            'payment_id' => $payment->id,
            'supplier_id' => $supplier->id,
            'invoice_folio' => $invoice->folio,
        ]);

        $this->assertSame('FAC-SCHEMA-001', $invoice->getKey());
        $this->assertSame($supplier->id, $invoice->supplier->id);
        $this->assertSame($invoice->folio, $payment->invoice->folio);
        $this->assertSame($purchaseOrder->id, $complement->purchaseOrder->id);
        $this->assertSame($payment->id, $complement->payment->id);
        $this->assertSame($supplier->id, $complement->supplier->id);
        $this->assertSame($invoice->folio, $complement->invoice->folio);
    }

    public function test_invoice_requires_an_existing_supplier(): void
    {
        $this->expectException(QueryException::class);

        Invoice::create([
            'folio' => 'FAC-SCHEMA-002',
            'supplier_id' => 999999,
        ]);
    }

    public function test_supplier_table_stores_tax_profile_and_contact_email(): void
    {
        $this->assertTrue(Schema::hasColumns('suppliers', [
            'person_type',
            'tax_regime',
            'cfdi_use',
            'contact_email',
        ]));
    }

    public function test_requisition_table_contains_only_capture_fields_and_system_columns(): void
    {
        $this->assertSame([
            'id',
            'folio',
            'created_on',
            'requested_on',
            'amount',
            'partida',
            'sp',
            'ppp',
            'sup',
            'dir',
            'process',
            'rf',
            'pdf_path',
            'user_id',
            'created_at',
            'updated_at',
        ], Schema::getColumnListing('requisitions'));
    }
}
