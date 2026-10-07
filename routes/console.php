<?php

use Illuminate\Foundation\Inspiring;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('users:promote {email : Email del usuario que se convertirá en administrador}', function (string $email): int {
    $user = User::query()->where('email', $email)->first();

    if (! $user) {
        $this->error('No se encontró un usuario con ese correo.');

        return self::FAILURE;
    }

    $department = Department::query()->where('slug', 'administrator')->firstOrFail();
    $user->department()->associate($department)->save();

    $this->info("El usuario {$user->email} ahora es administrador.");

    return self::SUCCESS;
})->purpose('Asignar el tipo Administrador a una cuenta existente');
