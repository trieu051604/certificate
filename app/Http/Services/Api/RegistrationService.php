<?php 

namespace App\Http\Services\Api;
use Illuminate\Http\Request;

class RegistrationService {
    private $crm;

    function __construct()
    {
        $this->crm = new \CRM();
    }

    function checkExistStudentByPhone($IdNumber) 
    {
        try {
            return $this->crm->checkExistStudentByIdNumber($IdNumber);
        } catch (\Throwable $th) {
            return null;
        }
    }

    function getPointSchoolRank($code)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://schoolrank.fpt.edu.vn/api/schoolrank',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode(['ma_ho_so' => $code]),
        CURLOPT_HTTPHEADER => array(
            'Token: 449b4879f77b63066602ec54c57e4c31',
            'CoSo: TSHCM',
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($response);
        return ($response && isset($response->data)) ? $response->data->diem : null;
    }

    function mergeArrayPoints($points)
    {
        $points = (array) $points;
        // $subjects = [
        //     'toan' => 'mathematics',
        //     'van' => 'literature',
        //     'anh' => 'english',
        //     'ly' => 'physics',
        //     'hoa' => 'chemistry',
        //     'sinh' => 'biology',
        //     'su' => 'history',
        //     'dia' => 'geography',
        //     'gdcd' => 'civic_education'
        // ];

        return [
            'points_grade_11' => [
                'mathematics' => $points['toan_1'],
                'literature' => $points['van_1'],
                'english' => $points['anh_1'],
                'physics' => $points['ly_1'],
                'chemistry' => $points['hoa_1'],
                'biology' => $points['sinh_1'],
                'history' => $points['su_1'],
                'geography' => $points['dia_1'],
                'civic_education' => $points['gdcd_1']
            ],
            'term_1_grade_12' => [
                'mathematics' => $points['toan_2'],
                'literature' => $points['van_2'],
                'english' => $points['anh_2'],
                'physics' => $points['ly_2'],
                'chemistry' => $points['hoa_2'],
                'biology' => $points['sinh_2'],
                'history' => $points['su_2'],
                'geography' => $points['dia_2'],
                'civic_education' => $points['gdcd_2']
            ],
        ];

        // foreach ($subjects as $key => $subject) {
        //     $data['points_grade_11['. $subject .']'] = $points[$key . '_1'];
        //     $data['term_1_grade_12['. $subject .']'] = $points[$key . '_2'];
        // }

        // return $data;

    }

    function checkExistStudentById($id) 
    {
        try {
            return $this->crm->checkExistStudentById($id);
        } catch (\Throwable $th) {
            return null;
        }
    }

    function getKeyPotential($form, $type)
    {
        $dataKey = [
            'duthi' => [
                'form' => ['examination'],
                'type' => 'normal'
            ],
            'mienthihocba' => [
                'form' => ['history_study', 'review_score_high_school'],
                'type' => 'normal'
            ],
            'hocbong' => [
                'form' => ['examination', 'other_consider'],
                'type' => 'scholarship'
            ],
            'mienthikhac' => [
                'form' => ['other_consider'],
                'type' => 'normal'
            ]
        ];

        foreach ($dataKey as $index => $key) {
            if ($key['type'] == $type && in_array($form, $key['form'])) return $index;
        }

        return "";
    }

    function getNameFreeExamByKey($key)
    {
        $data = [
            'tuyen_thang_theo_quy_che_bo_giao_duc' => 'Tuyển thẳng theo quy định của Bộ',
            'chung_chi_jlpt_n3' => 'JLPT N3',
            'chung_chi_ielts_toefl' => 'IELTS 6.0',
            'TOEFL_iBT_80' => 'TOEFL iBT 80',
            'topik_ii' => 'TOPIK II Cấp độ 4',
            'vstepbac5' => 'VSTEP bậc 5',
            'aptech_hdse' => 'APTECH HDSE',
            'aptech_adse' => 'APTECH ADSE',
            'graduating_university' => 'Tốt nghiệp Đại học',
            'arena_adim' => 'ARENA ADIM',
            'btec_hnd' => 'BTEC HND',
            'funix_software' => 'FUNiX Software Engineering',
            'graduated_from_foreign_high_school' => 'Tốt nghiệp phổ thông với văn bằng do nước ngoài cấp',
            'hoc_dai_hoc_nuoc_ngoai' => 'Học Đại học ở nước ngoài',
            'chuyen_truong_top1000' => 'SV trường thuộc Top 1000 QS, ARWU và THE đạt QS Star 5* về giảng dạy',
            'the_he_1_hocba_THPT' => 'Ưu tiên thế hệ 1 - Học bạ THPT Top50',
            'melbourne_polytechnic' => 'Chương trình Melbourne Polytechnic',
            'top50_cao_dang_fpt_polytechnic' => 'Tốt nghiệp Top40 Cao đẳng FPT Polytechnic',
            'dat_21_diem_hoc_ba' => 'Đạt 21 điểm học bạ THPT',
            'tot_nghiep_top40_9_poly' => 'Tốt nghiệp Top40 Chương trình Phổ thông Cao đẳng (9+) tại FPT Polytechnic'
        ];

        return isset($data[$key]) ? $data[$key] : "";
    }

    function getNameByMajorKey($key)
    {
        try {
            $majors = $this->crm->GetMasterDataList();
            foreach ($majors as $major) {
                if ($major['major_id'] == $key) return $major['major_name'];
            }
            return "";
        } catch (\Throwable $th) {
            return "";
        }
    }

    function convertProvinceAndDistrictToKey($type, $name, $reverse = false)
    {
        try {
            // contact_student_provinces
            // contact_student_districts
            // contact_student_sub_districts
            // schools
            $data = $this->crm->getAllDataDistrictAndProvince();
            if (!$data || !$data['ListsInfo']) return "";
            $dataDetails = $data['ListsInfo'][$type];
            foreach ($dataDetails as $detail) {
                if ($reverse) {
                    if ($detail['key'] == $name) return $detail['label'];
                } else {
                    if ($detail['label'] == $name) return $detail['key'];
                }
            }
            return "";
        } catch (\Throwable $th) {
            return "";
        }
    }

    function getMethod($key) {
        $convertMethod = [
            'chung_chi_ielts_toefl' => "chung_chi_jlpt_n3",
            'TOEFL_iBT_80' => "TOEFL_iBT_80",
            'topik_ii' => "topik_ii",
            'vstepbac5' => "vstepbac5",
            'aptech_hdse' => "aptech_hdse",
            'aptech_adse' => "aptech_adse",
            'graduating_university' => "graduating_university",
            'arena_adim' => "arena_adim",
            'btec_hnd' => "btec_hnd",
            'funix_software' => "funix_software",
            'graduated_from_foreign_high_school' => "graduated_from_foreign_high_school",
            'hoc_dai_hoc_nuoc_ngoai' => "hoc_dai_hoc_nuoc_ngoai",
            'chuyen_truong_top1000' => "chuyen_truong_top1000",
            'melbourne_polytechnic' => "melbourne_polytechnic",
            'top40_cao_dang_fpt_polytechnic' => "top50_cao_dang_fpt_polytechnic",
            'tot_nghiep_top40_9_poly' => "tot_nghiep_top40_9_poly",
        ];

        return isset($convertMethod[$key]) ? $convertMethod[$key] : "";
    }

    function saveAllData(Request $request) 
    {
        try {
            // $assignedId = env('ASSIGNED_ID');
            $hcm = [
                'binh_duong', 'dong_nai', 'tp_ho_chi_minh', 'tien_giang', 'long_an', 'tay_ninh', 'ba_ria_vung_tau', 'binh_thuan', 'dak_lak', 'dak_nong', 'binh_phuoc'
            ];
            $dataPost = [
                'mobile' => $request->Phone,
                'birthday' => formatDate($request->BOD),
                'id_card_number' => $request->IdNumber,
                'identity_card_issue_date' => formatDate($request->IdIssuedDate),
                'contacts_identity_card_issue_place' => convertProvinceToKey($request->IdIssuedPlace),
                'province_id' => convertProvinceToKey($request->ProvinceName),
                'key' => 'mienthihocba',
                'related_product' => $request->MajorCode ?? '49887',
                'lastname' => $request->FullName,
                'email' => $request->Email,
                'contact_student_name' => $request->FullName,
                'contact_student_phone' => $request->AltPhone,
                'contact_student_email' => $request->AltEmail,
                'contact_student_apartmentnumber' => $request->AltAddress,
                'contact_student_province' => convertProvinceToKey($request->AltProvince),
                'contact_student_district' => $this->convertProvinceAndDistrictToKey('contact_student_districts', $request->AltDistrict),
                'contact_student_sub_district' => $this->convertProvinceAndDistrictToKey('contact_student_sub_districts', $request->AltWard),
                'school_id' => $this->convertProvinceAndDistrictToKey('schools', $request->SchoolName),
                'contact_student_address' => $request->AltAddress . ' - ' . $request->AltDistrict . ' - ' . $request->AltProvince,
                'active_student_user' => 1,
                'contacts_gender' => $request->Gender == 'm' ? 'Male' : 'Female',
                'fu_ranking_50_verified_code' => $request->SchoolRankID,
                'potentials_fu_ranking_50_index' => in_array($request->SchoolRankTop, [10, 20, 30, 40, 50, 1, 2, 3, 4, 5, 15, 25, 35, 45]) ? 'top' . $request->SchoolRankTop : '',
                'assign_user_id' => in_array(convertProvinceToKey($request->ProvinceName), $hcm) ? 144 : 29,
                'assigned_user_id' => in_array(convertProvinceToKey($request->ProvinceName), $hcm) ? 144 : 29,
                'main_owner_id' => in_array(convertProvinceToKey($request->ProvinceName), $hcm) ? 144 : 29,
                'lead_source' => 'trang_dang_ky_ho',
                'source' => "HO",
                'related_cpsubjectlist' => "",
                'contacts_high_school_graduation_year' => $request->GraduateYear
            ];

            $resultSCR = $this->getPointSchoolRank($request->SchoolRankID);

            if ($request->SchoolRankID) {
                $dataPost['potentials_form'] = 'history_study';
                $dataPost['potentials_registration_type'] = 'points_grade_11_term_1_grade_12_fu_ranking50';
                $dataPost['potentials_condition_reg_type'] = 'points_grade_11_term_1_grade_12_fu_ranking50';
            } else {
                $dataPost['potentials_form'] = 'other_consider';
                $dataPost['potentials_registration_type'] = '';
                $dataPost['potentials_condition_reg_type'] = '';
            }

            if ($resultSCR) {
                $pointSCR = $this->mergeArrayPoints($resultSCR);
                $dataPost['subjects_points'] = $pointSCR;
                // $dataPost = array_merge($dataPost, $pointSCR);
            }

            $newContact = $this->crm->saveAll($dataPost);
            // return [];
            // return [
            //     'test' => 'ok',
            //     'data' => $dataPost
            // ];

            if (!isset($newContact) || !isset($newContact['code']) || $newContact['code'] != 200) return false;

            $dataInfo = collect($request->all())->except(['CheckSum', 'APICode'])->toArray();
            $dataInfo['ID'] = $newContact['studentId'];
            return $dataInfo;

        } catch (\Throwable $th) {
            echo $th->getMessage() . ' ' . $th->getLine() . ' ' . $th->getFile();
            return false;
        }
    }

    function updateStudent(Request $request)
    {
        try {
            $checkExistStudent = $this->crm->getContactByUniqueField(
                $request->Email,
                $request->Phone,
                $request->IdNumber
            );

            if ($checkExistStudent) return $checkExistStudent;

            // update SV
            $dataPostStudent = [
                'contact_id' => $request->ID,
                'lastname' => $request->FullName,
                'contacts_gender' => $request->Gender == 'm' ? 'Male' : 'Female',
                'birthday' => formatDate($request->BOD),
                'id_card_number' => $request->IdNumber,
                'identity_card_issue_date' => formatDate($request->IdIssuedDate),
                'contacts_identity_card_issue_place' => convertProvinceToKey($request->IdIssuedPlace),
                'mobile' => $request->Phone,
                'email' => $request->Email,
                'contact_student_name' => $request->FullName,
                'contact_student_phone' => $request->AltPhone,
                'contact_student_email' => $request->AltEmail,
                'contact_student_apartmentnumber' => $request->AltAddress,
                'contact_student_province' => convertProvinceToKey($request->AltProvince),
                'contact_student_district' => $this->convertProvinceAndDistrictToKey('contact_student_districts', $request->AltDistrict),
                'related_cpdistrict_account' => $this->convertProvinceAndDistrictToKey('related_cpdistrict_account', $request->AltDistrict),
                'contact_student_sub_district' => $this->convertProvinceAndDistrictToKey('contact_student_sub_districts', $request->AltWard),
                'account_id' => $this->convertProvinceAndDistrictToKey('schools', $request->SchoolName),
                'contact_student_address' => $request->AltAddress . ' - ' . $request->AltDistrict . ' - ' . $request->AltProvince,
                'contacts_gender' => $request->Gender == 'm' ? 'Male' : 'Female',
                'related_product' => $request->MajorCode,
                'related_product_register' => $request->MajorCode,
                'contacts_high_school_graduation_year' => $request->GraduateYear,
                'potentials_other_exemptions' => $this->getMethod($request->Method)
            ];

            // return $dataPostStudent;
            
            $updateContact = $this->crm->saveStudent($dataPostStudent);

            // update DKTS
            $dataPostPotential = $dataPostStudent;
            $dataPostPotential['fu_ranking_50_verified_code'] = $request->SchoolRankID;
            $dataPostPotential['potentials_fu_ranking_50_index'] = in_array($request->SchoolRankTop, [10, 20, 30, 40, 50, 1, 2, 3, 4, 5, 15, 25, 35, 45]) ? 'top' . $request->SchoolRankTop : '';

            if ($request->RegisterAdmission) $dataPostPotential['key'] = 'mienthihocba';
            // if ($request->ScholarshipExam || $request->ScholarshipConsideration) $dataPostPotential['key'] = 'hocbong';
            if ($request->NoSchoolrank) $dataPostPotential['key'] = 'mienthikhac';

            $dataSchoolarship = $request->Scholarship;
            $schoolarCode = "";
            if ($dataSchoolarship && is_array($dataSchoolarship)) {
                foreach ($dataSchoolarship as $schoolarship) {
                    if (isset($schoolarship['status']) && isset($schoolarship['Code']) && $schoolarship['status']) {
                        $schoolarCode = $schoolarship['Code'];
                    }
                }
            }
            $dataPostPotential['potentials_loai_hoc_bong'] = $schoolarCode;

            $updatePotential = $this->crm->savePotential($dataPostPotential);

            // return [
            //     'test' => 'ok',
            //     'data' => $updatePotential
            // ];

            if (!$updateContact || !$updatePotential) return null;
            return collect($request->all())->except(['CheckSum', 'APICode'])->toArray();
        } catch (\Throwable $th) {
            // echo $th->getMessage() . ' ' . $th->getLine() . ' ' . $th->getFile();
            return [];
        }
    }

    function updateStatus(Request $request)
    {
        try {
            $dataPost = [
                'contact_id' => $request->ID,
                'contacts_status' => strtolower($request->Status)
            ];
            $updateContact = $this->crm->saveStudent($dataPost);
            return collect($request->all())->except(['CheckSum', 'APICode'])->toArray();
        } catch (\Throwable $th) {
            return [];
        }
    }

    function getAllDataOfStudentByPhoneOrEmail(Request $request)
    {
        try {
            $student = $this->crm->getAllDataOfStudentByPhoneOrEmail($request->Email, $request->Phone);
            if (!$student || !$student['studentInfo']) return null;
            $studentInfo = $student['studentInfo'];
            return [
                'ID' => $studentInfo['contactid'],
                'Email' => $studentInfo['email'],
                'Phone' => $studentInfo['mobile'],
                'SchoolRankID' => $studentInfo['fu_ranking_50_verified_code'],
                'SchoolRankTop' => str_replace('top', '', $studentInfo['potentials_fu_ranking_50_index']),
                "FullName" => $studentInfo['lastname'],
                "Gender" => $studentInfo['contacts_gender'] == 'Male' ? 'm' : 'f',
                "BOD" => date("Ymd", strtotime($studentInfo['birthday'])), //(yyyyMMDD)
                "IdNumber" => $studentInfo['id_card_number'],
                "IdIssuedDate" => date("Ymd", strtotime($studentInfo['identity_card_issue_date'])),// (yyyyMMDD)
                "IdIssuedPlace" => convertProvinceToKey($studentInfo['contacts_identity_card_issue_place'], 'reverse'),
                "SchoolName" => $studentInfo['accountname'],
                "SchoolCode" => $studentInfo['ma_truong_thpt'], // Mã code của bộ Giáo dục
                "ProvinceName" => convertProvinceToKey($studentInfo['contacts_address_province'], 'reverse'),
                "GraduateYear" => $studentInfo['contacts_high_school_graduation_year'],
                "AltName" => $studentInfo['contact_student_name'],
                "AltPhone"=> $studentInfo['contact_student_phone'],
                "AltEmail"=> $studentInfo['contact_student_email'],
                "AltProvince"=> convertProvinceToKey($studentInfo['contact_student_province'], 'reverse'),
                "AltDistrict"=> $studentInfo['contact_student_district_name'],
                "AltWard" => $studentInfo['contacts_address_sub_district_name'],
                "AltAddress" => $studentInfo['contact_student_apartmentnumber'],
                "APICode" => $request->APICode,
                'StatusCode' => $studentInfo['contacts_status']
            ];
        } catch (\Throwable $th) {
            // echo $th->getMessage() . ' ' . $th->getLine() . ' ' . $th->getFile();
            return null;
        }
    }

    function updateDocumentAndPayment(Request $request)
    {
        try {
            $fullActivities = $request->FullActivities;
            if ($fullActivities && is_array($fullActivities)) {

                foreach ($fullActivities as $activity) {
                    // if ($activity['ActivityType'] == 'Payment' && isset($activity['linkEvidence']) && $activity['linkEvidence']) $urlPayment = $activity['linkEvidence'];
                    if ($activity['ActivityType'] == 'Document' && isset($activity['Code'])) {
                        if ($activity['Code'] == 'cccdmt' && isset($activity['linkEvidence']) && $activity['linkEvidence']) $urlCCCCDTruoc = $activity['linkEvidence'];
                        if ($activity['Code'] == 'cccdms' && isset($activity['linkEvidence']) && $activity['linkEvidence']) $urlCCCDSau = $activity['linkEvidence'];
                        if (strpos($activity['Code'], 'minh_chung_payment') && isset($activity['linkEvidence']) && $activity['linkEvidence']) $urlPayment = $activity['linkEvidence'];
                    }
                }

                $invoices = $request->Invoices;

                $dataPost = [
                    'contact_id' => $request->ID,
                    'receipt_image_1' => $urlPayment ?? "",
                    'front_id_card_image' => $urlCCCCDTruoc ?? "",
                    'behind_id_card_image' => $urlCCCDSau ?? "",
                    'transactionid_registration_fee' => $invoices['TransactionId'] ?? "",
                    'transactionid_admission' => $invoices['InvoiceSerialNumber'] ?? ""
                ];

                // var_dump($dataPost);

                // $updatePotential = $this->crm->saveStudent($dataPost);
                $updatePotential = $this->crm->savePotential($dataPost);
                if (!$updatePotential) return null;
                return collect($request->all())->except(['CheckSum', 'APICode'])->toArray();
            }
        } catch (\Throwable $th) {
            // echo $th->getMessage() . ' ' . $th->getLine() . ' ' . $th->getFile();
            return null;
        }
    }

    function sendOTP(Request $request)
    {
        try {
            $dataPost = [
                'phone_number' => $request->Phone
            ];
            return $this->crm->sendOTP($dataPost);
        } catch (\Throwable $th) {
            //throw $th;
            return null;
        }
    }

    function verifyOTP(Request $request)
    {
        try {
            $dataPost = [
                'phone_number' => $request->Phone,
                'otp' => $request->OTP,
                'user_id' => $request->ID ?? ""
            ];
            return $this->crm->verifyOTP($dataPost);
        } catch (\Throwable $th) {
            //throw $th;
            return null;
        }
    }
}