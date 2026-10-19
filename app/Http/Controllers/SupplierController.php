<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
            'person_type' => ['required', Rule::in(array_keys(Supplier::PERSON_TYPES))],
            'tax_regime' => ['required', Rule::in(array_keys(Supplier::TAX_REGIMES))],
            'cfdi_use' => ['required', Rule::in(array_keys(Supplier::CFDI_USES))],
            'representative' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'curp' => ['nullable', 'string', 'size:18'],
            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'cellphone' => ['nullable', 'string', 'max:30'],
            'economic_activity' => ['nullable', 'string', 'max:255'],
            'iva_percentage' => ['required', 'numeric', 'between:0,100'],
            'status' => ['required', 'string', 'max:100'],
            'it_capture' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated): void {
            Supplier::create($validated);

            $email = Str::lower($validated['contact_email']);
            $userExists = User::query()
                ->whereRaw('LOWER(email) = ?', [$email])
                ->exists();

            if (! $userExists) {
                $supplierDepartment = Department::query()
                    ->where('slug', 'suppliers')
                    ->firstOrFail();

                User::create([
                    'name' => $validated['name'],
                    'email' => $email,
                    'password' => '12345',
                    'department_id' => $supplierDepartment->id,
                ]);
            }
        });

        return redirect()->route('suppliers.index')->with(
            'success',
            'El proveedor se agregó correctamente. Si el correo no tenía cuenta, se creó el acceso al portal de proveedor con la contraseña temporal 12345.',
        );
    }
}
