<?php

namespace Database\Seeders;

use App\Models\Major;
use App\Models\Major_Group;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MajorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $majorGroups = [
            'Quản trị kinh doanh', 
            'Công nghệ thông tin', 
            'Ngôn ngữ', 
            'Công nghệ truyền thông',
            'Chưa chọn ngành'
        ];
        $listIdMajor = [];

        foreach ($majorGroups as $major) {
            $majorGroups = Major_Group::create([
                'name' => $major
            ]);
            $listIdMajor[] = $majorGroups->id;
        }

        $majors = [
            [
                'product_id' => 16,
                'product_no' => 7480201,
                'major' => 'An toàn thông tin',
                // 'major_group_id' => $listIdMajor[1]
            ],
            [
                'product_id' => 4727,
                'product_no' => 7480201,
                'major' => 'Kỹ thuật phần mềm',
                // 'major_group_id' => $listIdMajor[1]
            ],
            [
                'product_id' => 4728,
                'product_no' => 7480201,
                'major' => 'KHMT - Khoa học máy tính',
                // 'major_group_id' => $listIdMajor[1]
            ],
            [
                'product_id' => 4729,
                'product_no' => 7340101,
                'major' => 'Quản trị khách sạn',
                // 'major_group_id' => $listIdMajor[0]
            ],
            [
                'product_id' => 4730,
                'product_no' => 7340101,
                'major' => 'Quản trị dịch vụ Du lịch & Lữ hành',
                // 'major_group_id' => $listIdMajor[0]
            ],
            [
                'product_id' => 4731,
                'product_no' => 7340101,
                'major' => 'Quản trị kinh doanh',
                // 'major_group_id' => $listIdMajor[0]
            ],
            [
                'product_id' => 4732,
                'product_no' => 7340101,
                'major' => 'Kinh doanh quốc tế',
                // 'major_group_id' => $listIdMajor[3]
            ],
            [
                'product_id' => 4734,
                'product_no' => 'TC - NH',
                'major' => 'Tài chính - Ngân hàng',
                // 'major_group_id' => $listIdMajor[3]
            ],
            // [
            //     'product_id' => 4735,
            //     'product_no' => 7340101,
            //     'major' => 'Quản trị truyền thông đa phương tiện',
            //     // 'major_group_id' => $listIdMajor[0]
            // ],
            [
                'product_id' => 4736,
                'product_no' => 7220201,
                'major' => 'Ngôn ngữ Anh - Anh',
                // 'major_group_id' => $listIdMajor[2]
            ],
            [
                'product_id' => 4737,
                'product_no' => 7220209,
                'major' => 'Ngôn ngữ Nhật',
                // 'major_group_id' => $listIdMajor[2]
            ],
            [
                'product_id' => 4738,
                'product_no' => 7220210,
                'major' => 'Ngôn ngữ Hàn',
                // 'major_group_id' => $listIdMajor[2]
            ],
            [
                'product_id' => 4740,
                'product_no' => 7480201,
                'major' => 'Thiết kế mỹ thuật số',
                // 'major_group_id' => $listIdMajor[3]
            ],
            [
                'product_id' => 21267,
                'product_no' => 7480201,
                'major' => 'Công nghệ thông tin',
                // 'major_group_id' => $listIdMajor[1]
            ],
            [
                'product_id' => 21296,
                'product_no' => 7220210,
                'major' => 'Ngôn ngữ Hàn Quốc',
                // 'major_group_id' => $listIdMajor[2]
            ],
            [
                'product_id' => 49887,
                'product_no' => 'Chưa chọn ngành',
                'major' => 'Chưa chọn ngành',
                // 'major_group_id' => $listIdMajor[3]
            ],
            [
                'product_id' => 141090,
                'product_no' => 7480201,
                'major' => 'IoT (Internet of Things)',
                // 'major_group_id' => $listIdMajor[1]
            ],
            [
                'product_id' => 141093,
                'product_no' => 7480201,
                'major' => 'Trí tuệ nhân tạo',
                // 'major_group_id' => $listIdMajor[1]
            ],
            [
                'product_id' => 141100,
                'product_no' => 'mkt',
                'major' => 'Marketing',
                // 'major_group_id' => $listIdMajor[3]
            ],
            [
                'product_id' => 274355,
                'product_no' => 7480201,
                'major' => 'Hệ thống thông tin',
                // 'major_group_id' => $listIdMajor[1]
            ],
            [
                'product_id' => 551406,
                'product_no' => 'Not Categorized',
                'major' => 'Not Categorized',
                // 'major_group_id' => $listIdMajor[3]
            ],
            [
                'product_id' => 552843,
                'product_no' => 7340101,
                'major' => 'Digital Marketing',
                // 'major_group_id' => $listIdMajor[3]
            ],
            [
                'product_id' => 552848,
                'product_no' => 7480201,
                'major' => 'Phần mềm ô tô (dự kiến)',
                // 'major_group_id' => $listIdMajor[1]
            ],
            [
                'product_id' => 552850,
                'product_no' => 7480202,
                'major' => 'Thương mại điện tử (dự kiến)',
                // 'major_group_id' => $listIdMajor[3]
            ],
            [
                'product_id' => 3826500,
                'product_no' => 7340101,
                'major' => 'Tài chính',
                // 'major_group_id' => $listIdMajor[3]
            ],
            [
                'product_id' => 6478460,
                'product_no' => 7220201,
                'major' => 'Ngôn ngữ Anh - Trung',
                // 'major_group_id' => $listIdMajor[2]
            ],
            [
                'product_id' => 9672108,
                'product_no' => 0,
                'major' => 'Công nghệ ô tô số',
            ],
            [
                'product_id' => 9672109,
                'product_no' => 0,
                'major' => 'Logistic',
            ],
            [
                'product_id' => 9672110,
                'product_no' => 0,
                'major' => 'Quản lý chuỗi cung ứng',
            ],
            [
                'product_id' => 9672112,
                'product_no' => 0,
                'major' => 'Truyền thông đa phương tiện',
            ],
            [
                'product_id' => 9672114,
                'product_no' => 0,
                'major' => 'Quan hệ công chúng',
            ],
            [
                'product_id' => 9678139,
                'product_no' => 0,
                'major' => 'Vi mạch bán dẫn',
            ],
            [
                'product_id' => 9678335,
                'product_no' => 0,
                'major' => 'Song ngữ Nhật - Anh',
            ],
            [
                'product_id' => 9678336,
                'product_no' => 0,
                'major' => 'Ngôn ngữ Anh',
            ],
            [
                'product_id' => 9678337,
                'product_no' => 0,
                'major' => 'Song ngữ Hàn - Anh',
            ],
            [
                'product_id' => 9678338,
                'product_no' => 0,
                'major' => 'Song ngữ Trung - Anh',
            ],
        ];
        
        foreach ($majors as $major) {
            Major::create($major);
        }
    }
}
