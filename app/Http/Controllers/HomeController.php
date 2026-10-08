<?php

namespace App\Http\Controllers;

use App\Models\Requisition;
use App\Models\Supplier;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        if (auth()->user()->isSupplier()) {
            return view('suppliers.portal', [
                'purchaseOrders' => array_map(
                    static fn (int $orderNumber): array => [
                        'folio' => sprintf('Orden_Compra_TECNM_%d', 2515485 + $orderNumber),
                        'documentsSubmitted' => $orderNumber > 1,
                        'modifiedAt' => '08/10/2026',
                    ],
                    range(1, 8),
                ),
            ]);
        }

        return view('home', [
            'requisitions' => Requisition::with('purchaseOrder')->latest()->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function suppliers(): View
    {
        return view('suppliers.index', [
            'suppliers' => Supplier::orderBy('name')->paginate(20),
        ]);
    }
}
