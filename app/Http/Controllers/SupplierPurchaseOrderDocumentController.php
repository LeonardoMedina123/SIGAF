<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\UploadedDocument;
use App\Models\User;
use App\Notifications\UserActivityNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupplierPurchaseOrderDocumentController extends Controller
{
    public function storeInvoice(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorizeSupplierOrder($request, $purchaseOrder);

        $validated = $request->validate([
            'invoice_pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'invoice_xml' => ['required', 'file', 'mimes:xml', 'max:10240'],
            'review_confirmed' => ['accepted'],
        ]);

        DB::transaction(function () use ($validated, $purchaseOrder): void {
            UploadedDocument::replaceFor($purchaseOrder, 'invoice_pdf', $validated['invoice_pdf']);
            UploadedDocument::replaceFor($purchaseOrder, 'invoice_xml', $validated['invoice_xml']);
            $wasSaved = $purchaseOrder->forceFill([
                'invoice_pdf_path' => null,
                'invoice_xml_path' => null,
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

    public function storePaymentComplement(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorizeSupplierOrder($request, $purchaseOrder);
        abort_unless($purchaseOrder->payment_validated_at !== null, 403);

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

    public function downloadPurchaseOrder(Request $request, PurchaseOrder $purchaseOrder): StreamedResponse
    {
        $this->authorizeSupplierOrder($request, $purchaseOrder);

        $document = $this->findDocument($purchaseOrder, 'purchase_order_pdf');
        abort_if($document === null, 404);

        return $this->downloadUploadedDocument($document, Str::slug($purchaseOrder->folio).'.pdf');
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
