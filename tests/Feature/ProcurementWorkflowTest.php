<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\PurchaseOrder;
use App\Models\Requisition;
use App\Models\Supplier;
use App\Models\UploadedDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
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

        $supplierUser = User::where('email', 'compras@bbva.example')->firstOrFail();
        $this->assertSame('BBVA México', $supplierUser->name);
        $this->assertSame('suppliers', $supplierUser->department->slug);
        $this->assertTrue(Hash::check('12345', $supplierUser->password));

        $this->actingAs($supplierUser)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Aún no hay órdenes de compra para este proveedor.');
    }

    public function test_creating_supplier_does_not_change_an_existing_account(): void
    {
        $existingUser = User::factory()->create([
            'email' => 'compras@bbva.example',
            'password' => 'ExistingPassword123!',
        ]);

        $this->actingAs(User::factory()->create())
            ->post(route('suppliers.store'), [
                'number' => '20',
                'name' => 'BBVA México',
                'rfc' => 'BBA830831LJ2',
                'person_type' => 'moral',
                'tax_regime' => '601',
                'cfdi_use' => 'G03',
                'representative' => 'Contacto BBVA',
                'contact_email' => 'COMPRAS@BBVA.EXAMPLE',
                'iva_percentage' => '16',
                'status' => 'Activo',
            ])
            ->assertRedirect(route('suppliers.index'))
            ->assertSessionHas('success');

        $existingUser->refresh();
        $this->assertSame('departments', $existingUser->department->slug);
        $this->assertTrue(Hash::check('ExistingPassword123!', $existingUser->password));
        $this->assertSame(1, User::whereRaw('LOWER(email) = ?', ['compras@bbva.example'])->count());
    }

    public function test_authenticated_user_can_save_requisition_and_purchase_order(): void
    {
        Storage::fake('local');
        $requisitionPdf = UploadedFile::fake()->create('requisition.pdf', 20, 'application/pdf');
        $purchaseOrderPdf = UploadedFile::fake()->create('purchase-order.pdf', 20, 'application/pdf');
        $purchaseOrderXml = UploadedFile::fake()->create('purchase-order.xml', 1, 'text/xml');
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
                'requisition_pdf' => $requisitionPdf,
                'supplier_id' => $supplier->id,
                'quote_email' => 'compras@bbva.example',
                'quote_amount' => '4872.07',
                'delivery_location' => 'CRODE de Chihuahua',
                'receiver' => 'Persona receptora',
                'receiver_phone' => '5550000000',
                'review_confirmed' => 'on',
                'purchase_order_folio' => 'OC-2026-001',
                'purchase_order_date' => '2026-10-07',
                'purchase_order_partida' => '',
                'purchase_order_iva_included' => '',
                'purchase_order_rms' => '21601',
                'purchase_order_rf' => 'Si',
                'purchase_order_pdf' => $purchaseOrderPdf,
                'purchase_order_xml' => $purchaseOrderXml,
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHas('success');

        $requisition = Requisition::where('folio', 'REQ-2026-001')->firstOrFail();
        $purchaseOrder = PurchaseOrder::where('folio', 'OC-2026-001')->firstOrFail();

        $this->assertSame($user->id, $requisition->user_id);
        $this->assertSame($requisition->id, $purchaseOrder->requisition_id);
        $this->assertSame($supplier->id, $purchaseOrder->supplier_id);
        $this->assertNull($purchaseOrder->partida);
        $this->assertNull($purchaseOrder->iva_included);
        $this->assertSame('4872.07', $purchaseOrder->amount);
        $this->assertSame('21601', $purchaseOrder->rms);
        $this->assertSame('Si', $purchaseOrder->rf);
        $this->assertSame('3650.37', $requisition->amount);
        $this->assertSame('2026-10-07', $requisition->created_on->format('Y-m-d'));
        $this->assertSame('2026-10-07', $requisition->requested_on->format('Y-m-d'));
        $this->assertSame('33901', $requisition->partida);
        $this->assertSame('Observación', $requisition->ppp);
        $this->assertNull($requisition->pdf_path);
        $this->assertNull($purchaseOrder->pdf_path);
        $this->assertNull($purchaseOrder->xml_path);

        $this->assertSame(
            $requisitionPdf->getContent(),
            $requisition->uploadedDocuments()->where('kind', 'requisition_pdf')->firstOrFail()->content,
        );
        $this->assertSame(
            'requisition.pdf',
            $requisition->uploadedDocuments()->where('kind', 'requisition_pdf')->firstOrFail()->original_name,
        );
        $this->assertSame(
            $purchaseOrderPdf->getContent(),
            $purchaseOrder->uploadedDocuments()->where('kind', 'purchase_order_pdf')->firstOrFail()->content,
        );
        $this->assertSame(
            $purchaseOrderXml->getContent(),
            $purchaseOrder->uploadedDocuments()->where('kind', 'purchase_order_xml')->firstOrFail()->content,
        );
        Storage::disk('local')->assertEmpty();

        $supplierUser = User::factory()->create([
            'email' => $supplier->contact_email,
            'department_id' => Department::where('slug', 'suppliers')->value('id'),
        ]);

        $this->actingAs($supplierUser)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('OC-2026-001')
            ->assertSee('Adjunta factura y XML');
        $this->get(route('supplier.purchase-orders.pdf', $purchaseOrder))
            ->assertDownload('oc-2026-001.pdf')
            ->assertStreamedContent($purchaseOrderPdf->getContent());
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

        $response = $this->actingAs(User::factory()->create([
            'department_id' => Department::where('slug', 'departments')->value('id'),
        ]))
            ->get(route('home'))
            ->assertOk();

        $response
            ->assertSee('Catálogo de proveedores')
            ->assertDontSee('id="add-supplier-button"', false)
            ->assertSee('Crear nueva orden')
            ->assertSee('value="'.$supplier->id.'"', false)
            ->assertSee('data-purchase-order-paste', false)
            ->assertSee('Pega la fila copiada en este orden', false)
            ->assertSee('name="purchase_order_supplier"', false)
            ->assertSee('id="purchase-order-supplier-help"', false)
            ->assertSee('Escribe o pega el nombre del proveedor seleccionado.', false)
            ->assertSee('name="purchase_order_iva_included"', false)
            ->assertSee('name="requisition_folio"', false)
            ->assertSee('name="quote_amount"', false)
            ->assertSee('name="purchase_order_pdf"', false)
            ->assertSee('name="purchase_order_xml"', false);

        $content = $response->getContent();
        $previousPosition = -1;
        foreach ([
            'name="purchase_order_folio"',
            'name="purchase_order_date"',
            'name="purchase_order_supplier"',
            'data-purchase-order-review="requisition_folio"',
            'data-purchase-order-review="quote_amount"',
            'name="purchase_order_partida"',
            'name="purchase_order_iva_included"',
            'name="purchase_order_rms"',
            'name="purchase_order_rf"',
        ] as $fieldName) {
            $fieldPosition = strpos($content, $fieldName);

            $this->assertNotFalse($fieldPosition);
            $this->assertGreaterThan($previousPosition, $fieldPosition);
            $previousPosition = $fieldPosition;
        }

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

    public function test_supplier_portal_lists_only_orders_for_its_contact_email(): void
    {
        $supplierUser = $this->createUserWithType('suppliers', 'proveedor@example.com');
        $otherSupplierUser = $this->createUserWithType('suppliers', 'otro@example.com');
        $creator = User::factory()->create();
        $supplier = $this->createSupplier('10', $supplierUser->email);
        $otherSupplier = $this->createSupplier('11', $otherSupplierUser->email);

        $this->createPurchaseOrderForSupplier($supplier, 'OC-2026-010', $creator);
        $this->createPurchaseOrderForSupplier($otherSupplier, 'OC-2026-011', $creator);

        $this->actingAs($supplierUser)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('OC-2026-010')
            ->assertDontSee('OC-2026-011')
            ->assertSee('Adjunta factura y XML')
            ->assertSee('Pendiente de validación del pago')
            ->assertDontSee('Vista de ejemplo')
            ->assertDontSee('2515486');
    }

    public function test_supplier_can_upload_invoice_pdf_and_xml_for_its_order(): void
    {
        Storage::fake('local');
        $invoicePdf = UploadedFile::fake()->create('factura.pdf', 10, 'application/pdf');
        $invoiceXml = UploadedFile::fake()->create('factura.xml', 1, 'text/xml');
        $supplierUser = $this->createUserWithType('suppliers', 'proveedor@example.com');
        $creator = User::factory()->create();
        $supplier = $this->createSupplier('12', $supplierUser->email);
        $purchaseOrder = $this->createPurchaseOrderForSupplier($supplier, 'OC-2026-012', $creator);

        $this->actingAs($supplierUser)
            ->from(route('home'))
            ->post(route('supplier.purchase-orders.invoices.store', $purchaseOrder), [
                'invoice_pdf' => $invoicePdf,
                'invoice_xml' => $invoiceXml,
                'review_confirmed' => '1',
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHas('success');

        $purchaseOrder->refresh();
        $this->assertNull($purchaseOrder->invoice_pdf_path);
        $this->assertNull($purchaseOrder->invoice_xml_path);
        $this->assertSame(
            $invoicePdf->getContent(),
            $purchaseOrder->uploadedDocuments()->where('kind', 'invoice_pdf')->firstOrFail()->content,
        );
        $this->assertSame(
            $invoiceXml->getContent(),
            $purchaseOrder->uploadedDocuments()->where('kind', 'invoice_xml')->firstOrFail()->content,
        );
        Storage::disk('local')->assertEmpty();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Factura y XML recibidos');
    }

    public function test_internal_users_can_view_and_download_supplier_documents(): void
    {
        Storage::fake('local');
        $purchaseOrderPdf = UploadedFile::fake()->create('orden-compra.pdf', 10, 'application/pdf');
        $invoicePdf = UploadedFile::fake()->create('factura.pdf', 10, 'application/pdf');
        $invoiceXml = UploadedFile::fake()->create('factura.xml', 1, 'text/xml');
        $paymentComplement = UploadedFile::fake()->create('complemento.pdf', 10, 'application/pdf');
        $supplierUser = $this->createUserWithType('suppliers', 'proveedor@example.com');
        $administrator = $this->createUserWithType('administrator', 'admin@example.com');
        $internalUser = $this->createUserWithType('departments', 'usuario@example.com');
        $supplier = $this->createSupplier('20', $supplierUser->email);
        $purchaseOrder = $this->createPurchaseOrderForSupplier(
            $supplier,
            'OC-2026-020',
            User::factory()->create(),
        );
        UploadedDocument::replaceFor($purchaseOrder, 'purchase_order_pdf', $purchaseOrderPdf);

        $this->actingAs($supplierUser)
            ->post(route('supplier.purchase-orders.invoices.store', $purchaseOrder), [
                'invoice_pdf' => $invoicePdf,
                'invoice_xml' => $invoiceXml,
                'review_confirmed' => '1',
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHas('success');

        $this->actingAs($administrator)
            ->from(route('home'))
            ->post(route('admin.purchase-orders.validate-payment', $purchaseOrder))
            ->assertRedirect(route('home'))
            ->assertSessionHas('success');

        $this->actingAs($supplierUser)
            ->post(route('supplier.purchase-orders.payment-complement.store', $purchaseOrder), [
                'payment_complement' => $paymentComplement,
                'review_confirmed' => '1',
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHas('success');

        $this->actingAs($internalUser)
            ->get(route('home'))
            ->assertOk()
            ->assertSee($supplier->name)
            ->assertSee($supplier->contact_email)
            ->assertSee('Descargar PDF de orden')
            ->assertSee('Factura PDF')
            ->assertSee('Factura XML')
            ->assertSee('Descargar complemento')
            ->assertSee(route('purchase-orders.documents.download', [$purchaseOrder, 'purchase-order-pdf']), false)
            ->assertSee(route('purchase-orders.documents.download', [$purchaseOrder, 'invoice-pdf']), false)
            ->assertSee(route('purchase-orders.documents.download', [$purchaseOrder, 'invoice-xml']), false)
            ->assertSee(route('purchase-orders.documents.download', [$purchaseOrder, 'payment-complement']), false);

        $this->get(route('purchase-orders.documents.download', [$purchaseOrder, 'purchase-order-pdf']))
            ->assertDownload('oc-2026-020-orden-compra.pdf')
            ->assertStreamedContent($purchaseOrderPdf->getContent());
        $this->get(route('purchase-orders.documents.download', [$purchaseOrder, 'invoice-pdf']))
            ->assertDownload('oc-2026-020-factura.pdf')
            ->assertStreamedContent($invoicePdf->getContent());
        $this->get(route('purchase-orders.documents.download', [$purchaseOrder, 'invoice-xml']))
            ->assertDownload('oc-2026-020-factura.xml')
            ->assertStreamedContent($invoiceXml->getContent());
        $this->get(route('purchase-orders.documents.download', [$purchaseOrder, 'payment-complement']))
            ->assertDownload('oc-2026-020-complemento-pago.pdf')
            ->assertStreamedContent($paymentComplement->getContent());

        $this->assertSame(4, UploadedDocument::where('purchase_order_id', $purchaseOrder->id)->count());
        $this->assertSame(
            $paymentComplement->getContent(),
            $purchaseOrder->uploadedDocuments()->where('kind', 'payment_complement')->firstOrFail()->content,
        );
        Storage::disk('local')->assertEmpty();
    }

    public function test_supplier_cannot_download_internal_supplier_documents(): void
    {
        Storage::fake('local');
        $supplierUser = $this->createUserWithType('suppliers', 'proveedor@example.com');
        $supplier = $this->createSupplier('21', $supplierUser->email);
        $purchaseOrder = $this->createPurchaseOrderForSupplier(
            $supplier,
            'OC-2026-021',
            User::factory()->create(),
        );
        $purchaseOrder->forceFill(['invoice_pdf_path' => 'supplier-invoices/factura.pdf'])->save();
        Storage::disk('local')->put($purchaseOrder->invoice_pdf_path, 'PDF factura');

        $this->actingAs($supplierUser)
            ->get(route('purchase-orders.documents.download', [$purchaseOrder, 'invoice-pdf']))
            ->assertForbidden();
    }

    public function test_supplier_cannot_upload_documents_for_another_supplier_order(): void
    {
        Storage::fake('local');
        $supplierUser = $this->createUserWithType('suppliers', 'proveedor@example.com');
        $otherSupplier = $this->createSupplier('13', 'otro@example.com');
        $purchaseOrder = $this->createPurchaseOrderForSupplier($otherSupplier, 'OC-2026-013', User::factory()->create());

        $this->actingAs($supplierUser)
            ->post(route('supplier.purchase-orders.invoices.store', $purchaseOrder), [
                'invoice_pdf' => UploadedFile::fake()->create('factura.pdf', 10, 'application/pdf'),
                'invoice_xml' => UploadedFile::fake()->create('factura.xml', 1, 'text/xml'),
                'review_confirmed' => '1',
            ])
            ->assertNotFound();

        $this->assertNull($purchaseOrder->fresh()->invoice_pdf_path);
        $this->assertDatabaseMissing('uploaded_documents', [
            'purchase_order_id' => $purchaseOrder->id,
            'kind' => 'invoice_pdf',
        ]);
        $this->assertDatabaseCount('uploaded_documents', 0);
        Storage::disk('local')->assertEmpty();
    }

    public function test_supplier_invoice_upload_rejects_non_xml_files_without_saving_documents(): void
    {
        Storage::fake('local');
        $supplierUser = $this->createUserWithType('suppliers', 'proveedor@example.com');
        $supplier = $this->createSupplier('18', $supplierUser->email);
        $purchaseOrder = $this->createPurchaseOrderForSupplier($supplier, 'OC-2026-018', User::factory()->create());

        $this->actingAs($supplierUser)
            ->from(route('home'))
            ->post(route('supplier.purchase-orders.invoices.store', $purchaseOrder), [
                'invoice_pdf' => UploadedFile::fake()->create('factura.pdf', 10, 'application/pdf'),
                'invoice_xml' => UploadedFile::fake()->create('factura.txt', 1, 'text/plain'),
                'review_confirmed' => '1',
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors('invoice_xml');

        $this->assertNull($purchaseOrder->fresh()->invoice_pdf_path);
        $this->assertDatabaseMissing('uploaded_documents', [
            'purchase_order_id' => $purchaseOrder->id,
            'kind' => 'invoice_pdf',
        ]);
        $this->assertDatabaseCount('uploaded_documents', 0);
        Storage::disk('local')->assertEmpty();
    }

    public function test_supplier_cannot_upload_payment_complement_before_payment_validation(): void
    {
        $supplierUser = $this->createUserWithType('suppliers', 'proveedor@example.com');
        $supplier = $this->createSupplier('19', $supplierUser->email);
        $purchaseOrder = $this->createPurchaseOrderForSupplier($supplier, 'OC-2026-019', User::factory()->create());

        $this->actingAs($supplierUser)
            ->post(route('supplier.purchase-orders.payment-complement.store', $purchaseOrder))
            ->assertForbidden();

        $this->assertNull($purchaseOrder->fresh()->payment_complement_path);
    }

    public function test_administrator_validation_enables_supplier_payment_complement_upload(): void
    {
        Storage::fake('local');
        $administrator = $this->createUserWithType('administrator', 'admin@example.com');
        $supplierUser = $this->createUserWithType('suppliers', 'proveedor@example.com');
        $supplier = $this->createSupplier('14', $supplierUser->email);
        $purchaseOrder = $this->createPurchaseOrderForSupplier($supplier, 'OC-2026-014', User::factory()->create());

        $this->actingAs($administrator)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Validar pago');

        $this->from(route('home'))
            ->post(route('admin.purchase-orders.validate-payment', $purchaseOrder))
            ->assertRedirect(route('home'))
            ->assertSessionHas('success');

        $purchaseOrder->refresh();
        $this->assertNotNull($purchaseOrder->payment_validated_at);
        $this->assertSame($administrator->id, $purchaseOrder->payment_validated_by);

        $this->actingAs($supplierUser)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Adjunta complemento de pago');

        $this->from(route('home'))
            ->post(route('supplier.purchase-orders.payment-complement.store', $purchaseOrder), [
                'payment_complement' => UploadedFile::fake()->create('complemento.pdf', 10, 'application/pdf'),
                'review_confirmed' => '1',
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHas('success');

        $purchaseOrder->refresh();
        $this->assertNull($purchaseOrder->payment_complement_path);
        $this->assertDatabaseHas('uploaded_documents', [
            'purchase_order_id' => $purchaseOrder->id,
            'kind' => 'payment_complement',
            'mime_type' => 'application/pdf',
        ]);
        Storage::disk('local')->assertEmpty();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Complemento recibido')
            ->assertDontSee('Adjunta complemento de pago');
    }

    public function test_non_administrator_cannot_validate_a_purchase_order_payment(): void
    {
        $user = $this->createUserWithType('departments', 'usuario@example.com');
        $supplier = $this->createSupplier('15', 'proveedor@example.com');
        $purchaseOrder = $this->createPurchaseOrderForSupplier($supplier, 'OC-2026-015', $user);

        $this->actingAs($user)
            ->post(route('admin.purchase-orders.validate-payment', $purchaseOrder))
            ->assertForbidden();

        $this->assertNull($purchaseOrder->fresh()->payment_validated_at);
    }

    public function test_supplier_can_download_only_its_own_purchase_order_pdf(): void
    {
        Storage::fake('local');
        $supplierUser = $this->createUserWithType('suppliers', 'proveedor@example.com');
        $otherSupplier = $this->createSupplier('16', 'otro@example.com');
        $purchaseOrder = $this->createPurchaseOrderForSupplier(
            $this->createSupplier('17', $supplierUser->email),
            'OC-2026-016',
            User::factory()->create(),
        );
        Storage::disk('local')->put($purchaseOrder->pdf_path, 'orden de compra');
        $otherPurchaseOrder = $this->createPurchaseOrderForSupplier($otherSupplier, 'OC-2026-017', User::factory()->create());

        $this->actingAs($supplierUser)
            ->get(route('supplier.purchase-orders.pdf', $purchaseOrder))
            ->assertOk();

        $this->get(route('supplier.purchase-orders.pdf', $otherPurchaseOrder))
            ->assertNotFound();
    }

    private function createUserWithType(string $slug, string $email): User
    {
        return User::factory()->create([
            'email' => $email,
            'department_id' => Department::where('slug', $slug)->value('id'),
        ]);
    }

    private function createSupplier(string $number, string $contactEmail): Supplier
    {
        return Supplier::create([
            'number' => $number,
            'name' => 'Proveedor '.$number,
            'rfc' => 'ABC010203A'.str_pad($number, 2, '0', STR_PAD_LEFT),
            'person_type' => 'moral',
            'tax_regime' => '601',
            'cfdi_use' => 'G03',
            'representative' => 'Contacto '.$number,
            'contact_email' => $contactEmail,
            'iva_percentage' => 16,
            'status' => 'Activo',
        ]);
    }

    private function createPurchaseOrderForSupplier(Supplier $supplier, string $folio, User $creator): PurchaseOrder
    {
        $requisition = Requisition::create([
            'folio' => 'REQ-'.$folio,
            'created_on' => '2026-10-19',
            'requested_on' => '2026-10-19',
            'amount' => 1000,
            'partida' => '33901',
            'pdf_path' => 'requisitions/'.$folio.'.pdf',
            'user_id' => $creator->id,
        ]);

        return PurchaseOrder::create([
            'folio' => $folio,
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
            'pdf_path' => 'purchase-orders/'.$folio.'.pdf',
            'xml_path' => 'purchase-orders/'.$folio.'.xml',
        ]);
    }
}
