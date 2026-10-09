<?php

namespace App\Imports;

use App\Jobs\ImportExcelJob;
use App\Jobs\importZNSJob;
use App\Models\Import;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;

class ZNSImport implements ToCollection, SkipsOnError, WithChunkReading, WithMultipleSheets
{
    use Importable, SkipsErrors;

    // protected $import;

    public function __construct() {
        // $this->import = $import;
    }

    public function sheets(): array
    {
        return [
            0 => $this,
        ];
    }

    public function collection(Collection $leadCollections)
    {
        dispatch(new importZNSJob($leadCollections));
    }
    // public function startRow(): int
    // {
    //     return 2;
    // }

    public function chunkSize(): int
    {
        return 500;
    }
}
