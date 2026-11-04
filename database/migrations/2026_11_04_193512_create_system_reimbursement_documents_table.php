<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_reimbursement_documents', function (Blueprint $table): void {
            $table->id();
            $table->unsignedTinyInteger('order_number')->unique();
            $table->text('original_name');
            $table->string('mime_type', 127);
            $table->binary('content');
            $table->timestamps();
        });

        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE system_reimbursement_documents MODIFY content LONGBLOB NOT NULL');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('system_reimbursement_documents');
    }
};
