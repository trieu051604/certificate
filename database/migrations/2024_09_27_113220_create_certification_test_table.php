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
        Schema::create('certification_test', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['CERTIFICATION', 'QUIZGAME'])->default('CERTIFICATION');
            $table->string('url_redirect')->nullable();
            $table->string('slug');
            $table->string('course');
            $table->integer('passing_score');
            $table->enum('status', ['ACTIVE', 'UNACTIVE'])->default('ACTIVE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certification_test');
    }
};
