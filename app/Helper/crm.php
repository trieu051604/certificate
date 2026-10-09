<?php
// use App\Models\CallApi;
use App\Models\Lead;
use App\Models\LogPoint;
use App\Models\Major;
use Illuminate\Support\Facades\Session;

class CRM {
    private $url;
    private $username;
    private $password;
    private $pointDefaultConvertLead = 5;
    private $numberLeadByPoint = 3;
    private $optionAuth;
    private $ext;
    
    private $options = [
        'curl' => [
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT_MS => 600000,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Content-Type: application/json;charset=UTF-8',
            ]
        ]
    ];
    public function __construct($ext = '')
    {
        $this->ext = $ext;
        $this->url = env('CRM_URL');
        $this->username = env('CRM_USERNAME' . $ext);
        $this->password = env('CRM_PASSWORD' . $ext);
        $this->optionAuth = $this->optionAuth();
    }
    public function login()
    {
        $data = [
            "RequestAction" => "Login",
            "Credentials" => [
                "username" => $this->username,
                "password" => $this->password
            ]
        ];
        $response = Http::withOptions($this->options)->post($this->url, $data);
        $this->logApi("Login", $response);
        return $response->status() === 200 ? $response->json()['token'] : NULL;
    }
    public function pushDataToCrm($data)
    {
        // return $data;
        $serviceUrl = "https://hcmcrm.fpt.edu.vn/api/WebsiteApi.php";
        $data['URL'] = $data['URL'] == 'CRM' ? $serviceUrl : $data['URL'];
        $response = Http::withOptions($this->optionAuth)->post($data['URL'], collect($data)->except('URL')->toArray());
        $this->logApi("pushDataToCrm", $response);
        return $response;
    }
    public function importLeads($leads)
    {
        try {
            // map lại data
            // $dataLeads = $leads->filter(function ($lead) {
            //     return $lead->user !== null;
            // })->map(function ($lead) {
            //     $lead->major = $lead->major->product_id;
            //     $lead->assign_user_id = 29;
            //     // if ($lead->user !== null) {
            //     //     $lead->assign_user_id = $lead->user->assign_id;
            //     // }
            //     $lead->lead_share_id = $lead->id;
            //     return $lead;
            // });

            $dataLeads = $leads->map(function ($lead) {
                $lead->major = $lead->major->product_id;
                $lead->assign_user_id = 29;
                // if ($lead->user !== null) {
                //     $lead->assign_user_id = $lead->user->assign_id;
                // }
                $lead->lead_share_id = $lead->id;
                return $lead;
            });

            $data = [
                "RequestAction" => "saveLeads",
                "Data" => $dataLeads
            ];
            $response = Http::withOptions($this->optionAuth)->post($this->url, $data);
            $this->logApi("saveLeads", $response);

            $dataJson = $response->json();

            $leads->each(function ($lead) use ($dataJson) {
                if (isset($dataJson[$lead->id]['status'])) {
                    if ($dataJson[$lead->id]['status'] == 'SUCCESS') {
                        $lead->lead_crm_id = $dataJson[$lead->id]['lead_crm_id'];
                    }
                    $lead->messange_insert = $dataJson[$lead->id]['messange'];
                    $lead->status_insert = $dataJson[$lead->id]['status'];
                } else {
                    $lead->messange_insert = 'CALL API LỖI';
                    $lead->status_insert = 'ERROR';
                }
                unset($lead->major, $lead->assign_user_id, $lead->lead_share_id);
                $lead->save();
            });
        } catch (\Exception $e) {
            $this->log("ERROR IMPORT LEAD", $e->getMessage());
            \Log::channel('crm')->info('ERROR IMPORT LEAD' . $e->getMessage());
        } finally {
            $this->logout();
        }
    }

    public function updateNoteForLead($lead)
    {
        $data = [
            "RequestAction" => "saveRegistration",
            "Data" => [
                "lead_name" => $lead->fullname,
                "phone_number" => $lead->phone,
                "major" => $lead->major->product_id,
                "city" => $lead->province_id,
                "description" => $lead->note
            ]
        ];
        $response = Http::withOptions($this->optionAuth)->post($this->url, $data);
        $this->logApi("saveRegistration", $response);
    }

    public function saveRegistration($lead)
    {
        $data = [
            "RequestAction" => "saveRegistration",
            "Data" => [
                "lead_name" => $lead->fullname,
                "phone_number" => $lead->phone,
                "major" => $lead->major->product_id,
                "city" => $lead->province_id,
                "description" => $lead->note,
                "email" => $lead->email,
                "birthday" => $lead->birthday,
                "school" => $lead->school_id,
                "facebook" => $lead->link_facebook,
                "lead_source" => $lead->source,
            ]
        ];
        $response = Http::withOptions($this->optionAuth)->post($this->url, $data);
        $this->logApi("saveRegistration", $response);
        return $response;
    }

    public function getLeadToSendZalo()
    {
        $data = [
            "RequestAction" => "GetLeadToSendZalo",
        ];
        $response = Http::withOptions($this->optionAuth)->post($this->url, $data);
        $this->logApi("GetLeadToSendZalo", $response);

        return json_decode($response) ?? null;
    }

    public function getDropdownList($params = [])
    {
        $data = [
            "RequestAction" => "GetDropdownList",
            "Params" => [
                "Lists" => $params
            ]
        ];
        $response = Http::withOptions($this->optionAuth)->post($this->url, $data);
        $this->logApi("GetDropdownList", $response);

        return json_decode($response) ?? null;
    }

    public function optionAuth()
    {
        $token = $this->login();
        $optionAuth = [
            'Token: ' . $token,
            'Cookie: PHPSESSID=' . $token
        ];
        $this->options['curl'][CURLOPT_HTTPHEADER] = array_merge($this->options['curl'][CURLOPT_HTTPHEADER], $optionAuth);
        return $this->options;
    }

    public function convertPointToLead($sale)
    {
        // try {
        //     // + điểm cho sale với số lead đã chuyển đổi
        //     $queryLead = $sale->leads()->where('status_convert', 'PENDING')->where('status_insert', 'SUCCESS');
        //     $leads = (clone $queryLead)->pluck('lead_crm_id')->toArray();
        //     if (count($leads) > 0) {
        //         $stringLeadId = implode(',', $leads);
        //         $data = [
        //             'RequestAction' => 'getLeadConvert',
        //             'Params' => [
        //                 'listId' => $stringLeadId,
        //             ]
        //         ];
        //         $response = Http::withOptions($this->optionAuth)->post($this->url, $data);
        //         $this->logApi("getLeadConvert", $response);
    
        //         if ($response->status() === 200 && $response->json()) {
        //             $IdLeadConverts = $response->json()['leads']; // list id lấy đc từ call API
    
        //             if ($IdLeadConverts) {
        //                 $leadConverts = (clone $queryLead)->whereIn('lead_crm_id', $IdLeadConverts)->get();
        //                 foreach ($leadConverts as $lead) {
        //                     $lead->update(['status_convert' => 'CONVERTED']);
        //                 }
        //                 $countLeadsConvert = count($leadConverts);
        //                 $sale->increment('point', $countLeadsConvert);
        
        //                 // Chuyển đổi điểm thành lead
        //                 $numberLeadBtPoint = setting()->getMeta('number_lead_by_point') ?? $this->numberLeadByPoint;
        //                 $pointConvertLead = (int) setting()->getMeta('point') ?? $this->pointDefaultConvertLead;
        //                 $numberLeadConvert = floor($sale->point / $pointConvertLead) * (int) $numberLeadBtPoint;
        
        //                 if ($numberLeadConvert !== 0) {
        //                     $majorGroupId = $sale->major_group()->pluck('major_group.id')->toArray();
        //                     $majorId = Major::whereIn('major_group_id', $majorGroupId)->pluck('id')->toArray();

        //                     $leadConvertAutos = Lead::whereIn('major_id', $majorId)
        //                         ->where('status', 'PENDING')
        //                         ->where('status_insert', 'PENDING')
        //                         ->where('type', 'PERFORMANCE')
        //                         ->take($numberLeadConvert)
        //                         ->get();

        //                     if (count($leadConvertAutos) == $numberLeadConvert) {
        //                         $pointResidual = $sale->point % $pointConvertLead;
        //                         $sale->update(['point' => $pointResidual]);

        //                         $logPoint = LogPoint::create([
        //                             'point_convert' => $pointConvertLead,
        //                             'point_residual' => $pointResidual,
        //                             'leads' => $numberLeadConvert,
        //                             'user_id' => $sale->id,
        //                         ]);

        //                         foreach ($leadConvertAutos as $leadConvertAuto) {
        //                             $leadConvertAuto->update([
        //                                 'sale_id' => $sale->id,
        //                                 'convert_id' => $logPoint->id,
        //                                 'status' => 'SHARE_CONVERT'
        //                             ]);
        //                         }
        //                     }
        //                 }
        //             }
        //         }
        //     }
        //     return 0;
        // } catch (\Exception $e) {
        //     $this->log('ERROR CONVERT LEAD', $e->getMessage());
        // } finally {
        //     $this->logout();
        // }
    }

    public function checkExistStudentByIdNumber($idNumber)
    {
        $data = [
            "RequestAction" => "checkExistStudentByIdNumber",
            "Params" => [
                "id_card_number" => $idNumber
            ]
        ];
        $response = Http::withOptions($this->optionAuth)->post($this->url, $data);
        $this->logApi("checkExistStudentByIdNumber", $response);
        // var_dump($response->json());
        return $response->json() ? $response->json()['contact'] : null;
    }

    public function saveAll($dataPost)
    {
        $data = [
            "RequestAction" => "saveAll",
            "Params" => $dataPost
        ];
        $response = Http::withOptions($this->optionAuth)->post($this->url, $data);
        // var_dump([
        //     $response->json(),
        // ]);
        $this->logApi("saveAll", $response);
        return $response->json();
    }

    public function saveStudent($dataPost)
    {
        $data = [
            "RequestAction" => "updateStudentInfo",
            "Params" => $dataPost
        ];
        $response = Http::withOptions($this->optionAuth)->post($this->url, $data);
        $this->logApi("updateStudentInfo", $response);
        // var_dump($response->json());
        return ($response->json() && !isset($response->json()['error'])) ? $response->json()['success'] : null;
    }

    public function sendOTP($dataPost)
    {
        $data = [
            "RequestAction" => "sendOTP",
            "Params" => $dataPost
        ];
        $response = Http::withOptions($this->optionAuth)->post($this->url, $data);
        $this->logApi("sendOTP", $response);
        // var_dump($response->json());
        return $response->json();
    }

    public function verifyOTP($dataPost)
    {
        $data = [
            "RequestAction" => "verifyOTP",
            "Params" => $dataPost
        ];
        $response = Http::withOptions($this->optionAuth)->post($this->url, $data);
        $this->logApi("verifyOTP", $response);
        // var_dump($response->json());
        return $response->json();
    }

    public function savePotential($dataPost)
    {
        $data = [
            "RequestAction" => "updatePotentialInfo",
            "Params" => $dataPost
        ];
        $response = Http::withOptions($this->optionAuth)->post($this->url, $data);
        $this->logApi("updatePotentialInfo", $response);
        // var_dump([
        //     $response->json(),
        //     $dataPost
        // ]);
        return $response->json() ? $response->json()['success'] : null;
    }

    public function getAllDataDistrictAndProvince()
    {
        $data = [
            "RequestAction" => "getDropdownList",
            "Params" => [
                "Lists" => [
                    'contact_student_provinces',
                    'contact_student_districts',
                    'contact_student_sub_districts',
                    'schools'
                ]
            ]
        ];
        $response = Http::withOptions($this->optionAuth)->post($this->url, $data);
        $this->logApi("getDropdownList", $response);
        return $response->json();
    }

    function getAllDataOfStudentByPhoneOrEmail($email, $mobile)
    {
        $data = [
            "RequestAction" => "getContactByEmailOrPhone",
            "Params" => [
                "mobile" => $mobile,
                "email" => $email,
                "id_card_number" => ""
            ]
        ];
        $response = Http::withOptions($this->optionAuth)->post($this->url, $data);
        $this->logApi("getContactByEmailOrPhone", $response);
        return $response->json();
    }

    function getContactByUniqueField($email, $mobile, $idNumber)
    {
        $data = [
            "RequestAction" => "getContactByUniqueField",
            "Params" => [
                "mobile" => $mobile,
                "email" => $email,
                "id_card_number" => $idNumber
            ]
        ];
        $response = Http::withOptions($this->optionAuth)->post($this->url, $data);
        $this->logApi("getContactByUniqueField", $response);
        return $response->json() ? $response->json()['contact_id'] : null;
    }

    function getInfoStudentByContactId($contactId)
    {
        $data = [
            "RequestAction" => "getInfoStudentByContactId",
            "Params" => [
                "contact_id" => $contactId
            ]
        ];
        $response = Http::withOptions($this->optionAuth)->post($this->url, $data);
        $this->logApi("getInfoStudentByContactId", $response);
        return $response->json();
    }

    function GetMasterDataList()
    {
        $data = [
            "RequestAction" => "GetMasterDataList",
        ];
        $response = Http::withOptions($this->optionAuth)->post($this->url, $data);
        $this->logApi("GetMasterDataList", $response);
        return $response->json() && isset($response->json()['success']) ? $response->json()['major_list'] : null;
    }

    function checkExistStudentById($id)
    {
        $data = [
            "RequestAction" => "checkExistStudentById",
            "Params" => [
                "contact_id" => $id
            ]
        ];
        $response = Http::withOptions($this->optionAuth)->post($this->url, $data);
        $this->logApi("checkExistStudentById", $response);
        return $response->json() ? $response->json()['contact_id'] : null;
    }

    function getLeadToday()
    {
        $data = [
            "RequestAction" => "getLeadToday"
        ];
        $response = Http::withOptions($this->optionAuth)->post($this->url, $data);
        $this->logApi("getLeadToday", $response);
        return $response->json() ? $response->json()['data'] : [];
    }

    public function logout()
    {
        // $data = ["RequestAction" => "Logout"];
        // Http::withOptions($this->optionAuth)->post($this->url, $data);
    }

    public function log($title, $messange)
    {
        \Log::channel('crm')->info("                                  ");
        \Log::channel('crm')->info("========= ". $title ." ===========");
        \Log::channel('crm')->info($messange);
        \Log::channel('crm')->info("========= ". $title ." ===========");
        \Log::channel('crm')->info("                                   ");
    }

    public function logApi($function, $response)
    {
        // CallApi::create([
        //     'url' => $this->url,
        //     'function' => $function,
        //     'data' => json_encode($response->json()),
        //     'status' => $response->status(),
        // ]);
    }
}