<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('departments')->insertOrIgnore([
            'name' => 'Compras',
            'slug' => 'purchases',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('departments')
            ->where('slug', 'purchases')
            ->delete();
    }
};
