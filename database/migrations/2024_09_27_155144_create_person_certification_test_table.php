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
        Schema::create('person_certification_test', function (Blueprint $table) {
            $table->id();
            $table->string('fullname');
            $table->string('phone');
            $table->string('email');
            $table->integer('point');
            $table->json('answer_json');
            $table->integer('status_send_mail')->nullable();
            $table->text('msg_send_mail')->nullable();

            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('province_id');
            $table->unsignedBigInteger('certification_test_id');

            $table->foreign('school_id')->references('id')->on('school_certification')->onDelete('cascade');
            $table->foreign('province_id')->references('id')->on('province_certification')->onDelete('cascade');
            $table->foreign('certification_test_id')->references('id')->on('certification_test')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('person_certification_test');
    }
};
