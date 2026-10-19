<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\UploadedDocument;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

        $previousPaths = array_filter([
            $purchaseOrder->invoice_pdf_path,
            $purchaseOrder->invoice_xml_path,
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
        });

        Storage::disk('local')->delete($previousPaths);

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

        $previousPath = $purchaseOrder->payment_complement_path;

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
        });

        if ($previousPath !== null) {
            Storage::disk('local')->delete($previousPath);
        }

        return redirect()->route('home')->with('success', 'El complemento de pago se envió correctamente.');
    }

    public function downloadPurchaseOrder(Request $request, PurchaseOrder $purchaseOrder): StreamedResponse
    {
        $this->authorizeSupplierOrder($request, $purchaseOrder);

        $document = $this->findDocument($purchaseOrder, 'purchase_order_pdf');
        if ($document !== null) {
            return $this->downloadUploadedDocument($document, Str::slug($purchaseOrder->folio).'.pdf');
        }

        abort_unless(
            is_string($purchaseOrder->pdf_path) && Storage::disk('local')->exists($purchaseOrder->pdf_path),
            404,
        );

        return Storage::disk('local')->download($purchaseOrder->pdf_path, Str::slug($purchaseOrder->folio).'.pdf');
    }

    public function downloadInternalDocument(
        Request $request,
        PurchaseOrder $purchaseOrder,
        string $document,
    ): StreamedResponse {
        $user = $request->user();
        abort_unless($user instanceof User && ! $user->isSupplier(), 403);

        $documentDetails = match ($document) {
            'invoice-pdf' => ['invoice_pdf', $purchaseOrder->invoice_pdf_path, 'factura.pdf'],
            'invoice-xml' => ['invoice_xml', $purchaseOrder->invoice_xml_path, 'factura.xml'],
            'payment-complement' => ['payment_complement', $purchaseOrder->payment_complement_path, 'complemento-pago.pdf'],
            default => abort(404),
        };

        [$kind, $path, $fileName] = $documentDetails;
        $storedDocument = $this->findDocument($purchaseOrder, $kind);

        if ($storedDocument !== null) {
            return $this->downloadUploadedDocument(
                $storedDocument,
                Str::slug($purchaseOrder->folio).'-'.$fileName,
            );
        }

        abort_unless(is_string($path) && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download(
            $path,
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
