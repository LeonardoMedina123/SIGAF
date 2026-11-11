<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\PurchaseOrder;
use App\Models\Requisition;
use App\Models\Supplier;
use App\Models\UploadedDocument;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class RequisitionDocumentArchiveControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrators_can_download_all_requisition_files_in_a_zip_named_after_the_folio(): void
    {
        $this->seed(DepartmentSeeder::class);
        $administrator = $this->createUserWithType('administrator');
        $requisition = $this->createRequisition('REQ-2026-001');
        $supplier = $this->createSupplier();
        $purchaseOrder = $this->createPurchaseOrder($requisition, $supplier);

        UploadedDocument::replaceContentFor(
            $requisition,
            'requisition_pdf',
            'solicitud.pdf',
            'application/pdf',
            'requisition contents',
        );
        UploadedDocument::replaceContentFor(
            $purchaseOrder,
            'purchase_order_pdf',
            'orden-compra.pdf',
            'application/pdf',
            'purchase order contents',
        );
        UploadedDocument::replaceContentFor(
            $purchaseOrder,
            'purchase_order_xml',
            'orden-compra.xml',
            'text/xml',
            'purchase order XML contents',
        );
        UploadedDocument::replaceContentFor(
            $purchaseOrder,
            'invoice_pdf',
            'factura.pdf',
            'application/pdf',
            'invoice contents',
        );
        UploadedDocument::replaceContentFor(
            $purchaseOrder,
            'invoice_xml',
            '../../factura.xml',
            'text/xml',
            'invoice XML contents',
        );
        UploadedDocument::replaceContentFor(
            $purchaseOrder,
            'payment_complement',
            'complemento.pdf',
            'application/pdf',
            'payment complement contents',
        );

        $response = $this->actingAs($administrator)
            ->get(route('admin.requisitions.documents.download', $requisition))
            ->assertDownload('REQ-2026-001.zip')
            ->assertHeader('Content-Type', 'application/zip');

        $this->assertSame([
            'REQ-2026-001/requisicion/requisition-pdf-solicitud.pdf' => 'requisition contents',
            'REQ-2026-001/orden-compra/purchase-order-pdf-orden-compra.pdf' => 'purchase order contents',
            'REQ-2026-001/orden-compra/purchase-order-xml-orden-compra.xml' => 'purchase order XML contents',
            'REQ-2026-001/orden-compra/invoice-pdf-factura.pdf' => 'invoice contents',
            'REQ-2026-001/orden-compra/invoice-xml-factura.xml' => 'invoice XML contents',
            'REQ-2026-001/orden-compra/payment-complement-complemento.pdf' => 'payment complement contents',
        ], $this->extractArchiveEntries($response->streamedContent()));
    }

    public function test_administrators_see_the_archive_action_for_requisitions_with_documents(): void
    {
        $this->seed(DepartmentSeeder::class);
        $administrator = $this->createUserWithType('administrator');
        $requisition = $this->createRequisition('REQ-2026-002');

        UploadedDocument::replaceContentFor(
            $requisition,
            'requisition_pdf',
            'solicitud.pdf',
            'application/pdf',
            'requisition contents',
        );

        $this->actingAs($administrator)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Descarga de archivos')
            ->assertSee('data-archive-dialog-open', false)
            ->assertSee(route('admin.requisitions.documents.download', $requisition), false);
    }

    public function test_administrators_can_download_all_documents_in_the_order_date_range_with_each_hierarchy(): void
    {
        $this->seed(DepartmentSeeder::class);
        $administrator = $this->createUserWithType('administrator');
        $supplier = $this->createSupplier();
        $includedRequisition = $this->createRequisition('REQ-2026-010');
        $includedOrder = $this->createPurchaseOrder(
            $includedRequisition,
            $supplier,
            'OC-2026-010',
            '2026-10-19',
        );
        $excludedRequisition = $this->createRequisition('REQ-2026-011');
        $excludedOrder = $this->createPurchaseOrder(
            $excludedRequisition,
            $supplier,
            'OC-2026-011',
            '2026-10-20',
        );

        UploadedDocument::replaceContentFor(
            $includedRequisition,
            'requisition_pdf',
            'solicitud.pdf',
            'application/pdf',
            'requisition contents',
        );
        UploadedDocument::replaceContentFor(
            $includedOrder,
            'invoice_pdf',
            'factura.pdf',
            'application/pdf',
            'invoice contents',
        );
        UploadedDocument::replaceContentFor(
            $excludedOrder,
            'invoice_pdf',
            'fuera-de-rango.pdf',
            'application/pdf',
            'excluded contents',
        );

        $folders = [
            'day' => '2026-10-19/REQ-2026-010',
            'week' => 'semana-2026-43/2026-10-19/REQ-2026-010',
            'month' => '2026-10/semana-2026-43/2026-10-19/REQ-2026-010',
            'year' => '2026/10/semana-2026-43/2026-10-19/REQ-2026-010',
        ];

        foreach ($folders as $groupBy => $folder) {
            $response = $this->actingAs($administrator)
                ->get(route('admin.requisitions.documents.range', [
                    'from_date' => '2026-10-19',
                    'to_date' => '2026-10-19',
                    'group_by' => $groupBy,
                ]))
                ->assertOk()
                ->assertDownload('archivos-2026-10-19-a-2026-10-19.zip');

            $this->assertSame([
                $folder.'/requisicion/requisition-pdf-solicitud.pdf' => 'requisition contents',
                $folder.'/orden-compra/invoice-pdf-factura.pdf' => 'invoice contents',
            ], $this->extractArchiveEntries($response->streamedContent()));
        }
    }

    public function test_range_download_rejects_an_end_date_before_the_start_date(): void
    {
        $this->seed(DepartmentSeeder::class);
        $administrator = $this->createUserWithType('administrator');

        $this->actingAs($administrator)
            ->get(route('admin.requisitions.documents.range', [
                'from_date' => '2026-10-20',
                'to_date' => '2026-10-19',
                'group_by' => 'day',
            ]))
            ->assertSessionHasErrors('to_date');
    }

    public function test_non_administrators_cannot_download_a_date_range_archive(): void
    {
        $this->seed(DepartmentSeeder::class);
        $user = $this->createUserWithType('departments');

        $this->actingAs($user)
            ->get(route('admin.requisitions.documents.range', [
                'from_date' => '2026-10-19',
                'to_date' => '2026-10-19',
                'group_by' => 'day',
            ]))
            ->assertForbidden();
    }

    public function test_range_download_returns_not_found_when_no_documents_match_the_dates(): void
    {
        $this->seed(DepartmentSeeder::class);
        $administrator = $this->createUserWithType('administrator');
        $requisition = $this->createRequisition('REQ-2026-012');
        $this->createPurchaseOrder($requisition, $this->createSupplier());

        $this->actingAs($administrator)
            ->get(route('admin.requisitions.documents.range', [
                'from_date' => '2026-10-19',
                'to_date' => '2026-10-19',
                'group_by' => 'day',
            ]))
            ->assertNotFound();
    }

    public function test_non_administrators_receive_forbidden_when_downloading_a_requisition_archive(): void
    {
        $this->seed(DepartmentSeeder::class);
        $user = $this->createUserWithType('departments');
        $requisition = $this->createRequisition('REQ-2026-003');

        $this->actingAs($user)
            ->get(route('admin.requisitions.documents.download', $requisition))
            ->assertForbidden();
    }

    public function test_archive_download_returns_not_found_when_no_requisition_files_exist(): void
    {
        $this->seed(DepartmentSeeder::class);
        $administrator = $this->createUserWithType('administrator');
        $requisition = $this->createRequisition('REQ-2026-004');

        $this->actingAs($administrator)
            ->get(route('admin.requisitions.documents.download', $requisition))
            ->assertNotFound();
    }

    /**
     * @return array<string, string>
     */
    private function extractArchiveEntries(string $archiveContent): array
    {
        $archivePath = tempnam(sys_get_temp_dir(), 'sigaf-test-');
        $this->assertNotFalse($archivePath);
        $archive = new ZipArchive;
        $archiveOpened = false;

        try {
            $this->assertSame(strlen($archiveContent), file_put_contents($archivePath, $archiveContent));
            $archiveOpened = $archive->open($archivePath) === true;
            $this->assertTrue($archiveOpened);

            $entries = [];

            for ($index = 0; $index < $archive->numFiles; $index++) {
                $name = $archive->getNameIndex($index);
                $content = $archive->getFromIndex($index);

                $this->assertIsString($name);
                $this->assertIsString($content);
                $entries[$name] = $content;
            }

            return $entries;
        } finally {
            if ($archiveOpened) {
                $archive->close();
            }

            unlink($archivePath);
        }
    }

    private function createUserWithType(string $slug): User
    {
        return User::factory()->create([
            'department_id' => Department::where('slug', $slug)->value('id'),
        ]);
    }

    private function createRequisition(string $folio): Requisition
    {
        return Requisition::create([
            'folio' => $folio,
            'created_on' => '2026-10-19',
            'requested_on' => '2026-10-19',
            'amount' => 1000,
            'partida' => '33901',
            'pdf_path' => null,
            'user_id' => User::factory()->create()->id,
        ]);
    }

    private function createSupplier(): Supplier
    {
        return Supplier::create([
            'number' => '1',
            'name' => 'Proveedor de prueba',
            'rfc' => 'ABC010203AA1',
            'person_type' => 'moral',
            'tax_regime' => '601',
            'cfdi_use' => 'G03',
            'contact_email' => 'proveedor@example.com',
            'iva_percentage' => 16,
            'status' => 'Activo',
        ]);
    }

    private function createPurchaseOrder(
        Requisition $requisition,
        Supplier $supplier,
        string $folio = 'OC-2026-001',
        string $orderDate = '2026-10-19',
    ): PurchaseOrder {
        return PurchaseOrder::create([
            'folio' => $folio,
            'order_date' => $orderDate,
            'requisition_id' => $requisition->id,
            'supplier_id' => $supplier->id,
            'amount' => 1000,
            'partida' => '33901',
            'iva_percentage' => 16,
            'contact_email' => $supplier->contact_email,
            'delivery_location' => 'Almacén',
            'receiver' => 'Persona receptora',
            'receiver_phone' => '5550000000',
            'pdf_path' => null,
            'xml_path' => null,
        ]);
    }
}
