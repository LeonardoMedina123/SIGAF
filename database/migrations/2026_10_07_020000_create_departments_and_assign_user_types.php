<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        $now = now();
        DB::table('departments')->insert([
            ['name' => 'Administrador', 'slug' => 'administrator', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Proveedores', 'slug' => 'suppliers', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Departamentos', 'slug' => 'departments', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Financieros', 'slug' => 'financials', 'created_at' => $now, 'updated_at' => $now],
        ]);

        $departmentIds = DB::table('departments')->pluck('id', 'slug');

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->restrictOnDelete();
        });

        foreach (DB::table('users')->select('id', 'department')->get() as $user) {
            $oldDepartment = mb_strtolower((string) $user->department);
            $slug = match (true) {
                str_contains($oldDepartment, 'admin') => 'administrator',
                str_contains($oldDepartment, 'proveedor') => 'suppliers',
                str_contains($oldDepartment, 'financier') => 'financials',
                default => 'departments',
            };

            DB::table('users')
                ->where('id', $user->id)
                ->update(['department_id' => $departmentIds[$slug]]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable(false)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('department');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('department')->nullable();
        });

        foreach (DB::table('users')->join('departments', 'users.department_id', '=', 'departments.id')
            ->select('users.id', 'departments.name')
            ->get() as $user) {
            DB::table('users')->where('id', $user->id)->update(['department' => $user->name]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
        });

        Schema::dropIfExists('departments');
    }
};
