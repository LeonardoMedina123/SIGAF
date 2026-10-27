<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class SystemsDepartmentUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('services.systems_department_user.email');
        $password = config('services.systems_department_user.password');

        if (! is_string($email) || $email === '' || ! is_string($password) || $password === '') {
            throw new RuntimeException('Configure SYSTEMS_DEPARTMENT_USER_EMAIL y SYSTEMS_DEPARTMENT_USER_PASSWORD antes de ejecutar los seeders.');
        }

        $this->call(DepartmentSeeder::class);

        $department = Department::query()
            ->where('slug', 'systems')
            ->firstOrFail();

        $user = User::firstOrNew(['email' => $email]);

        if (! $user->exists) {
            $user->password = $password;
        }

        $user->fill([
            'name' => 'Mayra Vanessa Capetillo González',
            'department_id' => $department->id,
        ])->save();
    }
}
