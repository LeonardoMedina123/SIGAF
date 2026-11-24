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
        $validated = $this->validatedSupplierData($request);

        DB::transaction(function () use ($validated): void {
            Supplier::create($validated);

            if (isset($validated['contact_email'])) {
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
            }
        });

        return redirect()->route('suppliers.index')->with(
            'success',
            'El proveedor se agregó correctamente. Si el correo no tenía cuenta, se creó el acceso al portal de proveedor con la contraseña temporal 12345.',
        );
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($this->validatedSupplierData($request, $supplier));

        return redirect()->route('suppliers.index')->with('success', 'El proveedor se actualizó correctamente.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedSupplierData(Request $request, ?Supplier $supplier = null): array
    {
        $rfc = $request->input('rfc');

        if (is_string($rfc)) {
            $request->merge(['rfc' => strtoupper($rfc)]);
        }

        $curp = $request->input('curp');

        if (is_string($curp)) {
            $request->merge(['curp' => strtoupper($curp)]);
        }

        $numberRule = Rule::unique('suppliers', 'number');
        $rfcRule = Rule::unique('suppliers', 'rfc');

        if ($supplier !== null) {
            $numberRule->ignore($supplier->getKey());
            $rfcRule->ignore($supplier->getKey());
        }

        $rules = [
            'number' => ['required', 'string', 'max:50', $numberRule],
            'name' => ['required', 'string', 'max:255'],
            'rfc' => ['bail', 'required', 'string', 'regex:/^[A-Z&Ñ]{3,4}\d{6}[A-Z0-9]{3}$/iu', $rfcRule],
            'curp' => ['nullable', 'string', 'max:18'],
            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'representative' => ['nullable', 'string', 'max:255'],
            'cellphone' => ['nullable', 'string', 'max:30'],
            'economic_activity' => ['nullable', 'string', 'max:255'],
            'iva_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', 'string', 'max:100'],
            'it_capture' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
        ];

        return $request->validate($rules);
    }
}
