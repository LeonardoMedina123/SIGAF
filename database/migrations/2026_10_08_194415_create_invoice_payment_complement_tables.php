<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->string('folio')->primary();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->string('uuid')->nullable();
            $table->string('issuer_tax_regime')->nullable();
            $table->string('receiver_tax_regime')->nullable();
            $table->string('payment_form')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('cfdi_use')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_folio');
            $table->foreign('invoice_folio')->references('folio')->on('invoices')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('payment_complements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->restrictOnDelete();
            $table->foreignId('payment_id')->constrained()->restrictOnDelete();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->string('invoice_folio');
            $table->foreign('invoice_folio')->references('folio')->on('invoices')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_complements');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoices');
    }
};
