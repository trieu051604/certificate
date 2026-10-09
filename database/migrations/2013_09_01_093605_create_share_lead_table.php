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
        Schema::create('share_lead', function (Blueprint $table) {
            $table->id();
            $table->string('list_user_miss')->nullable()->comment('Lưu danh sách user id lead chia thiếu');
            $table->string('detail')->nullable();
            $table->unsignedBigInteger('major_group_id')->nullable();
            $table->foreign('major_group_id')->references('id')->on('major_group')->onDelete('cascade');
            $table->enum('status', ['SUCCESS', 'ERROR'])->nullable();
            $table->longText('messange')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_lead');
    }
};
