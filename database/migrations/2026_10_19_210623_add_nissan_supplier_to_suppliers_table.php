<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $contactEmail = 'Nissan@gmail.com';
        $number = 'NIS-001';

        if (DB::table('suppliers')->whereRaw('LOWER(contact_email) = ?', [strtolower($contactEmail)])->exists()) {
            return;
        }

        if (DB::table('suppliers')->where('number', $number)->exists()) {
            throw new RuntimeException('Cannot add Nissan: supplier number NIS-001 is already in use.');
        }

        DB::table('suppliers')->insert([
            'number' => $number,
            'name' => 'Nissan',
            'rfc' => 'NIS000000ABC',
            'person_type' => 'moral',
            'tax_regime' => '601',
            'cfdi_use' => 'G03',
            'contact_email' => $contactEmail,
            'address' => 'Domicilio ficticio; no válido para facturación',
            'city' => 'Aguascalientes (ficticio)',
            'phone' => '0000000000',
            'representative' => 'Contacto de prueba Nissan',
            'cellphone' => '0000000000',
            'economic_activity' => 'Comercialización de vehículos (dato ficticio)',
            'iva_percentage' => 16,
            'status' => 'Activo',
            'it_capture' => 'Registro ficticio',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supplier records are business data and must not be deleted on rollback.
    }
};
