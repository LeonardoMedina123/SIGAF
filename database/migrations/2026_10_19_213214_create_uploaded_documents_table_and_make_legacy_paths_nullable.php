<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uploaded_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('requisition_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('kind', 50);
            $table->text('original_name');
            $table->string('mime_type', 127);
            $table->binary('content');
            $table->timestamps();
            $table->unique(['requisition_id', 'kind']);
            $table->unique(['purchase_order_id', 'kind']);
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE uploaded_documents MODIFY content LONGBLOB NOT NULL');
        }

        Schema::table('requisitions', function (Blueprint $table): void {
            $table->string('pdf_path')->nullable()->change();
        });

        Schema::table('purchase_orders', function (Blueprint $table): void {
            $table->string('pdf_path')->nullable()->change();
            $table->string('xml_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uploaded_documents');
    }
};
