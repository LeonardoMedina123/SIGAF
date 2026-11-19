<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\UserActivityNotification;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class HelpController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user instanceof User && ! $user->isAdministrator(), 403);

        $supportPhone = (string) config('services.support.phone', '');

        return view('help.index', [
            'administrators' => $this->administrators(),
            'supportPhone' => $supportPhone,
            'supportPhoneDigits' => preg_replace('/\D+/', '', $supportPhone) ?? '',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User && ! $user->isAdministrator(), 403);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ], [
            'message.required' => 'Escribe tu mensaje para poder enviarlo.',
            'message.max' => 'El mensaje no puede exceder los 5000 caracteres.',
        ]);

        $administrators = $this->administrators();
        abort_if($administrators->isEmpty(), 503, 'No hay administradores disponibles para recibir el mensaje.');

        Notification::send($administrators, new UserActivityNotification(
            'support_message',
            'Nuevo mensaje de ayuda de '.$user->name,
            'Correo: '.$user->email."\n\n".$validated['message'],
        ));

        return redirect()->route('help.index')->with('success', 'Tu mensaje se envió a las cuentas administradoras.');
    }

    /**
     * @return Collection<int, User>
     */
    private function administrators(): Collection
    {
        return User::query()
            ->whereHas('department', static function (Builder $query): void {
                $query->where('slug', 'administrator');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
