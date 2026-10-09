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
        Schema::create('question_certification_test', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->string('answer_correct_id')->nullable();
            $table->unsignedBigInteger('certification_test_id');
            $table->foreign('certification_test_id')->references('id')->on('certification_test')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_certification_test');
    }
};
