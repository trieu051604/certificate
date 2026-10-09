<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('registration_forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('fields');
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::table('certification_test', function (Blueprint $table) {
            $table->foreignId('registration_form_id')->nullable()->constrained('registration_forms')->restrictOnDelete();
        });
        foreach (['person_certification_test', 'person_game_certification_test'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->json('registration_data')->nullable();
                $table->unsignedBigInteger('school_id')->nullable()->change();
                $table->unsignedBigInteger('province_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        foreach (['person_certification_test', 'person_game_certification_test'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropColumn('registration_data'));
        }
        // Keep optional school/province IDs nullable: submitted forms may have omitted them.
        Schema::table('certification_test', fn (Blueprint $table) => $table->dropConstrainedForeignId('registration_form_id'));
        Schema::dropIfExists('registration_forms');
    }
};
