<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Requisition;
use App\Models\UploadedDocument;
use App\Models\User;
use App\Notifications\UserActivityNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class OrderController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->validationRules());

        DB::transaction(function () use ($validated, $request): void {
            $requisition = Requisition::create([
                ...$this->requisitionAttributes($validated),
                'pdf_path' => null,
                'user_id' => $request->user()->id,
            ]);

            $purchaseOrder = PurchaseOrder::create([
                ...$this->purchaseOrderAttributes($validated),
                'requisition_id' => $requisition->id,
                'pdf_path' => null,
                'xml_path' => null,
            ]);

            $this->replaceUploadedDocuments($requisition, $purchaseOrder, $validated);

            $supplierEmail = $purchaseOrder->supplier()->value('contact_email');
            $supplierUsers = User::query()
                ->whereRaw('LOWER(email) = ?', [Str::lower((string) $supplierEmail)])
                ->whereHas('department', function (Builder $query): void {
                    $query->where('slug', 'suppliers');
                })
                ->get();

            Notification::send($supplierUsers, new UserActivityNotification(
                'purchase_order_created',
                'Nueva orden de compra',
                'Recibiste la orden de compra '.$purchaseOrder->folio.'.',
            ));
        });

        return redirect()->route('home')->with('success', 'La requisición y la orden de compra se guardaron correctamente.');
    }

    public function edit(Request $request, Requisition $requisition): JsonResponse
    {
        $this->authorizeOrderEditing($request);

        $purchaseOrder = $requisition->purchaseOrder()
            ->with('uploadedDocuments:id,purchase_order_id,kind,original_name')
            ->firstOrFail();
        $requisition->load('uploadedDocuments:id,requisition_id,kind,original_name');

        return response()->json([
            'requisition_folio' => $requisition->folio,
            'requisition_created_on' => $requisition->created_on?->toDateString(),
            'requisition_requested_on' => $requisition->requested_on?->toDateString(),
            'requisition_amount' => $requisition->amount,
            'item_number' => $requisition->partida,
            'requisition_sp' => $requisition->sp,
            'requisition_ppp' => $requisition->ppp,
            'requisition_sup' => $requisition->sup,
            'requisition_dir' => $requisition->dir,
            'requisition_process' => $requisition->process,
            'requisition_rf' => $requisition->rf,
            'requisition_pdf_name' => $requisition->uploadedDocuments->firstWhere('kind', 'requisition_pdf')?->original_name,
            'supplier_id' => $purchaseOrder->supplier_id,
            'quote_email' => $purchaseOrder->contact_email,
            'delivery_location' => $purchaseOrder->delivery_location,
            'receiver' => $purchaseOrder->receiver,
            'receiver_phone' => $purchaseOrder->receiver_phone,
            'purchase_order_folio' => $purchaseOrder->folio,
            'purchase_order_date' => $purchaseOrder->order_date?->toDateString(),
            'purchase_order_partida' => $purchaseOrder->partida,
            'purchase_order_iva_included' => $purchaseOrder->iva_included === null
                ? ''
                : (int) $purchaseOrder->iva_included,
            'purchase_order_rms' => $purchaseOrder->rms,
            'purchase_order_rf' => $purchaseOrder->rf,
            'purchase_order_pdf_name' => $purchaseOrder->uploadedDocuments->firstWhere('kind', 'purchase_order_pdf')?->original_name,
        ]);
    }

    public function update(Request $request, Requisition $requisition): RedirectResponse
    {
        $this->authorizeOrderEditing($request);

        $purchaseOrder = $requisition->purchaseOrder()->firstOrFail();
        $validated = $request->validate($this->validationRules($requisition, $purchaseOrder));

        DB::transaction(function () use ($requisition, $purchaseOrder, $validated): void {
            $requisition->update($this->requisitionAttributes($validated));
            $purchaseOrder->update($this->purchaseOrderAttributes($validated));
            $this->replaceUploadedDocuments($requisition, $purchaseOrder, $validated);
        });

        return redirect()->route('home')->with('success', 'La requisición y la orden de compra se actualizaron correctamente.');
    }

    /**
     * @return array<string, array<int, string|Unique>>
     */
    private function validationRules(?Requisition $requisition = null, ?PurchaseOrder $purchaseOrder = null): array
    {
        $requisitionFolioUniqueRule = Rule::unique('requisitions', 'folio');
        if ($requisition !== null) {
            $requisitionFolioUniqueRule->ignore($requisition);
        }

        $purchaseOrderFolioUniqueRule = Rule::unique('purchase_orders', 'folio');
        if ($purchaseOrder !== null) {
            $purchaseOrderFolioUniqueRule->ignore($purchaseOrder);
        }

        $requisitionPdfRequirement = $requisition === null
            || ! $requisition->uploadedDocuments()->where('kind', 'requisition_pdf')->exists()
            ? 'required'
            : 'nullable';
        $purchaseOrderPdfRequirement = $purchaseOrder === null
            || ! $purchaseOrder->uploadedDocuments()->where('kind', 'purchase_order_pdf')->exists()
            ? 'required'
            : 'nullable';

        return [
            'requisition_folio' => ['required', 'string', 'max:100', $requisitionFolioUniqueRule],
            'requisition_created_on' => ['required', 'date'],
            'requisition_requested_on' => ['required', 'date'],
            'requisition_amount' => ['required', 'numeric', 'min:0.01', 'decimal:0,2'],
            'item_number' => ['required', 'string', 'max:100'],
            'requisition_sp' => ['nullable', 'string', 'max:255'],
            'requisition_ppp' => ['nullable', 'string', 'max:255'],
            'requisition_sup' => ['nullable', 'string', 'max:255'],
            'requisition_dir' => ['nullable', 'string', 'max:255'],
            'requisition_process' => ['nullable', 'string', 'max:255'],
            'requisition_rf' => ['nullable', 'string', 'max:255'],
            'requisition_pdf' => [$requisitionPdfRequirement, 'file', 'mimes:pdf', 'max:10240'],
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'quote_email' => ['required', 'email', 'max:255'],
            'delivery_location' => ['required', 'string', 'max:1000'],
            'receiver' => ['required', 'string', 'max:255'],
            'receiver_phone' => ['required', 'string', 'max:30'],
            'review_confirmed' => ['accepted'],
            'purchase_order_folio' => ['required', 'string', 'max:100', $purchaseOrderFolioUniqueRule],
            'purchase_order_date' => ['required', 'date'],
            'purchase_order_partida' => ['nullable', 'string', 'max:100'],
            'purchase_order_iva_included' => ['nullable', 'boolean'],
            'purchase_order_rms' => ['nullable', 'string', 'max:255'],
            'purchase_order_rf' => ['nullable', 'string', 'max:255'],
            'purchase_order_pdf' => [$purchaseOrderPdfRequirement, 'file', 'mimes:pdf', 'max:10240'],
            'purchase_order_xml' => ['nullable', 'file', 'mimes:xml', 'max:10240'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function requisitionAttributes(array $validated): array
    {
        return [
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
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function purchaseOrderAttributes(array $validated): array
    {
        return [
            'folio' => $validated['purchase_order_folio'],
            'order_date' => $validated['purchase_order_date'],
            'supplier_id' => $validated['supplier_id'],
            'amount' => $validated['requisition_amount'],
            'partida' => $validated['purchase_order_partida'] ?? null,
            'iva_included' => $validated['purchase_order_iva_included'] ?? null,
            'rms' => $validated['purchase_order_rms'] ?? null,
            'rf' => $validated['purchase_order_rf'] ?? null,
            'contact_email' => $validated['quote_email'],
            'delivery_location' => $validated['delivery_location'],
            'receiver' => $validated['receiver'],
            'receiver_phone' => $validated['receiver_phone'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function replaceUploadedDocuments(
        Requisition $requisition,
        PurchaseOrder $purchaseOrder,
        array $validated,
    ): void {
        if (isset($validated['requisition_pdf'])) {
            UploadedDocument::replaceFor($requisition, 'requisition_pdf', $validated['requisition_pdf']);
        }

        if (isset($validated['purchase_order_pdf'])) {
            UploadedDocument::replaceFor($purchaseOrder, 'purchase_order_pdf', $validated['purchase_order_pdf']);
        }

        if (isset($validated['purchase_order_xml'])) {
            UploadedDocument::replaceFor($purchaseOrder, 'purchase_order_xml', $validated['purchase_order_xml']);
        }
    }

    private function authorizeOrderEditing(Request $request): void
    {
        $user = $request->user();
        abort_unless($user instanceof User && ! $user->isSupplier() && ! $user->isSystemsDepartment(), 403);
    }
}
