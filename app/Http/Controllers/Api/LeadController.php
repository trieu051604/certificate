<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LogRequestApi;
use App\Models\Major;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Services\Api\RegistrationService;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class LeadController extends Controller
{



    public function getCheckSum($content, $hashkey)
        {
            $returnHash = base64_encode(hash_hmac("sha1", $content, $hashkey, true));
            $returnHash = str_replace("=", "%3d", $returnHash);
            $returnHash = str_replace(" ", "+", $returnHash);
            return $returnHash;
        }

     public function saveDatalake(Request $request)
    {   
        $data = $request->all();

        // die();
        // Hash key của bạn
        $hashKey = "A0H5XT376SI66rFOmn3Co72864sZRIoq1xt9rLiUVD5ynk1qnwuBI7gDiK94AdpwDrfI8HwK";

        // Tạo content để tính checksum
        $content = $data['time'] .
                   $data['data']['name'].
                   $data['data']['phone']['phone1'].
                   $data['data']['email'].
                   $data['send_to'] .
                   $data['action'] .
                   "zeef1SdrAInEqYYF9l9r6DKAPbLqlNaDDSjwgOV9TbQ2ISg4SpRT9ZG6DNzZgWgWITbHPBzu".
                   $data['client_code'];

        // Tạo checksum
        $checksum =$this->getCheckSum($content, $hashKey);
        // echo $checksum;
        // echo "</>";
        // echo $data['checksum'];
        if($checksum ==  $data['checksum']){
           

            $logRequestApi = new LogRequestApi();
            $logRequestApi->url = $request->url();
            $logRequestApi->ip = $request->ip();
            $logRequestApi->method = $request->method();
            $logRequestApi->header = json_encode($request->header());
            $logRequestApi->body = json_encode($request->all());
            $logRequestApi->response = '';
            $logRequestApi->save();
            
            
                $leadExist = Lead::where('phone', $data['data']['phone']['phone1'])->first();


                if ($leadExist) {
                    $leadExist->update(['note' => $leadExist->note . ' - ' .  $request->note]);
                    if ($leadExist->status_insert !== 'PENDING') {
                        $crm = new \CRM();
                        $crm->updateNoteForLead($leadExist);
                        // $zns->send($leadExist);
                    }

                    $dataError = [
                        "code" => "409",
                        "status" => "duplicate",
                        "message" => "Duplicate lead detected. This lead already exists in the system.",
                        "lead_id" => $leadExist->id,
                    ];


                    $logRequestApi->response = json_encode($dataError);
                    $logRequestApi->save();
                    return response()->json($dataError);
                } else {

                    // $loai_xet_tuyen = $request->loai_xet_tuyen;
                    $lead = Lead::create([
                        'fullname' => $data['data']['name'],
                        'birth_day' => $data['data']['birthdate'],
                        'school_id' => $data['data']['province_id'],
                        'major_id' => "16",
                        'phone' => $data['data']['phone']['phone1'],
                        'email' => $data['data']['email'],
                        'link_facebook' => "",
                        'address' => "",
                       
                        'province_id' => "ha_noi",
                        'source' => $data['data']['channel'],
                        'note' => "Lead test từ gọi API tuyển sinh trực tuyến",
                        // 'type' => setting()->getMeta('type_import')
                        'type' => 'DEFAULT',
                        // 'xettuyen' => is_array($loai_xet_tuyen) && in_array('xettuyen', $loai_xet_tuyen) ? '1' : NULL,
                        // 'hocbong' => is_array($loai_xet_tuyen) && in_array('hocbong', $loai_xet_tuyen) ? '1' : NULL,
                        // 'tindung' => is_array($loai_xet_tuyen) && in_array('tindung', $loai_xet_tuyen) ? '1' : NULL,
                    ]);
        
                    if ($lead) {
                        // if ($lead->source != 'schoolrank') $zns->send($lead);
                        $dataError = [
                              "code" => "200",
                              "status" => "success",
                              "message" => "Lead created successfully.",
                              "lead_id" => $lead->id,

                        ];
                        $logRequestApi->response = json_encode($dataError);
                        $logRequestApi->save();
                        return response()->json($dataError);
                    }
                }

                $dataError = [
                    'status_code' => 500,
                    'message' => 'Có lỗi xảy ra, vui lòng thử lại',
                ];
                $logRequestApi->response = json_encode($dataError);
                $logRequestApi->save();
                return response()->json($dataError);
            }
        
    }


    public function responseCreate(Request $request){
         $data = $request->all();
      
         $dataError = [
                                      "code" => "200",
                                      "status" => "success",
                                      "message" => "Lead updated successfully.",
                                      "lead_id" => $data['lead_id'],

            ];
        return response()->json($dataError);


        // die();
        // Hash key của bạn
        $hashKey = "A0H5XT376SI66rFOmn3Co72864sZRIoq1xt9rLiUVD5ynk1qnwuBI7gDiK94AdpwDrfI8HwK";

        // Tạo content để tính checksum
        $content = $data['time'] .
                   $data['data']['name'].
                   $data['data']['phone']['phone1'].
                   $data['data']['email'].
                   $data['send_to'] .
                   $data['action'] .
                   "zeef1SdrAInEqYYF9l9r6DKAPbLqlNaDDSjwgOV9TbQ2ISg4SpRT9ZG6DNzZgWgWITbHPBzu".
                   $data['client_code'];

        // Tạo checksum
        $checksum =$this->getCheckSum($content, $hashKey);
        // echo $checksum;
        // echo "</>";
        // echo $data['checksum'];
        if($checksum ==  $data['checksum']){


        }
    }

    public function save(Request $request)
    {   

        if ($request->province_id == '-1') {
            return response()->json([
                'status_code' => 500,
                'message' => 'province_id error',
            ]);
        }

        $logRequestApi = new LogRequestApi();
        $logRequestApi->url = $request->url();
        $logRequestApi->ip = $request->ip();
        $logRequestApi->method = $request->method();
        $logRequestApi->header = json_encode($request->header());
        $logRequestApi->body = json_encode($request->all());
        $logRequestApi->response = '';
        $logRequestApi->save();
        try {

            // set type 
            // setting()->getMeta('type_import') === 'DEFAULT' ? setting()->setMeta('type_import', 'PERFORMANCE') : setting()->setMeta('type_import', 'DEFAULT');

            $validator = Validator::make($request->all(), [
                'fullname' => 'required|max:50',
                // 'school_id' => 'required|integer|max:1000000',
                'major_id' => 'required|integer|max:100000000',
                'phone' => 'required|size:10',
                'province_id' => 'required|max:50'
            ]);

            // $provinceAllow = [
            //     'binh_duong', 'dong_nai', 'tp_ho_chi_minh', 'tien_giang', 'long_an', 'tay_ninh', 'ba_ria_vung_tau'
            // ];
            // if (!in_array($request->province_id, $provinceAllow)) {
            //     $dataError = [
            //         'status_code' => 403,
            //         'error' => 'Tỉnh không cho phép',
            //     ];
            //     $logRequestApi->response = json_encode($dataError);
            //     $logRequestApi->save();
            //     return response()->json($dataError, 400);
            // }
        
            if ($validator->fails()) {
                $dataError = [
                    'status_code' => 400,
                    'error' => $validator->errors(),
                ];
                $logRequestApi->response = json_encode($dataError);
                $logRequestApi->save();
                return response()->json($dataError, 400);
            }

            $major = Major::where('product_id', $request->major_id)->first();

            if (!$major) {
                $dataError = [
                    'status_code' => 404,
                    'error' => 'Không tìm thấy ngành',
                ];
                $logRequestApi->response = json_encode($dataError);
                $logRequestApi->save();
                return response()->json($dataError, 404);
            }

            $leadExist = Lead::where('phone', $request->phone)->first();
            $zns = new \ZNS();

            if ($leadExist) {
                $leadExist->update(['note' => $leadExist->note . ' - ' .  $request->note]);
                if ($leadExist->status_insert !== 'PENDING') {
                    $crm = new \CRM();
                    $crm->updateNoteForLead($leadExist);
                    // $zns->send($leadExist);
                }
                $dataError = [
                    'status_code' => 403,
                    'message' => 'Update Success',
                ];
                $logRequestApi->response = json_encode($dataError);
                $logRequestApi->save();
                return response()->json($dataError);
            } else {
                // $loai_xet_tuyen = $request->loai_xet_tuyen;
                $lead = Lead::create([
                    'fullname' => $request->fullname,
                    'birth_day' => $request->birth_day,
                    'school_id' => $request->school_id,
                    'major_id' => $major->id,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'link_facebook' => $request->link_facebook,
                    'address' => $request->address,
                    'time_contact' => $request->time_contact,
                    'province_id' => $request->province_id,
                    'source' => $request->source,
                    'note' => $request->note,
                    // 'type' => setting()->getMeta('type_import')
                    'type' => 'DEFAULT',
                    // 'xettuyen' => is_array($loai_xet_tuyen) && in_array('xettuyen', $loai_xet_tuyen) ? '1' : NULL,
                    // 'hocbong' => is_array($loai_xet_tuyen) && in_array('hocbong', $loai_xet_tuyen) ? '1' : NULL,
                    // 'tindung' => is_array($loai_xet_tuyen) && in_array('tindung', $loai_xet_tuyen) ? '1' : NULL,
                ]);
    
                if ($lead) {
                    // if ($lead->source != 'schoolrank') $zns->send($lead);
                    $dataError = [
                        'status_code' => 200,
                        'message' => 'Create Success',
                    ];
                    $logRequestApi->response = json_encode($dataError);
                    $logRequestApi->save();
                    return response()->json($dataError);
                }
            }

            $dataError = [
                'status_code' => 500,
                'message' => 'Có lỗi xảy ra, vui lòng thử lại',
            ];
            $logRequestApi->response = json_encode($dataError);
            $logRequestApi->save();
            return response()->json($dataError);
        } catch (\Throwable $th) {
            $dataError = [
                'status_code' => 500,
                'message' => $th->getMessage(),
            ];
            $logRequestApi->response = json_encode($dataError);
            $logRequestApi->save();
            return response()->json($dataError);
        }
    }

    public function getSchools($provinceKey)
    {
        $logRequestApi = new LogRequestApi();
        $logRequestApi->url = request()->url();
        $logRequestApi->ip = request()->ip();
        $logRequestApi->method = request()->method();
        $logRequestApi->header = json_encode(request()->header());
        $logRequestApi->body = json_encode(request()->all());
        $logRequestApi->response = '';
        $logRequestApi->save();
        try {
            $crm = new \CRM();
            $result = $crm->getDropdownList(["schools"]);
            $schools = array_filter($result->ListsInfo->schools, function($school) use ($provinceKey) {
                return $school->in_province === $provinceKey;
            });
            

            return response()->json($schools);
        } catch (\Throwable $th) {
            $dataError = [
                'status_code' => 500,
                'message' => $th->getMessage(),
            ];
            $logRequestApi->response = json_encode($dataError);
            $logRequestApi->save();
            return response()->json($dataError);
        }
    }

    public function importLeadToCrm(Request $request)
    {

        if ($request->province_id == '-1') {
            return response()->json([
                'status_code' => 500,
                'message' => 'province_id error',
            ]);
        }
        
        $logRequestApi = new LogRequestApi();
        $logRequestApi->url = $request->url();
        $logRequestApi->ip = $request->ip();
        $logRequestApi->method = $request->method();
        $logRequestApi->header = json_encode($request->header());
        $logRequestApi->body = json_encode($request->all());
        $logRequestApi->response = '';
        $logRequestApi->save();
        try {

            // set type 
            // setting()->getMeta('type_import') === 'DEFAULT' ? setting()->setMeta('type_import', 'PERFORMANCE') : setting()->setMeta('type_import', 'DEFAULT');

            $validator = Validator::make($request->all(), [
                'fullname' => 'required|max:50',
                // 'school_id' => 'required|integer|max:1000000',
                'major_id' => 'required|integer|max:100000000',
                'phone' => 'required|size:10',
                'province_id' => 'required|max:50'
            ]);
        
            if ($validator->fails()) {
                $dataError = [
                    'status_code' => 400,
                    'error' => $validator->errors(),
                ];
                $logRequestApi->response = json_encode($dataError);
                $logRequestApi->save();
                return response()->json($dataError, 400);
            }

            $major = Major::where('product_id', $request->major_id)->first();

            if (!$major) {
                $dataError = [
                    'status_code' => 404,
                    'error' => 'Không tìm thấy ngành',
                ];
                $logRequestApi->response = json_encode($dataError);
                $logRequestApi->save();
                return response()->json($dataError, 404);
            }

            if (auth()->user()->name == 'StudentAdmin') {
                $crm = new \CRM('_STUDENT');
            } else {
                $crm = new \CRM('_OTHER');
            }
            $lead = new Lead();
            $lead->fullname = $request->fullname;
            $lead->birth_day = $request->birth_day;
            $lead->school_id = $request->school_id;
            $lead->major_id = $major->id;
            $lead->phone = $request->phone;
            $lead->email = $request->email;
            $lead->link_facebook = $request->link_facebook;
            // $lead->address = $request->address;
            // $lead->time_contact = $request->time_contact;
            $lead->province_id = $request->province_id;
            $lead->source = $request->source;
            $lead->note = $request->note;
            
            $result = $crm->saveRegistration($lead);    
            $resultJson = json_decode($result);

            if ($resultJson && isset($resultJson->error_code) && $resultJson->error_code == 'LEAD_IS_DUPLICATED_BY_PHONE') {
                $dataError = [
                    'status_code' => 403,
                    'message' => 'Update Success',
                ];
                $logRequestApi->response = json_encode($dataError);
                $logRequestApi->save();
                return response()->json($dataError);
            }

            $provinceAllow = [
                '_QN' => [
                    'binh_dinh', 'gia_lai', 'khanh_hoa', 'phu_yen', 'quang_ngai'
                ],
            ];

            $keyZNS = false;
            // foreach ($provinceAllow as $key => $province) {
            //     if (in_array($request->province_id, $province)) {
            //         $keyZNS = $key;
            //         break;
            //     }
            // }

            // if ($keyZNS) {
            //     $zns = new \ZNS($keyZNS);
            //     $zns->send($lead);
            // }

            $dataError = [
                'status_code' => 200,
                'message' => 'Add Lead Success',
            ];
            $logRequestApi->response = json_encode($dataError);
            $logRequestApi->save();
            return response()->json($dataError);

        } catch (\Throwable $th) {
            $dataError = [
                'status_code' => 500,
                'message' => $th->getMessage(),
            ];
            $logRequestApi->response = json_encode($dataError);
            $logRequestApi->save();
            return response()->json($dataError);
        }
    }

    public function pushDataToCrm(Request $request)
    {
       try {
            $crm = new \CRM('_TRANSFER');
            $data = $request->only(['RequestAction', 'Params', 'Data', 'Credentials']);
            return $crm->pushDataToCrm($data);
       } catch (\Throwable $th) {
            // err
       }
    }

    public function callApiUni($data)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://university.fpt.edu.vn/cong-dang-ky-tuyen-sinh/api/update-student-info',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
        ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    }

    public function pushDataToNewPageRegister(Request $request)
    {
        $APICode = env('API_CODE');
        $accessToken = env('ACCESS_TOKEN');
        $hashKey = env('HASH_KEY');
        $registrationService = new RegistrationService();
        $crm = new \CRM();

        $statusContact = [
            'non_reg' => 'Chưa có đăng ký',
            'pre_reg' => 'Đăng ký thành công',
            'reg' => 'Đã duyệt hồ sơ',
            'passed' => 'Trúng tuyển đại học FPT',
            'pre_ne' => 'Đã ghi danh',
            'nb' => 'New booking',
            'ne' => 'Sinh viên đại học FPT',
            'do' => 'Sinh viên đại học FPT - Đã rút hồ sơ',
            'cancel' => 'Hủy'
        ];

        try {
            $contact = $crm->getInfoStudentByContactId($request->contact_id);
            // return response()->json($contact);
            $dataPost = [
                "APICode" => $APICode,
                "ID" =>  $contact['contactid'],
                "Email" =>  $contact['email'],
                "Phone"  =>  $contact['mobile'],
                "FullName" => $contact['lastname'],
                "Gender" =>  $contact['contacts_gender'] == 'Male' ? 'm' : 'f',
                "BOD" =>  Carbon::createFromFormat('Y-m-d', $contact['birthday'])->format('Ymd'), //(yyyyMMDD)
                "IdNumber" =>  $contact['id_card_number'],
                "IdIssuedDate" =>  Carbon::createFromFormat('Y-m-d', $contact['identity_card_issue_date'])->format('Ymd') ,//(yyyyMMDD)
                "IdIssuedPlace" =>  $contact['contacts_identity_card_issue_place'],
                "SchoolName" =>  $registrationService->convertProvinceAndDistrictToKey('schools', $contact['accountid'], true),
                // "SchoolCode" =>  $contact['ma_truong_thpt'], // Mã code của bộ Giáo dục
                "ProvinceName" =>  $registrationService->convertProvinceAndDistrictToKey('contact_student_provinces', $contact['contacts_city'], true),
                "GraduateYear" =>  $contact['contacts_high_school_graduation_year'],
                "AltName" =>  $contact['contact_student_name'],
                "AltPhone" =>  $contact['contact_student_phone'],
                "AltEmail" =>  $contact['contact_student_email'],
                "AltProvince" =>  $registrationService->convertProvinceAndDistrictToKey('contact_student_provinces', $contact['contact_student_province'], true),
                "AltDistrict" =>  $registrationService->convertProvinceAndDistrictToKey('contact_student_districts', $contact['contact_student_district'], true),
                "AltWard" =>  $registrationService->convertProvinceAndDistrictToKey('contact_student_sub_districts', $contact['contact_student_sub_district'], true),
                "AltAddress" => $contact['contact_student_address'],
                "RegisterAdmission" => $registrationService->getKeyPotential($contact['potentials_form'], $contact['potentials_type']) == 'mienthihocba', //Đăng ký xét tuyển
                "ApplyCredit" => $registrationService->getKeyPotential($contact['potentials_form'], $contact['potentials_type']) == 'mienthihocba', //Đăng ký ưu đãi tín dụng
                "ScholarshipExam" => $registrationService->getKeyPotential($contact['potentials_form'], $contact['potentials_type']) == 'hocbong', //Đăng ký thi học bổng 14/04
                "ScholarshipConsideration" => $registrationService->getKeyPotential($contact['potentials_form'], $contact['potentials_type']) == 'hocbong', //Đăng ký xét cấp học bổng
                "NoSchoolrank" => $registrationService->getKeyPotential($contact['potentials_form'], $contact['potentials_type']) == 'mienthikhac', //Không Schoolrank
                "MajorCode" => $contact['related_product'],
                "MajorName" => $registrationService->getNameByMajorKey($contact['related_product']),
                // "CampusCode" => "FPTUHN",
                // "PlaceExam" =>  "FPTUHN",
                "Method" => $registrationService->getNameFreeExamByKey($contact['potentials_other_exemptions']),
                // "Incentives" => "",
                "StatusCode" => $contact['contacts_status'],
                "Status" => isset($statusContact[$contact['contacts_status']]) ? $statusContact[$contact['contacts_status']] : "",
                "Scholarship" => [
                    [
                        "Code" =>  "hocbongnguyenvandao",
                        "Name" => "Học bổng Nguyễn Văn Đạo",
                        "status" => $contact['potentials_loai_hoc_bong'] == 'hocbongnguyenvandao'
                    ],
                    [
                        "Code" =>  "hocbongdiaphuong",
                        "Name" => "Học bổng địa phương",
                        "status" => $contact['potentials_loai_hoc_bong'] == 'hocbongdiaphuong'
                    ],
                    [
                        "Code" =>  "hocbongtruongthpt",
                        "Name" => "Học bổng trường THPT",
                        "status" => $contact['potentials_loai_hoc_bong'] == 'hocbongtruongthpt'
                    ]
                ],
            ];

            $content = $APICode . $accessToken . $dataPost['Phone'] . $dataPost['Email'] . $dataPost['Gender'] . $dataPost['ID'];
            $dataPost["CheckSum"] = getCheckSum($content, $hashKey);

            // return response()->json($dataPost);

            $response = $this->callApiUni($dataPost);

            return response()->json(['status' => 200, 'message' => 'Success', 'data' => json_decode($response)]);
       } catch (\Throwable $th) {
            return response()->json([
                'status' => 500, 
                'message' => $th->getMessage(), 
                'file' => $th->getFile(), 
                'line' => $th->getLine()
            ]);
       }
    }
}
