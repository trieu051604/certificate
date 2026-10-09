<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'id' => 17,
                'username' => 'hue.tran',
                'full_name' => 'Trần Thị Linh Huệ',
            ],
            [
                'id' => 23,
                'username' => 'oanh.dang',
                'full_name' => 'Đặng Thị Kiều Oanh',
            ],
            [
                'id' => 24,
                'username' => 'tuyen.le',
                'full_name' => 'Lê Thanh Tuyền',
            ],
            [
                'id' => 69,
                'username' => 'hau.ngo',
                'full_name' => 'Ngô Thi Thanh Hậu',
            ],
            [
                'id' => 107,
                'username' => 'khoi.nguyenxuan',
                'full_name' => 'Nguyễn Xuân Khôi',
            ],
            [
                'id' => 146,
                'username' => 'vivtk',
                'full_name' => 'Võ Thị Kim Vi',
            ],
            [
                'id' => 147,
                'username' => 'vinth',
                'full_name' => 'Nguyễn Thị Hồng Vi',
            ],
            [
                'id' => 154,
                'username' => 'linhttt23',
                'full_name' => 'Trần Thị Thùy Linh',
            ],
            [
                'id' => 174,
                'username' => 'hanghtt0504',
                'full_name' => 'Hoàng Thị Thanh Hằng',
            ],
            [
                'id' => 191,
                'username' => 'nhungdang.28',
                'full_name' => 'Đặng Thị Mai Nhung',
            ],
            [
                'id' => 192,
                'username' => 'ntathu19',
                'full_name' => 'Nguyễn Thị Anh Thư',
            ],
            [
                'id' => 254,
                'username' => 'hoanggiang0803',
                'full_name' => 'Hồ Hoàng Giang',
            ],
            [
                'id' => 258,
                'username' => 'thanhthao0404',
                'full_name' => 'Thái Thị Thanh Thảo',
            ],
            [
                'id' => 299,
                'username' => 'yennhi77152',
                'full_name' => 'Trần Thị Yến Nhi',
            ],
            [
                'id' => 315,
                'username' => 'haiquyen2802',
                'full_name' => 'Vũ Mạnh Hải Quyên',
            ],
            [
                'id' => 359,
                'username' => 'minhhoang072000',
                'full_name' => 'Đinh Lê Minh Hoàng',
            ],
        ];

        foreach ($users as $user){
            User::create([
                'name' => $user['full_name'],
                'email' => $user['username'],
                'assign_id' => $user['id'],
                'password' => Hash::make('123456'),
            ]);
        }
        
    }
}
