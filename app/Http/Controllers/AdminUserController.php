<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminUserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::with('department')->orderBy('name')->paginate(20),
            'departments' => Department::orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'department_id' => ['required', 'exists:departments,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'El usuario se creó correctamente.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'department_id' => ['required', 'exists:departments,id'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        DB::transaction(function () use ($user, $validated): void {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if (
                $lockedUser->isAdministrator()
                && (int) $validated['department_id'] !== (int) $lockedUser->department_id
                && $this->administratorCount() <= 1
            ) {
                throw ValidationException::withMessages([
                    'department_id' => 'No se puede cambiar el tipo del último administrador.',
                ]);
            }

            $lockedUser->fill($validated)->save();
        });

        return redirect()->route('admin.users.index')->with('success', 'Los datos del usuario se actualizaron correctamente.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->is(auth()->user()), 403, 'No puedes eliminar tu propia cuenta.');

        DB::transaction(function () use ($user): void {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($lockedUser->requisitions()->exists()) {
                throw ValidationException::withMessages([
                    'user' => 'No se puede eliminar un usuario con requisiciones registradas para conservar el historial.',
                ]);
            }

            if ($lockedUser->isAdministrator() && $this->administratorCount() <= 1) {
                throw ValidationException::withMessages([
                    'user' => 'No se puede eliminar al último administrador.',
                ]);
            }

            $lockedUser->delete();
        });

        return redirect()->route('admin.users.index')->with('success', 'El usuario se eliminó correctamente.');
    }

    private function administratorCount(): int
    {
        $administratorId = Department::query()
            ->where('slug', 'administrator')
            ->value('id');

        return User::query()
            ->where('department_id', $administratorId)
            ->lockForUpdate()
            ->get(['id'])
            ->count();
    }
}
