<?php

namespace Tests\Feature;

use App\Models\PurchaseOrder;
use App\Models\Requisition;
use App\Models\Supplier;
use App\Models\UploadedDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportLegacyDocumentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_local_documents_and_clears_the_legacy_paths(): void
    {
        Storage::fake('local');
        $requisitionContent = 'contenido de requisicion';
        $invoiceContent = 'contenido de factura';
        Storage::disk('local')->put('legacy/requisition.pdf', $requisitionContent);
        Storage::disk('local')->put('legacy/invoice.pdf', $invoiceContent);

        [$requisition, $purchaseOrder] = $this->createLegacyDocuments([
            'pdf_path' => 'legacy/requisition.pdf',
        ], [
            'invoice_pdf_path' => 'legacy/invoice.pdf',
            'payment_complement_path' => 'legacy/already-in-database.pdf',
        ]);
        UploadedDocument::replaceContentFor(
            $purchaseOrder,
            'payment_complement',
            'complemento.pdf',
            'application/pdf',
            'contenido ya almacenado',
        );

        $this->artisan('app:import-legacy-documents')
            ->assertSuccessful()
            ->expectsOutputToContain('Importados: 2; ya estaban en la base de datos: 1; archivos locales faltantes: 0.');

        $this->assertNull($requisition->fresh()->pdf_path);
        $this->assertNull($purchaseOrder->fresh()->invoice_pdf_path);
        $this->assertNull($purchaseOrder->fresh()->payment_complement_path);
        $this->assertDatabaseHas('uploaded_documents', [
            'requisition_id' => $requisition->id,
            'kind' => 'requisition_pdf',
            'original_name' => 'requisition.pdf',
            'mime_type' => 'application/pdf',
            'content' => $requisitionContent,
        ]);
        $this->assertDatabaseHas('uploaded_documents', [
            'purchase_order_id' => $purchaseOrder->id,
            'kind' => 'invoice_pdf',
            'original_name' => 'invoice.pdf',
            'mime_type' => 'application/pdf',
            'content' => $invoiceContent,
        ]);
        $this->assertSame(
            'contenido ya almacenado',
            $purchaseOrder->uploadedDocuments()->where('kind', 'payment_complement')->firstOrFail()->content,
        );
    }

    public function test_it_keeps_legacy_paths_when_the_local_file_is_missing(): void
    {
        Storage::fake('local');
        [, $purchaseOrder] = $this->createLegacyDocuments([], [
            'invoice_pdf_path' => 'legacy/missing-invoice.pdf',
        ]);

        $this->artisan('app:import-legacy-documents')
            ->assertFailed()
            ->expectsOutputToContain('archivos locales faltantes: 1.');

        $this->assertSame('legacy/missing-invoice.pdf', $purchaseOrder->fresh()->invoice_pdf_path);
        $this->assertDatabaseMissing('uploaded_documents', [
            'purchase_order_id' => $purchaseOrder->id,
            'kind' => 'invoice_pdf',
        ]);
    }

    /**
     * @param  array<string, string>  $requisitionPaths
     * @param  array<string, string>  $purchaseOrderPaths
     * @return array{Requisition, PurchaseOrder}
     */
    private function createLegacyDocuments(array $requisitionPaths, array $purchaseOrderPaths): array
    {
        $user = User::factory()->create();
        $supplier = Supplier::create([
            'number' => '101',
            'name' => 'Proveedor de prueba',
            'rfc' => 'ABC010203AB1',
            'person_type' => 'moral',
            'tax_regime' => '601',
            'cfdi_use' => 'G03',
            'contact_email' => 'proveedor@example.com',
            'iva_percentage' => 16,
            'status' => 'Activo',
        ]);
        $requisition = Requisition::create(array_merge([
            'folio' => 'REQ-LEGACY',
            'created_on' => '2026-10-19',
            'requested_on' => '2026-10-19',
            'amount' => 1000,
            'partida' => '33901',
            'user_id' => $user->id,
        ], $requisitionPaths));
        $purchaseOrder = PurchaseOrder::create(array_merge([
            'folio' => 'OC-LEGACY',
            'order_date' => '2026-10-19',
            'requisition_id' => $requisition->id,
            'supplier_id' => $supplier->id,
            'amount' => 1000,
            'partida' => '33901',
            'iva_percentage' => 16,
            'contact_email' => $supplier->contact_email,
            'delivery_location' => 'Almacén',
            'receiver' => 'Persona receptora',
            'receiver_phone' => '5550000000',
        ], $purchaseOrderPaths));

        return [$requisition, $purchaseOrder];
    }
}
