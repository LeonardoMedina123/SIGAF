<?php

namespace App\Http\Controllers;

use App\Models\Requisition;
use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index(): View
    {
        if (auth()->user()->isSupplier()) {
            $supplier = Supplier::query()
                ->whereRaw('LOWER(contact_email) = ?', [Str::lower(auth()->user()->email)])
                ->first();

            return view('suppliers.portal', [
                'purchaseOrders' => $supplier?->purchaseOrders()
                    ->with('uploadedDocuments:id,purchase_order_id,kind')
                    ->latest('order_date')
                    ->get() ?? collect(),
            ]);
        }

        return view('home', [
            'requisitions' => Requisition::with([
                'purchaseOrder.supplier',
                'purchaseOrder.uploadedDocuments:id,purchase_order_id,kind',
            ])->latest()->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'canValidatePayments' => auth()->user()->isAdministrator(),
        ]);
    }

    public function suppliers(): View
    {
        return view('suppliers.index', [
            'suppliers' => Supplier::orderBy('name')->paginate(20),
            'personTypes' => Supplier::PERSON_TYPES,
            'taxRegimes' => Supplier::TAX_REGIMES,
            'cfdiUses' => Supplier::CFDI_USES,
        ]);
    }
}
