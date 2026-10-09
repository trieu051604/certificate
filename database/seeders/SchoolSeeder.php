<?php

namespace Database\Seeders;

use App\Imports\SchoolImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Excel::import(new SchoolImport, storage_path('app/excel/tinh_truong.xlsx'));
    }
}
