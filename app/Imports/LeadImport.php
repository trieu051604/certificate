<?php

namespace App\Imports;

use App\Jobs\ImportExcelJob;
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

class LeadImport implements ToCollection, WithValidation, SkipsOnError, WithStartRow, WithChunkReading, WithMultipleSheets, SkipsUnknownSheets
{
    use Importable, SkipsErrors;

    protected $import;

    public function __construct(Import $import) {
        $this->import = $import;
    }

    public function sheets(): array
    {
        return [
            0 => $this,
        ];
    }

    public function onUnknownSheet($sheetName)
    {
        
    }

    public function collection(Collection $leadCollections)
    {
        dispatch(new ImportExcelJob($leadCollections, $this->import));
    }
    public function startRow(): int
    {
        return 2;
    }
    public function rules(): array
    {
        return [
            '0' => ['required'],
            '2' => ['required'],
            '3' => ['required'],
            '4' => ['required', 'size:10'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            '0.required' => 'Tên trống',
            '2.required' => 'Trường trống',
            '3.required' => 'Ngành trống',
            '4.required' => 'Số điện thoại trống',
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
