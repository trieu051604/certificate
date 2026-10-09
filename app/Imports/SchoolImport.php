<?php

namespace App\Imports;

use App\Models\School;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class SchoolImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        foreach ($collection as $school) {
            School::create([
                'name' => $school[1],
                'code' => $school[0],
                'code_province' => $school[2],
                'code_kv' => $school[3],
            ]);
        }
    }
}
