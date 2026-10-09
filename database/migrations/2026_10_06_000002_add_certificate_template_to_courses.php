<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certification_test', function (Blueprint $table) {
            $table->foreignId('certificate_template_id')->nullable()
                ->constrained('certificate_templates')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('certification_test', function (Blueprint $table) {
            $table->dropConstrainedForeignId('certificate_template_id');
        });
    }
};
