<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->string('name');
            $table->string('rfc', 13)->unique();
            $table->string('curp', 18)->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('representative')->nullable();
            $table->string('cellphone', 30)->nullable();
            $table->string('economic_activity')->nullable();
            $table->decimal('iva_percentage', 5, 2)->default(16);
            $table->string('status')->default('Activo');
            $table->string('it_capture')->nullable();
            $table->timestamps();
        });

        Schema::create('requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('folio')->unique();
            $table->date('created_on');
            $table->date('requested_on');
            $table->date('needed_by');
            $table->decimal('amount', 14, 2);
            $table->string('partida');
            $table->string('sp')->nullable();
            $table->string('ppp')->nullable();
            $table->string('sup')->nullable();
            $table->string('dir')->nullable();
            $table->string('process')->nullable();
            $table->string('rf')->nullable();
            $table->string('budget_key');
            $table->unsignedInteger('quantity');
            $table->string('unit');
            $table->decimal('unit_cost', 14, 2);
            $table->text('description');
            $table->string('department');
            $table->text('justification');
            $table->boolean('budget_confirmed')->default(false);
            $table->string('pdf_path');
            $table->string('xml_path');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('folio')->unique();
            $table->date('order_date');
            $table->foreignId('requisition_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('partida');
            $table->decimal('iva_percentage', 5, 2);
            $table->string('rms')->nullable();
            $table->string('rf')->nullable();
            $table->string('contact_email');
            $table->text('delivery_location');
            $table->string('receiver');
            $table->string('receiver_phone', 30);
            $table->string('pdf_path');
            $table->string('xml_path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('requisitions');
        Schema::dropIfExists('suppliers');
    }
};
