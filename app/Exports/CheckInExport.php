<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CheckInExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $checkin;

    public function __construct($checkin)
    {
        $this->checkin = $checkin;
    }
    public function collection()
    {
        return collect($this->checkin)->map(function($item) {
            $time = explode(' ', $item->created_at);
            return [
                'fullname' => $item->fullname,
                'email' => $item->email,
                'phone' => $item->phone,
                'province' => $item->province,
                'school' => $item->school,
                'mon_hoc' => $item->mon_hoc,
                'khoa_hoc' => $item->khoa_hoc,
                'timeDate' => $time[0],
                'time' => $time[1],
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Họ và Tên',
            'Email',
            'Phone',
            'Tỉnh',
            'Trường',
            'Môn học',
            'Khoá học',
            'Ngày tạo',
            'Thời gian tạo'
        ];
    }
}
