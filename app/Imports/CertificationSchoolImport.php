<?php

namespace App\Imports;

use App\Models\SchoolCertificationTest;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class CertificationSchoolImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    protected $provinceId;
    public function __construct($provinceId) {
        $this->provinceId = $provinceId;
    }
    public function collection(Collection $collection)
    {
        $schools = SchoolCertificationTest::where('province_id', $this->provinceId)->pluck('school')->toArray();
        try{
            foreach ($collection as $index => $school) {
                if ($index == 0) continue;
                if (!in_array($school[0], $schools)) {
                    SchoolCertificationTest::create([
                        'school' => $school[0],
                        'province_id' => $this->provinceId
                    ]);
                }
            }
        } catch (\Throwable $th) {
            \DB::rollBack();
            dd($th->getMessage());
        }
    }
}
