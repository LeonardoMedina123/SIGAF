<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $rfc = $request->input('rfc');

        if (is_string($rfc)) {
            $request->merge(['rfc' => strtoupper($rfc)]);
        }

        $validated = $request->validate([
            'number' => ['required', 'string', 'max:50', 'unique:suppliers,number'],
            'name' => ['required', 'string', 'max:255'],
            'rfc' => ['bail', 'required', 'string', 'regex:/^[A-Z&Ñ]{3,4}\d{6}[A-Z0-9]{3}$/iu', 'unique:suppliers,rfc'],
            'curp' => ['nullable', 'string', 'size:18'],
            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'representative' => ['nullable', 'string', 'max:255'],
            'cellphone' => ['nullable', 'string', 'max:30'],
            'economic_activity' => ['nullable', 'string', 'max:255'],
            'iva_percentage' => ['required', 'numeric', 'between:0,100'],
            'status' => ['required', 'string', 'max:100'],
            'it_capture' => ['nullable', 'string', 'max:255'],
        ]);

        Supplier::create($validated);

        return redirect()->route('suppliers.index')->with('success', 'El proveedor se agregó correctamente.');
    }
}
