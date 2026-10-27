<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Administrador', 'slug' => 'administrator'],
            ['name' => 'Proveedores', 'slug' => 'suppliers'],
            ['name' => 'Departamentos', 'slug' => 'departments'],
            ['name' => 'Compras', 'slug' => 'purchases'],
            ['name' => 'Financieros', 'slug' => 'financials'],
            ['name' => 'Departamento de Sistemas', 'slug' => 'systems'],
        ] as $department) {
            Department::updateOrCreate(['slug' => $department['slug']], $department);
        }
    }
}
