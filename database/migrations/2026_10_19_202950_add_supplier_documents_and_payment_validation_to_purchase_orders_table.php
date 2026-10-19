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
        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->string('invoice_pdf_path')->nullable();
            $table->string('invoice_xml_path')->nullable();
            $table->string('payment_complement_path')->nullable();
            $table->timestamp('payment_validated_at')->nullable();
            $table->foreignId('payment_validated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->dropForeign(['payment_validated_by']);
            $table->dropColumn([
                'invoice_pdf_path',
                'invoice_xml_path',
                'payment_complement_path',
                'payment_validated_at',
                'payment_validated_by',
            ]);
        });
    }
};
