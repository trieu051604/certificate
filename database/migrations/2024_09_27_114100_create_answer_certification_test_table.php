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
        Schema::create('answer_certification_test', function (Blueprint $table) {
            $table->id();
            $table->string('answer');
            $table->unsignedBigInteger(column: 'question_certification_test_id');
            $table->foreign('question_certification_test_id')->references('id')->on('question_certification_test')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answer_certification_test');
    }
};
