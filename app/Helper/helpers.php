<?php

use App\Models\Setting;
use Carbon\Carbon;

function verTime(): string
{
    return Carbon::now()->timestamp;
}

function setting()
{
    return Setting::first();
}

function changeColorByStatus($status)
{
    switch ($status) {
        case "DONE":
        case "DEFAULT":
        case "SHORTAGE":
        case "SUCCESS":
        case "CONVERTED":
        case "SHARE_CONVERT":
            return "success";
        case "ERROR":
            return "danger";
        default:
            return "warning";
    }
    
}

function jsonResponse($status, $code, $message, $dataCount = 0, $data = []) {
    return response()->json([
        'status' => $status,
        'code' => $code,
        'message' => $message,
        'data_count' => $dataCount,
        'data' => $data
    ]);
}

function getCheckSum($content, $hashkey) {
    $returnHash = base64_encode(hash_hmac("sha1", $content, $hashkey, $raw_output = TRUE));
    $returnHash = str_replace("=", "%3d", $returnHash);
    $returnHash = str_replace(" ", "+", $returnHash);
    return $returnHash;
}

function convertProvinceToKey ($province, $type = null) {
    $dataProvince = [
        'an_giang' => 'An Giang',
        'ba_ria_vung_tau' => 'Bà Rịa - Vũng Tàu',
        'bac_giang' => 'Bắc Giang',
        'bac_kan' => 'Bắc Kạn',
        'bac_lieu' => 'Bạc Liêu',
        'bac_ninh' => 'Bắc Ninh',
        'ben_tre' => 'Bến Tre',
        'binh_dinh' => 'Bình Định',
        'binh_duong' => 'Bình Dương',
        'binh_phuoc' => 'Bình Phước',
        'binh_thuan' => 'Bình Thuận',
        'ca_mau' => 'Cà Mau',
        'can_tho' => 'Cần Thơ',
        'cao_bang' => 'Cao Bằng',
        'da_nang' => 'Đà Nẵng',
        'dak_lak' => 'Đắk Lắk',
        'dak_nong' => 'Đắk Nông',
        'dien_bien' => 'Điện biên',
        'dong_nai' => 'Đồng Nai',
        'dong_thap' => 'Đồng Tháp',
        'gia_lai' => 'Gia Lai',
        'ha_giang' => 'Hà Giang',
        'ha_nam' => 'Hà Nam',
        'ha_noi' => 'Hà Nội',
        'ha_tinh' => 'Hà Tĩnh',
        'hai_duong' => 'Hải Dương',
        'hai_phong' => 'Hải Phòng',
        'hau_giang' => 'Hậu Giang',
        'hoa_binh' => 'Hòa Bình',
        'hung_yen' => 'Hưng Yên',
        'khanh_hoa' => 'Khánh Hòa',
        'kien_giang' => 'Kiên Giang',
        'kon_tum' => 'Kon Tum',
        'lai_chau' => 'Lai Châu',
        'lam_dong' => 'Lâm Đồng',
        'lang_son' => 'Lạng Sơn',
        'lao_cai' => 'Lào Cai',
        'long_an' => 'Long An',
        'nam_dinh' => 'Nam Định',
        'nghe_an' => 'Nghệ An',
        'ninh_binh' => 'Ninh Bình',
        'ninh_thuan' => 'Ninh Thuận',
        'phu_tho' => 'Phú Thọ',
        'phu_yen' => 'Phú Yên',
        'quang_binh' => 'Quảng Bình',
        'quang_nam' => 'Quảng Nam',
        'quang_ngai' => 'Quảng Ngãi',
        'quang_ninh' => 'Quảng Ninh',
        'soc_trang' => 'Sóc Trăng',
        'quang_tri' => 'Quảng Trị',
        'son_la' => 'Sơn La',
        'tay_ninh' => 'Tây Ninh',
        'thai_binh' => 'Thái Bình',
        'thai_nguyen' => 'Thái Nguyên',
        'thanh_hoa' => 'Thanh Hóa',
        'thua_thien_hue' => 'Thừa Thiên Huế',
        'tien_giang' => 'Tiền Giang',
        'tp_ho_chi_minh' => 'TP. Hồ Chí Minh',
        'tra_vinh' => 'Trà Vinh',
        'tuyen_quang' => 'Tuyên Quang',
        'vinh_long' => 'Vĩnh Long',
        'vinh_phuc' => 'Vĩnh Phúc',
        'yen_bai' => 'Yên Bái'
    ];

    if ($type == 'reverse') return isset($dataProvince[$province]) ? $dataProvince[$province] : "";
    return in_array($province, $dataProvince) ? array_search($province, $dataProvince) : "";
}

function formatDate($date) {
    return substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
}

function checkFieldRequired($fields, $requires) {
    $arrayKey = array_keys($fields);
    foreach ($requires as $require) {
        if (!in_array($require, $arrayKey)) {
            return [
                'status' => false,
                'field' => $require
            ];
        }
    }

    return [
        'status' => true,
        'field' => null
    ];
}

function checkFieldAccept($fields, $accepts) {
    foreach ($fields as $key => $field) {
        if (!in_array($key, $accepts)) {
            return [
                'status' => false,
                'field' => $key
            ];
        }
    }

    return [
        'status' => true,
        'field' => null
    ];
}
