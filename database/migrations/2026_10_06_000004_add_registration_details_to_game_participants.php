<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('person_game_certification_test', function (Blueprint $table) {
            $table->string('name_school')->nullable();
            $table->string('you_is')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('person_game_certification_test', fn (Blueprint $table) => $table->dropColumn(['name_school', 'you_is']));
    }
};
