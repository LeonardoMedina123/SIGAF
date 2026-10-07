<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Requisition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class OrderController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'requisition_folio' => ['required', 'string', 'max:100', 'unique:requisitions,folio'],
            'requisition_created_on' => ['required', 'date'],
            'requisition_requested_on' => ['required', 'date'],
            'requisition_amount' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'item_number' => ['required', 'string', 'max:100'],
            'requisition_sp' => ['nullable', 'string', 'max:255'],
            'requisition_ppp' => ['nullable', 'string', 'max:255'],
            'requisition_sup' => ['nullable', 'string', 'max:255'],
            'requisition_dir' => ['nullable', 'string', 'max:255'],
            'requisition_process' => ['nullable', 'string', 'max:255'],
            'requisition_rf' => ['nullable', 'string', 'max:255'],
            'budget_key' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit' => ['required', 'string', 'max:100'],
            'unit_cost' => ['required', 'numeric', 'min:0.01', 'decimal:0,2'],
            'description' => ['required', 'string', 'max:5000'],
            'department' => ['required', 'string', 'max:255'],
            'needed_by' => ['required', 'date'],
            'justification' => ['required', 'string', 'max:5000'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'quote_email' => ['required', 'email', 'max:255'],
            'quote_amount' => ['required', 'numeric', 'min:0.01', 'decimal:0,2'],
            'delivery_location' => ['required', 'string', 'max:1000'],
            'receiver' => ['required', 'string', 'max:255'],
            'receiver_phone' => ['required', 'string', 'max:30'],
            'budget_confirmed' => ['accepted'],
            'review_confirmed' => ['accepted'],
            'purchase_order_folio' => ['required', 'string', 'max:100', 'unique:purchase_orders,folio'],
            'purchase_order_date' => ['required', 'date'],
            'purchase_order_partida' => ['required', 'string', 'max:100'],
            'purchase_order_iva' => ['required', 'numeric', 'between:0,100'],
            'purchase_order_rms' => ['nullable', 'string', 'max:255'],
            'purchase_order_rf' => ['nullable', 'string', 'max:255'],
            'requisition_pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'requisition_xml' => ['required', 'file', 'mimes:xml', 'max:10240'],
            'purchase_order_pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'purchase_order_xml' => ['required', 'file', 'mimes:xml', 'max:10240'],
        ]);

        $storedPaths = [];

        try {
            $requisitionPdf = $this->storeDocument($request, 'requisition_pdf', 'requisitions');
            $storedPaths[] = $requisitionPdf;
            $requisitionXml = $this->storeDocument($request, 'requisition_xml', 'requisitions');
            $storedPaths[] = $requisitionXml;
            $purchaseOrderPdf = $this->storeDocument($request, 'purchase_order_pdf', 'purchase-orders');
            $storedPaths[] = $purchaseOrderPdf;
            $purchaseOrderXml = $this->storeDocument($request, 'purchase_order_xml', 'purchase-orders');
            $storedPaths[] = $purchaseOrderXml;

            DB::transaction(function () use ($validated, $request, $requisitionPdf, $requisitionXml, $purchaseOrderPdf, $purchaseOrderXml): void {
                $requisition = Requisition::create([
                    'folio' => $validated['requisition_folio'],
                    'created_on' => $validated['requisition_created_on'],
                    'requested_on' => $validated['requisition_requested_on'],
                    'needed_by' => $validated['needed_by'],
                    'amount' => $validated['requisition_amount'],
                    'partida' => $validated['item_number'],
                    'sp' => $validated['requisition_sp'] ?? null,
                    'ppp' => $validated['requisition_ppp'] ?? null,
                    'sup' => $validated['requisition_sup'] ?? null,
                    'dir' => $validated['requisition_dir'] ?? null,
                    'process' => $validated['requisition_process'] ?? null,
                    'rf' => $validated['requisition_rf'] ?? null,
                    'budget_key' => $validated['budget_key'],
                    'quantity' => $validated['quantity'],
                    'unit' => $validated['unit'],
                    'unit_cost' => $validated['unit_cost'],
                    'description' => $validated['description'],
                    'department' => $validated['department'],
                    'justification' => $validated['justification'],
                    'budget_confirmed' => true,
                    'pdf_path' => $requisitionPdf,
                    'xml_path' => $requisitionXml,
                    'user_id' => $request->user()->id,
                ]);

                PurchaseOrder::create([
                    'folio' => $validated['purchase_order_folio'],
                    'order_date' => $validated['purchase_order_date'],
                    'requisition_id' => $requisition->id,
                    'supplier_id' => $validated['supplier_id'],
                    'amount' => $validated['quote_amount'],
                    'partida' => $validated['purchase_order_partida'],
                    'iva_percentage' => $validated['purchase_order_iva'],
                    'rms' => $validated['purchase_order_rms'] ?? null,
                    'rf' => $validated['purchase_order_rf'] ?? null,
                    'contact_email' => $validated['quote_email'],
                    'delivery_location' => $validated['delivery_location'],
                    'receiver' => $validated['receiver'],
                    'receiver_phone' => $validated['receiver_phone'],
                    'pdf_path' => $purchaseOrderPdf,
                    'xml_path' => $purchaseOrderXml,
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($storedPaths);
            throw $exception;
        }

        return redirect()->route('home')->with('success', 'La requisición y la orden de compra se guardaron correctamente.');
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
