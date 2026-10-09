<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificate_templates', function (Blueprint $table) {
            $table->softDeletes();
        });

        foreach (config('certificates.templates', []) as $template) {
            $path = public_path($template['background']);
            if (!is_file($path) || DB::table('certificate_templates')->where('background', $template['background'])->exists()) {
                continue;
            }

            $dimensions = getimagesize($path);
            if (!$dimensions) {
                continue;
            }

            DB::table('certificate_templates')->insert([
                'name' => $template['name'],
                'background' => $template['background'],
                'width' => $dimensions[0],
                'height' => $dimensions[1],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('certificate_templates', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
