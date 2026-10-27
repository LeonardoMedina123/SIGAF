<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class SupplierPurchaseOrderDocumentController extends Controller
{
    public function storeInvoice(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorizeSupplierOrder($request, $purchaseOrder);

        $request->validate([
            'invoice_pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'invoice_xml' => ['required', 'file', 'mimes:xml', 'max:10240'],
            'review_confirmed' => ['accepted'],
        ]);

        $previousPaths = array_filter([
            $purchaseOrder->invoice_pdf_path,
            $purchaseOrder->invoice_xml_path,
        ]);
        $storedPaths = [];

        try {
            $invoicePdfPath = $this->storeDocument($request, 'invoice_pdf', 'supplier-invoices');
            $storedPaths[] = $invoicePdfPath;
            $invoiceXmlPath = $this->storeDocument($request, 'invoice_xml', 'supplier-invoices');
            $storedPaths[] = $invoiceXmlPath;

            $wasSaved = $purchaseOrder->forceFill([
                'invoice_pdf_path' => $invoicePdfPath,
                'invoice_xml_path' => $invoiceXmlPath,
            ])->save();

            if (! $wasSaved) {
                throw new RuntimeException('No fue posible registrar la factura y el XML.');
            }
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($storedPaths);
            throw $exception;
        }

        Storage::disk('local')->delete($previousPaths);

        return redirect()->route('home')->with('success', 'La factura y el XML se enviaron correctamente.');
    }

    public function storePaymentComplement(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $this->authorizeSupplierOrder($request, $purchaseOrder);
        abort_unless($purchaseOrder->payment_validated_at !== null, 403);

        $request->validate([
            'payment_complement' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'review_confirmed' => ['accepted'],
        ]);

        $previousPath = $purchaseOrder->payment_complement_path;
        $storedPaths = [];

        try {
            $paymentComplementPath = $this->storeDocument($request, 'payment_complement', 'payment-complements');
            $storedPaths[] = $paymentComplementPath;

            $wasSaved = $purchaseOrder->forceFill([
                'payment_complement_path' => $paymentComplementPath,
            ])->save();

            if (! $wasSaved) {
                throw new RuntimeException('No fue posible registrar el complemento de pago.');
            }
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($storedPaths);
            throw $exception;
        }

        if ($previousPath !== null) {
            Storage::disk('local')->delete($previousPath);
        }

        return redirect()->route('home')->with('success', 'El complemento de pago se envió correctamente.');
    }

    public function downloadPurchaseOrder(Request $request, PurchaseOrder $purchaseOrder): StreamedResponse
    {
        $this->authorizeSupplierOrder($request, $purchaseOrder);
        abort_unless(Storage::disk('local')->exists($purchaseOrder->pdf_path), 404);

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
            'purchase-order-pdf' => [$purchaseOrder->pdf_path, 'orden-compra.pdf'],
            'invoice-pdf' => [$purchaseOrder->invoice_pdf_path, 'factura.pdf'],
            'invoice-xml' => [$purchaseOrder->invoice_xml_path, 'factura.xml'],
            'payment-complement' => [$purchaseOrder->payment_complement_path, 'complemento-pago.pdf'],
            default => abort(404),
        };

        [$path, $fileName] = $documentDetails;
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

    private function storeDocument(Request $request, string $field, string $directory): string
    {
        $path = $request->file($field)->store($directory, 'local');

        if ($path === false) {
            throw new RuntimeException("No fue posible guardar el archivo {$field}.");
        }

        return $path;
    }
}
