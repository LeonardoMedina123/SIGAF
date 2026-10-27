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
                'purchaseOrders' => $supplier?->purchaseOrders()->latest('order_date')->get() ?? collect(),
            ]);
        }

        if (auth()->user()->isSystemsDepartment()) {
            return view('systems.portal', [
                'reimbursements' => array_map(
                    static fn (int $orderNumber): array => [
                        'folio' => 'Orden_TECNM_2515486',
                        'documentsSubmitted' => $orderNumber > 1,
                        'status' => match (true) {
                            $orderNumber === 1 => 'Enviado',
                            $orderNumber < 4 => 'En proceso',
                            default => 'Completado',
                        },
                        'modifiedAt' => '08/10/2026',
                    ],
                    range(1, 9),
                ),
            ]);
        }

        return view('home', [
            'requisitions' => Requisition::with('purchaseOrder.supplier')->latest()->get(),
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
