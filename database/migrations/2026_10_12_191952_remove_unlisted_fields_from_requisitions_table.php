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
        Schema::table('requisitions', function (Blueprint $table) {
            $table->dropColumn([
                'needed_by',
                'budget_key',
                'quantity',
                'unit',
                'unit_cost',
                'description',
                'department',
                'justification',
                'budget_confirmed',
                'xml_path',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requisitions', function (Blueprint $table) {
            $table->date('needed_by')->nullable();
            $table->string('budget_key')->nullable();
            $table->unsignedInteger('quantity')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('unit_cost', 14, 2)->nullable();
            $table->text('description')->nullable();
            $table->string('department')->nullable();
            $table->text('justification')->nullable();
            $table->boolean('budget_confirmed')->default(false);
            $table->string('xml_path')->nullable();
        });
    }
};
