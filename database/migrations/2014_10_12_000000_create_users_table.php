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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->enum('type', ['ADMIN', 'MANAGER', 'ZNS', 'API'])->default('MANAGER');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->unsignedBigInteger('assign_id')->nullable();
            $table->unsignedBigInteger('call_id')->nullable();
            $table->unsignedBigInteger('major_group_id')->nullable();
            $table->foreign('major_group_id')->references('id')->on('major_group')->onDelete('cascade');
            $table->integer('point')->default(0)->comment('Tích điểm chia lead');
            // $table-
            $table->rememberToken();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
