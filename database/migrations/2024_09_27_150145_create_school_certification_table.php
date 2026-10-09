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
        Schema::create('school_certification', function (Blueprint $table) {
            $table->id();
            $table->string('school');
            $table->unsignedBigInteger('province_id');
            $table->foreign('province_id')->references('id')->on('province_certification')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_certification');
    }
};
