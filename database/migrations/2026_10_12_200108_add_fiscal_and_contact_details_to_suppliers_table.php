<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('person_type', 10)->nullable();
            $table->string('tax_regime', 3)->nullable();
            $table->string('cfdi_use', 4)->nullable();
            $table->string('contact_email')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn([
                'person_type',
                'tax_regime',
                'cfdi_use',
                'contact_email',
            ]);
        });
    }
};
