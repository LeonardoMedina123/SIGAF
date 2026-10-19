<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Requisition;
use App\Models\UploadedDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'requisition_pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'quote_email' => ['required', 'email', 'max:255'],
            'quote_amount' => ['required', 'numeric', 'min:0.01', 'decimal:0,2'],
            'delivery_location' => ['required', 'string', 'max:1000'],
            'receiver' => ['required', 'string', 'max:255'],
            'receiver_phone' => ['required', 'string', 'max:30'],
            'review_confirmed' => ['accepted'],
            'purchase_order_folio' => ['required', 'string', 'max:100', 'unique:purchase_orders,folio'],
            'purchase_order_date' => ['required', 'date'],
            'purchase_order_partida' => ['nullable', 'string', 'max:100'],
            'purchase_order_iva_included' => ['nullable', 'boolean'],
            'purchase_order_rms' => ['nullable', 'string', 'max:255'],
            'purchase_order_rf' => ['nullable', 'string', 'max:255'],
            'purchase_order_pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'purchase_order_xml' => ['required', 'file', 'mimes:xml', 'max:10240'],
        ]);

        DB::transaction(function () use ($validated, $request): void {
            $requisition = Requisition::create([
                'folio' => $validated['requisition_folio'],
                'created_on' => $validated['requisition_created_on'],
                'requested_on' => $validated['requisition_requested_on'],
                'amount' => $validated['requisition_amount'],
                'partida' => $validated['item_number'],
                'sp' => $validated['requisition_sp'] ?? null,
                'ppp' => $validated['requisition_ppp'] ?? null,
                'sup' => $validated['requisition_sup'] ?? null,
                'dir' => $validated['requisition_dir'] ?? null,
                'process' => $validated['requisition_process'] ?? null,
                'rf' => $validated['requisition_rf'] ?? null,
                'pdf_path' => null,
                'user_id' => $request->user()->id,
            ]);

            $purchaseOrder = PurchaseOrder::create([
                'folio' => $validated['purchase_order_folio'],
                'order_date' => $validated['purchase_order_date'],
                'requisition_id' => $requisition->id,
                'supplier_id' => $validated['supplier_id'],
                'amount' => $validated['quote_amount'],
                'partida' => $validated['purchase_order_partida'] ?? null,
                'iva_included' => $validated['purchase_order_iva_included'] ?? null,
                'rms' => $validated['purchase_order_rms'] ?? null,
                'rf' => $validated['purchase_order_rf'] ?? null,
                'contact_email' => $validated['quote_email'],
                'delivery_location' => $validated['delivery_location'],
                'receiver' => $validated['receiver'],
                'receiver_phone' => $validated['receiver_phone'],
                'pdf_path' => null,
                'xml_path' => null,
            ]);

            UploadedDocument::replaceFor(
                $requisition,
                'requisition_pdf',
                $validated['requisition_pdf'],
            );
            UploadedDocument::replaceFor(
                $purchaseOrder,
                'purchase_order_pdf',
                $validated['purchase_order_pdf'],
            );
            UploadedDocument::replaceFor(
                $purchaseOrder,
                'purchase_order_xml',
                $validated['purchase_order_xml'],
            );
        });

        return redirect()->route('home')->with('success', 'La requisición y la orden de compra se guardaron correctamente.');
    }
}
