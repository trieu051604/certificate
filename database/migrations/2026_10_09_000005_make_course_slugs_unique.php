<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Keep the oldest course's existing URL; give later duplicates their own URL.
        DB::transaction(function () {
            $duplicates = DB::table('certification_test')->select('slug')
                ->groupBy('slug')->havingRaw('COUNT(*) > 1')->get();
            foreach ($duplicates as $duplicate) {
                $courses = DB::table('certification_test')->where('slug', $duplicate->slug)->orderBy('id')->get();
                foreach ($courses->skip(1) as $course) {
                    $base = substr($course->slug, 0, 230).'-'.$course->id;
                    $slug = $base;
                    $suffix = 2;
                    while (DB::table('certification_test')->where('slug', $slug)->exists()) {
                        $slug = $base.'-'.$suffix++;
                    }
                    DB::table('certification_test')->where('id', $course->id)->update(['slug' => $slug]);
                }
            }
        });

        Schema::table('certification_test', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('certification_test', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });
        // Preserve the repaired URLs rather than recreating ambiguous links.
    }
};
