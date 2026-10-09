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
        Schema::table('certification_test', function (Blueprint $table) {
            $table->string(column: 'title')->nullable()->after('slug');
            $table->string(column: 'subject')->nullable()->after('slug');
            $table->text(column: 'content')->nullable()->after('slug');
            $table->string(column: 'image')->nullable()->after('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certification_test', function (Blueprint $table) {
            //
        });
    }
};
