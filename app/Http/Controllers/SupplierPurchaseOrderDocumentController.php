<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\UploadedDocument;
use App\Models\User;
use App\Notifications\UserActivityNotification;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupplierPurchaseOrderDocumentController extends Controller
{
    public function storeInvoice(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorizeSupplierOrder($request, $purchaseOrder);
        $hasUploadedInvoice = $purchaseOrder->hasDocument('invoice_pdf')
            || $purchaseOrder->hasDocument('invoice_xml');
        abort_unless(
            ! $hasUploadedInvoice || $purchaseOrder->invoice_edit_enabled,
            403,
        );

        $validated = $request->validate([
            'invoice_pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'invoice_xml' => ['required', 'file', 'mimes:xml', 'max:10240'],
            'review_confirmed' => ['accepted'],
        ]);

        $this->validateInvoiceXmlMatchesPurchaseOrder($validated['invoice_xml'], $purchaseOrder);

        DB::transaction(function () use ($validated, $purchaseOrder): void {
            UploadedDocument::replaceFor($purchaseOrder, 'invoice_pdf', $validated['invoice_pdf']);
            UploadedDocument::replaceFor($purchaseOrder, 'invoice_xml', $validated['invoice_xml']);
            $wasSaved = $purchaseOrder->forceFill([
                'invoice_pdf_path' => null,
                'invoice_xml_path' => null,
                'invoice_edit_enabled' => false,
            ])->save();

            if (! $wasSaved) {
                throw new RuntimeException('No fue posible registrar la factura y el XML.');
            }

            $recipients = User::query()
                ->whereKey($purchaseOrder->requisition()->value('user_id'))
                ->orWhereHas('department', function (Builder $query): void {
                    $query->whereIn('slug', ['administrator', 'financials']);
                })
                ->get();

            Notification::send($recipients, new UserActivityNotification(
                'invoice_uploaded',
                'Factura y XML recibidos',
                'El proveedor cargó la factura y el XML de la orden '.$purchaseOrder->folio.'.',
            ));
        });

        return redirect()->route('home')->with('success', 'La factura y el XML se enviaron correctamente.');
    }

    private function validateInvoiceXmlMatchesPurchaseOrder(
        UploadedFile $invoiceXml,
        PurchaseOrder $purchaseOrder,
    ): void {
        $document = new DOMDocument;
        $previousLibxmlErrorSetting = libxml_use_internal_errors(true);

        try {
            $isValidXml = $document->loadXML(
                $invoiceXml->getContent(),
                LIBXML_NONET | LIBXML_NOBLANKS,
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousLibxmlErrorSetting);
        }

        $root = $document->documentElement;

        if (
            ! $isValidXml
            || $root === null
            || $root->localName !== 'Comprobante'
            || ! in_array($root->namespaceURI, [
                'http://www.sat.gob.mx/cfd/3',
                'http://www.sat.gob.mx/cfd/4',
            ], true)
        ) {
            throw ValidationException::withMessages([
                'invoice_xml' => 'El archivo no contiene un XML CFDI válido.',
            ]);
        }

        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('cfdi', $root->namespaceURI);
        $issuer = $xpath->query('/cfdi:Comprobante/cfdi:Emisor')->item(0);

        if (! $issuer instanceof DOMElement) {
            throw ValidationException::withMessages([
                'invoice_xml' => 'El XML CFDI no incluye los datos del emisor.',
            ]);
        }

        $errors = [];
        $expectedRfc = mb_strtoupper(trim((string) $purchaseOrder->supplier->rfc));
        $invoiceRfc = mb_strtoupper(trim($issuer->getAttribute('Rfc')));

        if ($invoiceRfc === '' || ! hash_equals($expectedRfc, $invoiceRfc)) {
            $errors[] = 'El RFC emisor del XML no coincide con el RFC registrado del proveedor.';
        }

        $expectedAmount = $this->amountInCents((string) $purchaseOrder->amount);
        $invoiceAmount = $this->amountInCents($root->getAttribute('Total'));

        if ($invoiceAmount === null || $expectedAmount === null || $invoiceAmount !== $expectedAmount) {
            $errors[] = 'El importe total del XML no coincide con el importe de la orden de compra.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages([
                'invoice_xml' => $errors,
            ]);
        }
    }

    private function amountInCents(string $amount): ?string
    {
        if (! preg_match('/\A(\d{1,12})(?:\.(\d{1,2}))?\z/', trim($amount), $matches)) {
            return null;
        }

        return str_pad($matches[1], 12, '0', STR_PAD_LEFT)
            .str_pad($matches[2] ?? '', 2, '0');
    }

    public function storePaymentComplement(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorizeSupplierOrder($request, $purchaseOrder);
        abort_unless($purchaseOrder->payment_validated_at !== null, 403);
        abort_unless(
            ! $purchaseOrder->hasDocument('payment_complement')
            || $purchaseOrder->payment_complement_edit_enabled,
            403,
        );

        $validated = $request->validate([
            'payment_complement' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'review_confirmed' => ['accepted'],
        ]);

        DB::transaction(function () use ($validated, $purchaseOrder): void {
            UploadedDocument::replaceFor(
                $purchaseOrder,
                'payment_complement',
                $validated['payment_complement'],
            );
            $wasSaved = $purchaseOrder->forceFill([
                'payment_complement_path' => null,
                'payment_complement_edit_enabled' => false,
            ])->save();

            if (! $wasSaved) {
                throw new RuntimeException('No fue posible registrar el complemento de pago.');
            }

            $recipients = User::query()
                ->whereKey($purchaseOrder->requisition()->value('user_id'))
                ->orWhereHas('department', function (Builder $query): void {
                    $query->whereIn('slug', ['administrator', 'financials']);
                })
                ->get();

            Notification::send($recipients, new UserActivityNotification(
                'payment_complement_uploaded',
                'Complemento de pago recibido',
                'El proveedor cargó el complemento de pago de la orden '.$purchaseOrder->folio.'.',
            ));
        });

        return redirect()->route('home')->with('success', 'El complemento de pago se envió correctamente.');
    }

    public function enableDocumentEdit(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User && ! $user->isSupplier(), 403);

        $validated = $request->validate([
            'document' => ['required', 'string', 'in:invoice,payment-complement'],
        ]);

        $field = $validated['document'] === 'invoice'
            ? 'invoice_edit_enabled'
            : 'payment_complement_edit_enabled';
        $documentKind = $validated['document'] === 'invoice'
            ? 'invoice_pdf'
            : 'payment_complement';

        abort_unless($purchaseOrder->hasDocument($documentKind), 404);
        $purchaseOrder->forceFill([$field => true])->saveOrFail();

        $supplier = $purchaseOrder->supplier;
        $phoneDigits = preg_replace('/\D+/', '', (string) $supplier->number);

        if ($phoneDigits === '') {
            return back()
                ->with('success', 'Se habilitó la modificación del documento solicitado.')
                ->with('warning', 'El campo Número del proveedor no contiene un teléfono; no se pudo preparar el aviso de WhatsApp.');
        }

        if (strlen($phoneDigits) === 10) {
            $phoneDigits = '52'.$phoneDigits;
        }

        if (strlen($phoneDigits) < 10 || strlen($phoneDigits) > 15) {
            return back()
                ->with('success', 'Se habilitó la modificación del documento solicitado.')
                ->with('warning', 'El campo Número del proveedor no tiene un formato válido para WhatsApp.');
        }

        $documentName = $validated['document'] === 'invoice'
            ? 'la factura PDF y el XML'
            : 'el complemento de pago';
        $message = 'Hola, '.$supplier->name.'. Se solicita cambiar '.$documentName
            .' de la orden de compra '.$purchaseOrder->folio
            .'. Ingresa al portal SIGAF para reemplazar los documentos.';
        $whatsAppUrl = 'https://wa.me/'.$phoneDigits.'?text='.rawurlencode($message);

        return back()
            ->with('success', 'Se habilitó la modificación. Abre WhatsApp y confirma el envío del aviso al proveedor.')
            ->with('whatsapp_url', $whatsAppUrl);
    }

    public function downloadPurchaseOrder(Request $request, PurchaseOrder $purchaseOrder): StreamedResponse
    {
        $this->authorizeSupplierOrder($request, $purchaseOrder);

        $document = $this->findDocument($purchaseOrder, 'purchase_order_pdf');
        abort_if($document === null, 404);

        return $this->downloadUploadedDocument($document, Str::slug($purchaseOrder->folio).'.pdf');
    }

    public function previewPurchaseOrder(Request $request, PurchaseOrder $purchaseOrder): StreamedResponse
    {
        $this->authorizeSupplierOrder($request, $purchaseOrder);

        $document = $this->findDocument($purchaseOrder, 'purchase_order_pdf');
        abort_if($document === null, 404);

        return response()->stream(
            static function () use ($document): void {
                echo $document->content;
            },
            200,
            [
                'Content-Type' => $document->mime_type,
                'Content-Disposition' => 'inline; filename="'.Str::slug($purchaseOrder->folio).'.pdf"',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }

    public function downloadInternalDocument(
        Request $request,
        PurchaseOrder $purchaseOrder,
        string $document,
    ): StreamedResponse {
        $user = $request->user();
        abort_unless($user instanceof User && ! $user->isSupplier(), 403);

        $documentDetails = match ($document) {
            'purchase-order-pdf' => ['purchase_order_pdf', 'orden-compra.pdf'],
            'invoice-pdf' => ['invoice_pdf', 'factura.pdf'],
            'invoice-xml' => ['invoice_xml', 'factura.xml'],
            'payment-complement' => ['payment_complement', 'complemento-pago.pdf'],
            default => abort(404),
        };

        [$kind, $fileName] = $documentDetails;
        $storedDocument = $this->findDocument($purchaseOrder, $kind);
        abort_if($storedDocument === null, 404);

        return $this->downloadUploadedDocument(
            $storedDocument,
            Str::slug($purchaseOrder->folio).'-'.$fileName,
        );
    }

    private function authorizeSupplierOrder(Request $request, PurchaseOrder $purchaseOrder): void
    {
        $user = $request->user();
        abort_unless($user instanceof User && $user->isSupplier(), 403);

        $belongsToSupplier = Supplier::query()
            ->whereKey($purchaseOrder->supplier_id)
            ->whereRaw('LOWER(contact_email) = ?', [Str::lower($user->email)])
            ->exists();

        abort_unless($belongsToSupplier, 404);
    }

    private function findDocument(PurchaseOrder $purchaseOrder, string $kind): ?UploadedDocument
    {
        return $purchaseOrder->uploadedDocuments()->where('kind', $kind)->first();
    }

    private function downloadUploadedDocument(UploadedDocument $document, string $fileName): StreamedResponse
    {
        $content = $document->content;

        return response()->streamDownload(
            static function () use ($content): void {
                echo $content;
            },
            $fileName,
            [
                'Content-Type' => $document->mime_type,
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }
}
