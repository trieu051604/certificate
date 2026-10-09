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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->integer('count_duplicate')->default(0);
            $table->integer('point')->default(0);
            $table->string('fullname');
            $table->string('birth_day')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('province_id');
            $table->string('school_id')->nullable();
            $table->string('class')->nullable();
            $table->string('link_facebook')->nullable();
            $table->string('source')->nullable();
            $table->string('note')->nullable();
            $table->string('xettuyen')->nullable();
            $table->string('hocbong')->nullable();
            $table->string('tindung')->nullable();
            $table->longText('messange_insert')->nullable();
            $table->unsignedBigInteger('lead_crm_id')->nullable()->comment('Id ở CRM');

            $table->unsignedBigInteger('sale_id')->nullable();
            $table->foreign('sale_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('major_id')->nullable();
            $table->foreign('major_id')->references('id')->on('majors')->onDelete('cascade');
            $table->unsignedBigInteger('import_id')->nullable();
            $table->foreign('import_id')->references('id')->on('imports')->onDelete('cascade');
            $table->unsignedBigInteger('share_lead_id')->nullable();
            $table->foreign('share_lead_id')->references('id')->on('share_lead')->onDelete('cascade');
            $table->unsignedBigInteger('convert_id')->nullable();
            $table->foreign('convert_id')->references('id')->on('log_points')->onDelete('cascade');

            $table->enum('status_insert', ['PENDING', 'SUCCESS', 'ERROR'])->default('PENDING');
            $table->enum('source_ads', ['Facebook'])->nullable();
            $table->enum('time_contact', ['8h-9h', '11h-13h', '18h-20h', 'khac'])->nullable();
            $table->enum('status', ['PENDING', 'DONE', 'SHORTAGE', 'SHARE_CONVERT'])->default('PENDING')->comment('Trạng thái lead');
            $table->enum('status_convert', ['PENDING', 'CONVERTED'])->default('PENDING')->comment('Trạng thái chuyển đổi điểm');
            $table->enum('type', ['DEFAULT', 'PERFORMANCE'])->default('DEFAULT')->comment('Loại chia');
            $table->enum('gender', ['Male', 'Female', 'Other', 'not_categorized'])->nullable();
            $table->unsignedBigInteger('insert_lead_id')->nullable();
            $table->foreign('insert_lead_id')->references('id')->on('insert_lead_api')->onDelete('cascade');

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
        Schema::dropIfExists('leads');
    }
};
