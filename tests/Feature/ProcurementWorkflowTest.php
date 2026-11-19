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
                'address' => 'Av. Paseo de la Reforma',
                'city' => 'Ciudad de México',
                'economic_activity' => 'Servicios financieros',
                'status' => 'Activo',
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
            'representative' => null,
            'phone' => null,
            'cellphone' => null,
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
        $supplierUser = User::factory()->create([
            'email' => $supplier->contact_email,
            'department_id' => Department::where('slug', 'suppliers')->value('id'),
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
        $this->assertSame('3650.37', $purchaseOrder->amount);
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
        $this->assertDatabaseMissing('uploaded_documents', [
            'purchase_order_id' => $purchaseOrder->id,
            'kind' => 'purchase_order_xml',
        ]);

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
        Storage::disk('local')->assertEmpty();

        $this->assertSame(1, $supplierUser->unreadNotifications()->count());
        $this->assertSame(
            'purchase_order_created',
            $supplierUser->unreadNotifications()->firstOrFail()->data['event'],
        );

        $this->actingAs($supplierUser)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('OC-2026-001')
            ->assertSee('Adjunta factura y XML')
            ->assertSee('Nueva orden de compra');
        $this->get(route('supplier.purchase-orders.pdf', $purchaseOrder))
            ->assertDownload('oc-2026-001.pdf')
            ->assertStreamedContent($purchaseOrderPdf->getContent());
    }

    public function test_authenticated_user_can_edit_a_requisition_and_its_order_from_the_folio(): void
    {
        $user = User::factory()->create();
        $supplier = $this->createSupplier('97', 'proveedor-edicion@example.com');
        $purchaseOrder = $this->createPurchaseOrderForSupplier($supplier, 'OC-2026-097', $user);
        $requisition = $purchaseOrder->requisition;
        UploadedDocument::replaceContentFor(
            $requisition,
            'requisition_pdf',
            'requisicion-original.pdf',
            'application/pdf',
            'Contenido PDF de requisición',
        );
        UploadedDocument::replaceContentFor(
            $purchaseOrder,
            'purchase_order_pdf',
            'orden-original.pdf',
            'application/pdf',
            'Contenido PDF de orden',
        );

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('data-edit-order', false)
            ->assertSee(route('orders.edit', $requisition), false)
            ->assertSee(route('orders.update', $requisition), false);

        $this->getJson(route('orders.edit', $requisition))
            ->assertOk()
            ->assertJsonPath('requisition_folio', $requisition->folio)
            ->assertJsonPath('supplier_id', $supplier->id)
            ->assertJsonPath('requisition_pdf_name', 'requisicion-original.pdf')
            ->assertJsonPath('purchase_order_pdf_name', 'orden-original.pdf');

        $this->put(route('orders.update', $requisition), [
            'requisition_folio' => $requisition->folio,
            'requisition_created_on' => '2026-10-20',
            'requisition_requested_on' => '2026-10-21',
            'requisition_amount' => '2350.50',
            'item_number' => '33902',
            'requisition_sp' => 'SP editado',
            'requisition_ppp' => 'PPP editado',
            'requisition_sup' => 'SUP editado',
            'requisition_dir' => 'DIR editado',
            'requisition_process' => 'Proceso editado',
            'requisition_rf' => 'RF editado',
            'supplier_id' => $supplier->id,
            'quote_email' => $supplier->contact_email,
            'delivery_location' => 'Almacén actualizado',
            'receiver' => 'Nueva persona receptora',
            'receiver_phone' => '5550000001',
            'review_confirmed' => 'on',
            'purchase_order_folio' => $purchaseOrder->folio,
            'purchase_order_date' => '2026-10-22',
            'purchase_order_partida' => '33902',
            'purchase_order_iva_included' => '0',
            'purchase_order_rms' => '21701',
            'purchase_order_rf' => 'No',
        ])
            ->assertRedirect(route('home'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('requisitions', [
            'id' => $requisition->id,
            'partida' => '33902',
            'sp' => 'SP editado',
            'ppp' => 'PPP editado',
            'sup' => 'SUP editado',
            'dir' => 'DIR editado',
            'process' => 'Proceso editado',
            'rf' => 'RF editado',
        ]);
        $requisition->refresh();
        $this->assertSame('2026-10-20', $requisition->created_on->format('Y-m-d'));
        $this->assertSame('2026-10-21', $requisition->requested_on->format('Y-m-d'));
        $this->assertSame('2350.50', $requisition->amount);
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $purchaseOrder->id,
            'partida' => '33902',
            'iva_included' => false,
            'delivery_location' => 'Almacén actualizado',
            'receiver' => 'Nueva persona receptora',
            'receiver_phone' => '5550000001',
            'rms' => '21701',
            'rf' => 'No',
        ]);
        $purchaseOrder->refresh();
        $this->assertSame('2026-10-22', $purchaseOrder->order_date->format('Y-m-d'));
        $this->assertSame('2350.50', $purchaseOrder->amount);
        $this->assertSame(
            'Contenido PDF de requisición',
            $requisition->uploadedDocuments()->where('kind', 'requisition_pdf')->firstOrFail()->content,
        );
        $this->assertSame(
            'Contenido PDF de orden',
            $purchaseOrder->uploadedDocuments()->where('kind', 'purchase_order_pdf')->firstOrFail()->content,
        );
    }

    public function test_supplier_cannot_edit_an_internal_requisition_or_order(): void
    {
        $supplierUser = $this->createUserWithType('suppliers', 'proveedor-edicion@example.com');
        $supplier = $this->createSupplier('96', $supplierUser->email);
        $purchaseOrder = $this->createPurchaseOrderForSupplier(
            $supplier,
            'OC-2026-096',
            User::factory()->create(),
        );
        $requisition = $purchaseOrder->requisition;

        $this->actingAs($supplierUser)
            ->get(route('orders.edit', $requisition))
            ->assertForbidden();

        $this->put(route('orders.update', $requisition), [])
            ->assertForbidden();
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
            ->assertSee('name="supplier_id"', false)
            ->assertSee('data-purchase-order-review="supplier_name"', false)
            ->assertSee('data-purchase-order-review="requisition_amount"', false)
            ->assertDontSee('name="purchase_order_supplier"', false)
            ->assertSee('name="purchase_order_iva_included"', false)
            ->assertSee('name="requisition_folio"', false)
            ->assertSee('name="requisition_amount"', false)
            ->assertDontSee('name="quote_amount"', false)
            ->assertSee('name="purchase_order_pdf"', false)
            ->assertDontSee('name="purchase_order_xml"', false);

        $content = $response->getContent();
        $this->assertSame(5, substr_count($content, '<button type="button" data-step-trigger'));
        $this->assertSame(1, substr_count($content, 'aria-label="Ir al paso 5: Confirmación"'));

        $previousPosition = -1;
        foreach ([
            'name="purchase_order_folio"',
            'name="purchase_order_date"',
            'data-purchase-order-review="supplier_name"',
            'data-purchase-order-review="requisition_folio"',
            'data-purchase-order-review="requisition_amount"',
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

    public function test_supplier_form_omits_duplicate_and_removed_fields(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->get(route('suppliers.index'))
            ->assertOk();

        $content = $response->getContent();

        foreach (['<input name="number"', '<input name="name"'] as $fieldName) {
            $this->assertSame(1, substr_count($content, $fieldName));
        }

        foreach ([
            '<input name="representative"',
            '<input name="phone"',
            '<input name="cellphone"',
            '<input name="curp"',
            '<input name="iva_percentage"',
            '<input name="it_capture"',
        ] as $removedFieldName) {
            $this->assertSame(0, substr_count($content, $removedFieldName));
        }
    }

    public function test_supplier_can_be_created_without_removed_contact_and_tax_fields(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('suppliers.store'), [
                'number' => '4',
                'name' => 'Proveedor con datos fiscales',
                'rfc' => 'ABC010203AB1',
                'person_type' => 'fisica',
                'tax_regime' => '612',
                'cfdi_use' => 'G03',
                'contact_email' => 'ana@example.com',
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
            'contact_email' => 'ana@example.com',
            'representative' => null,
            'phone' => null,
            'cellphone' => null,
            'iva_percentage' => '16.00',
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
            ->assertSee('data-order-amount="1000.00"', false)
            ->assertSee('data-supplier-rfc="ABC010203A10"', false)
            ->assertSee('Verifica visualmente que la factura PDF')
            ->assertSee('Pendiente de validación del pago')
            ->assertDontSee('Vista de ejemplo')
            ->assertDontSee('2515486');
    }

    public function test_supplier_can_upload_invoice_pdf_and_xml_for_its_order(): void
    {
        Storage::fake('local');
        $invoicePdf = UploadedFile::fake()->create('factura.pdf', 10, 'application/pdf');
        $supplierUser = $this->createUserWithType('suppliers', 'proveedor@example.com');
        $creator = User::factory()->create();
        $administrator = $this->createUserWithType('administrator', 'admin@example.com');
        $financialUser = $this->createUserWithType('financials', 'finanzas@example.com');
        $unrelatedUser = User::factory()->create();
        $supplier = $this->createSupplier('12', $supplierUser->email);
        $purchaseOrder = $this->createPurchaseOrderForSupplier($supplier, 'OC-2026-012', $creator);
        $invoiceXml = $this->createInvoiceXml($purchaseOrder);

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

        foreach ([$creator, $administrator, $financialUser] as $recipient) {
            $this->assertSame(1, $recipient->unreadNotifications()->count());
            $this->assertSame(
                'invoice_uploaded',
                $recipient->unreadNotifications()->firstOrFail()->data['event'],
            );
        }
        $this->assertSame(0, $supplierUser->unreadNotifications()->count());
        $this->assertSame(0, $unrelatedUser->unreadNotifications()->count());

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Factura y XML recibidos');
    }

    public function test_supplier_invoice_upload_rejects_an_xml_with_a_different_supplier_rfc(): void
    {
        Storage::fake('local');
        $supplierUser = $this->createUserWithType('suppliers', 'proveedor@example.com');
        $supplier = $this->createSupplier('21', $supplierUser->email);
        $purchaseOrder = $this->createPurchaseOrderForSupplier(
            $supplier,
            'OC-2026-021',
            User::factory()->create(),
        );

        $this->actingAs($supplierUser)
            ->from(route('home'))
            ->post(route('supplier.purchase-orders.invoices.store', $purchaseOrder), [
                'invoice_pdf' => UploadedFile::fake()->create('factura.pdf', 10, 'application/pdf'),
                'invoice_xml' => $this->createInvoiceXml($purchaseOrder, rfc: 'XYZ010203AA1'),
                'review_confirmed' => '1',
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors('invoice_xml');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('El RFC emisor del XML no coincide con el RFC registrado del proveedor.');

        $this->assertDatabaseCount('uploaded_documents', 0);
        $this->assertDatabaseCount('notifications', 0);
        Storage::disk('local')->assertEmpty();
    }

    public function test_supplier_invoice_upload_rejects_an_xml_with_a_different_total(): void
    {
        Storage::fake('local');
        $supplierUser = $this->createUserWithType('suppliers', 'proveedor@example.com');
        $supplier = $this->createSupplier('22', $supplierUser->email);
        $purchaseOrder = $this->createPurchaseOrderForSupplier(
            $supplier,
            'OC-2026-022',
            User::factory()->create(),
        );

        $this->actingAs($supplierUser)
            ->from(route('home'))
            ->post(route('supplier.purchase-orders.invoices.store', $purchaseOrder), [
                'invoice_pdf' => UploadedFile::fake()->create('factura.pdf', 10, 'application/pdf'),
                'invoice_xml' => $this->createInvoiceXml($purchaseOrder, amount: '1000.01'),
                'review_confirmed' => '1',
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors('invoice_xml');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('El importe total del XML no coincide con el importe de la orden de compra.');

        $this->assertDatabaseCount('uploaded_documents', 0);
        $this->assertDatabaseCount('notifications', 0);
        Storage::disk('local')->assertEmpty();
    }

    public function test_supplier_invoice_upload_rejects_malformed_cfdi_xml(): void
    {
        Storage::fake('local');
        $supplierUser = $this->createUserWithType('suppliers', 'proveedor@example.com');
        $supplier = $this->createSupplier('23', $supplierUser->email);
        $purchaseOrder = $this->createPurchaseOrderForSupplier(
            $supplier,
            'OC-2026-023',
            User::factory()->create(),
        );

        $this->actingAs($supplierUser)
            ->from(route('home'))
            ->post(route('supplier.purchase-orders.invoices.store', $purchaseOrder), [
                'invoice_pdf' => UploadedFile::fake()->create('factura.pdf', 10, 'application/pdf'),
                'invoice_xml' => UploadedFile::fake()->createWithContent('factura.xml', '<not-cfdi/>')
                    ->mimeType('text/xml'),
                'review_confirmed' => '1',
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors('invoice_xml');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('El archivo no contiene un XML CFDI válido.');

        $this->assertDatabaseCount('uploaded_documents', 0);
        $this->assertDatabaseCount('notifications', 0);
        Storage::disk('local')->assertEmpty();
    }

    public function test_internal_users_can_view_and_download_supplier_documents(): void
    {
        Storage::fake('local');
        $purchaseOrderPdf = UploadedFile::fake()->create('orden-compra.pdf', 10, 'application/pdf');
        $invoicePdf = UploadedFile::fake()->create('factura.pdf', 10, 'application/pdf');
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
                'invoice_xml' => $this->createInvoiceXml($purchaseOrder),
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
            ->assertSee('Descargar factura PDF')
            ->assertSee('Factura XML')
            ->assertSee('Descargar complemento')
            ->assertSee('data-filter-invoice-status="Recibida"', false)
            ->assertSee('data-filter-payment-status="Pagado"', false)
            ->assertSee('data-filter-complement-status="Recibido"', false)
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

    public function test_internal_document_download_does_not_fall_back_to_a_local_file(): void
    {
        Storage::fake('local');
        $internalUser = $this->createUserWithType('departments', 'usuario@example.com');
        $supplier = $this->createSupplier('23', 'proveedor@example.com');
        $purchaseOrder = $this->createPurchaseOrderForSupplier(
            $supplier,
            'OC-2026-023',
            User::factory()->create(),
        );
        $purchaseOrder->forceFill(['invoice_pdf_path' => 'supplier-invoices/factura.pdf'])->save();
        Storage::disk('local')->put($purchaseOrder->invoice_pdf_path, 'PDF factura heredado');

        $this->assertFalse($purchaseOrder->hasDocument('invoice_pdf'));

        $this->actingAs($internalUser)
            ->get(route('purchase-orders.documents.download', [$purchaseOrder, 'invoice-pdf']))
            ->assertNotFound();
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
        $this->assertDatabaseCount('notifications', 0);
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
        $financialUser = $this->createUserWithType('financials', 'finanzas@example.com');
        $creator = User::factory()->create();
        $supplier = $this->createSupplier('14', $supplierUser->email);
        $purchaseOrder = $this->createPurchaseOrderForSupplier($supplier, 'OC-2026-014', $creator);

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
        $this->assertSame(1, $supplierUser->unreadNotifications()->count());
        $this->assertSame(1, $creator->unreadNotifications()->count());
        $this->assertSame(
            'payment_validated',
            $supplierUser->unreadNotifications()->firstOrFail()->data['event'],
        );

        $this->post(route('admin.purchase-orders.validate-payment', $purchaseOrder))
            ->assertRedirect(route('home'));

        $this->assertSame(1, $supplierUser->unreadNotifications()->count());
        $this->assertSame(1, $creator->unreadNotifications()->count());

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
        $this->assertSame(2, $creator->unreadNotifications()->count());
        $this->assertTrue(
            $creator->unreadNotifications()->get()->pluck('data.event')->contains('payment_complement_uploaded'),
        );
        $this->assertSame(1, $administrator->unreadNotifications()->count());
        $this->assertSame(1, $financialUser->unreadNotifications()->count());
        Storage::disk('local')->assertEmpty();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Complemento recibido')
            ->assertDontSee('Adjunta complemento de pago');
    }

    public function test_supplier_can_replace_a_missing_legacy_payment_complement_and_internal_users_can_download_it(): void
    {
        Storage::fake('local');
        $supplierUser = $this->createUserWithType('suppliers', 'proveedor@example.com');
        $internalUser = $this->createUserWithType('departments', 'usuario@example.com');
        $supplier = $this->createSupplier('22', $supplierUser->email);
        $purchaseOrder = $this->createPurchaseOrderForSupplier(
            $supplier,
            'OC-2026-022',
            User::factory()->create(),
        );
        $purchaseOrder->forceFill([
            'payment_validated_at' => now(),
            'payment_complement_path' => 'payment-complements/missing.pdf',
        ])->save();
        $paymentComplement = UploadedFile::fake()->create('complemento.pdf', 10, 'application/pdf');

        $this->actingAs($supplierUser)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('El complemento anterior no está disponible. Vuelve a adjuntarlo.')
            ->assertSee('Adjunta complemento de pago');

        $this->actingAs($supplierUser)
            ->post(route('supplier.purchase-orders.payment-complement.store', $purchaseOrder), [
                'payment_complement' => $paymentComplement,
                'review_confirmed' => '1',
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHas('success');

        $this->actingAs($supplierUser)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Complemento recibido')
            ->assertDontSee('El complemento anterior no está disponible.');

        $this->actingAs($internalUser)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Descargar complemento');

        $this->get(route('purchase-orders.documents.download', [$purchaseOrder, 'payment-complement']))
            ->assertDownload('oc-2026-022-complemento-pago.pdf')
            ->assertStreamedContent($paymentComplement->getContent());
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
        $purchaseOrderPdf = UploadedFile::fake()->create('orden-compra.pdf', 10, 'application/pdf');
        $purchaseOrder = $this->createPurchaseOrderForSupplier(
            $this->createSupplier('17', $supplierUser->email),
            'OC-2026-016',
            User::factory()->create(),
        );
        UploadedDocument::replaceFor($purchaseOrder, 'purchase_order_pdf', $purchaseOrderPdf);
        $otherPurchaseOrder = $this->createPurchaseOrderForSupplier($otherSupplier, 'OC-2026-017', User::factory()->create());

        $this->actingAs($supplierUser)
            ->get(route('supplier.purchase-orders.pdf', $purchaseOrder))
            ->assertDownload('oc-2026-016.pdf')
            ->assertStreamedContent($purchaseOrderPdf->getContent());

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

    private function createInvoiceXml(
        PurchaseOrder $purchaseOrder,
        ?string $amount = null,
        ?string $rfc = null,
    ): UploadedFile {
        $content = sprintf(
            '<cfdi:Comprobante xmlns:cfdi="http://www.sat.gob.mx/cfd/4" Total="%s">'
            .'<cfdi:Emisor Rfc="%s"/>'
            .'</cfdi:Comprobante>',
            $amount ?? $purchaseOrder->amount,
            $rfc ?? $purchaseOrder->supplier->rfc,
        );

        return UploadedFile::fake()->createWithContent('factura.xml', $content)->mimeType('text/xml');
    }
}
