<?php

namespace App\Http\Controllers;

use App\Models\Requisition;
use App\Models\Supplier;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
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
