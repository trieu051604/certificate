<?php

namespace Database\Seeders;

use App\Imports\GetDatFromFileExcel;
use App\Models\ProvinceCertificationTest;
use App\Models\SchoolCertificationTest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TraCuuImport;

class DataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ini_set('memory_limit', '1024M');
        $filePath = public_path('excel/ds-truong.xlsx');

        // if (!file_exists($filePath)) {
        //     var_dump('không có file');
        // }

        
        $collection = Excel::toCollection(new GetDatFromFileExcel, $filePath);
        foreach ($collection[0] as $item) {
            if ($item[2] == 'Thừa Thiên -Huế') {
                $item[2] = 'Thừa Thiên Huế';
                $province = ProvinceCertificationTest::where('province', trim($item[2]))->first();
                if ($province) {
                    $school = SchoolCertificationTest::where('school', trim($item[6]))
                                                        ->where('province_id', $province->id)
                                                        ->first();
                    if (!$school) {
                        SchoolCertificationTest::create([
                            'school' => trim($item[6]),
                            'province_id' => $province->id
                        ]);
                    }
                }
            }
            // SchoolCertificationTest
        //     \DB::table('tra-cuu')->insert([
        //         'name' => $item[3],
        //         'cccd' => $item[4],
        //         'birthday' => $item[5],
        //         'major' => $item[8],
        //         'kq' => $item[10] == 'Đỗ' ? 1 : 0
        //     ]);
        }
    }
}
