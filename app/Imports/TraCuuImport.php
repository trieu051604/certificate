<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class TraCuuImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        $result = [];

        foreach ($collection as $row) {
            // Ensure the required columns exist in the row
            if (isset($row[5])) {
                $row[5] = $this->transformDate($row[5]);
            }

            $result[] = [
                'name' => isset($row[3]) ? $row[3] : null,
                'cccd' => isset($row[4]) ? $row[4] : null,
                'birthday' => isset($row[5]) ? $this->transformDate($row[5]) : null,
                'major' => isset($row[8]) ? $row[8] : null,
            ];
        }

        return $result;
    }

    private function transformDate($excelDate)
    {
        // Excel date base (1900-01-01)
        $baseDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($excelDate);
        return $baseDate->format('d/m/Y'); // Adjust the format as needed
    }
}
