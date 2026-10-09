<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PersonCertificationExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $persons;

    public function __construct($persons)
    {
        $this->persons = $persons;
    }
    public function collection()
    {
        return collect($this->persons)->map(function($item) {
            $time = explode(' ', $item->created_at);
            return [
                'fullname' => $item->fullname,
                'email' => $item->email,
                'phone' => $item->phone,
                'province' => $item->province?->province ?? '',
                'school' => $item->name_school ?: ($item->school?->school ?? ''),
                'point' => $item->point,
                'result' => $item->isPass() ? "Đậu" : "Không đậu",
                'status_send_mail' => $item->status_send_mail == 200 ? "Đã gửi" : "Chưa gửi",
                'timeDate' => $time[0],
                'time' => $time[1],
                'registration' => collect($item->registration_data ?? [])->map(fn ($field) => $field['label'].': '.$field['value'])->implode("\n"),
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
            'Điểm',
            'Kết quả',
            'Tình trạng gửi mail',
            'Ngày tạo',
            'Thời gian tạo',
            'Thông tin đăng ký'
        ];
    }
}
