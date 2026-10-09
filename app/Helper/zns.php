<?php



class ZNS {
    private $url;
    private $secretKey;
    private $appId;
    private $templateId;
    private $ext;
    public function __construct($ext = '')
    {
        $this->ext = $ext;
        $this->url = env('ZNS_URL');
        $this->secretKey = env('ZNS_SECRET_KEY' . $ext);
        $this->appId = env('ZNS_APP_ID' . $ext);
        $this->templateId = env('ZNS_TEMPLATE_ID' . $ext);
    }

    public function send($lead)
    {
        try {
            $phone = $lead->phone;
            $fullname = ucwords($lead->fullname);
            $majorName = $lead->major->major;
            $date = " ".date('d/m/Y');
            $ma_id_dki = "LEA".rand(1000,10000);
            $postData = [
                "phone" => $phone,
                "template_id" => $this->templateId,
                "template_data" => [
                    "customer_name" => $fullname,
                    "date" => $date,
                    "ten_nganh" => $majorName,
                    "Ma_dang_ky" => $ma_id_dki
                ],
                "failover" => 1
            ];

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($postData),
                CURLOPT_HTTPHEADER => array(
                    'app-id: ' .$this->appId,
                    'secret-key: ' . $this->secretKey,
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            return $response;
        } catch (\Throwable $th) {
            return null;
        }
    }

    public function sendV2($lead)
    {
        try {
            $postData = [
                "phone" => $lead['mobile'],
                "template_id" => $this->templateId,
                "template_data" => [
                    "customer_name" => ucwords($lead['lastname']),
                    "CBTV" => $lead['last_name'],
                    "ten_nganh" => $lead['productname'],
                    "Ma_dang_ky" => "LEA".rand(1000,10000)
                ],
                "failover" => 1
            ];

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($postData),
                CURLOPT_HTTPHEADER => array(
                    'app-id: ' .$this->appId,
                    'secret-key: ' . $this->secretKey,
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            return $response;
        } catch (\Throwable $th) {
            return null;
        }
    }

    public function sendByExcel($postData)
    {
        try {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($postData),
                CURLOPT_HTTPHEADER => array(
                    'app-id: ' .$this->appId,
                    'secret-key: ' . $this->secretKey,
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            return $response;
        } catch (\Throwable $th) {
            return null;
        }
    }

    public function sendZaloOAEndMonth()
    {
        // $date = Date('d/m/Y');
        // $lastDayOfMonth = date("t/m/Y");
        // if($date == $lastDayOfMonth){
        
            $crm = new \CRM();
            $requestResult = $crm->getLeadToSendZalo();

            if($requestResult && isset($requestResult->success) && $requestResult->success == 1){

                foreach ($requestResult->lead_info as $key => $value) {
                
                    if($value->giao_cho != ""){

                        $ma_id_dki = "LEA".rand(1000,10000);
                        $templateData = [
                            "ho_ten" => $value->name,
                            "ten_can_bo" => $value->giao_cho,
                            "ma_khao_sat" => $ma_id_dki,
                            
                        ];

                        $postData = [
                            "phone" => $value->mobile,
                            "template_id" => '5127',
                            "template_data" => $templateData,
                            "failover" => 1
                        ];

                        $this->sendByExcel($postData);
                    }
                }
            }
        // }
    }
}

// class ZNSOTHER{
//     private $url;
//     private $secretKey;
//     private $appId;
//     private $templateId;
//     private $ext;
//     public function __construct($ext)
//     {
//         $this->ext = $ext;
//         $this->url = env('ZNS_URL');
//         $this->secretKey = env('ZNS_SECRET_KEY' . $ext);
//         $this->appId = env('ZNS_APP_ID' . $ext);
//         $this->templateId = env('ZNS_TEMPLATE_ID' . $ext);
//     }
//     public function send($lead)
//     {
//         try {
//             $phone = $lead->phone;
//             $fullname = ucwords($lead->fullname);
//             $majorName = $lead->major->major;
//             $date = " ".date('d/m/Y');
//             $ma_id_dki = "LEA".rand(1000,10000);
//             $postData = [
//                 "phone" => $phone,
//                 "template_id" => $this->templateId,
//                 "template_data" => [
//                     "customer_name" => $fullname,
//                     "date" => $date,
//                     "ten_nganh" => $majorName,
//                     "Ma_dang_ky" => $ma_id_dki
//                 ],
//                 "failover" => 1
//             ];

//             $curl = curl_init();

//             curl_setopt_array($curl, array(
//                 CURLOPT_URL => $this->url,
//                 CURLOPT_RETURNTRANSFER => true,
//                 CURLOPT_ENCODING => '',
//                 CURLOPT_MAXREDIRS => 10,
//                 CURLOPT_TIMEOUT => 0,
//                 CURLOPT_SSL_VERIFYPEER => 0,
//                 CURLOPT_FOLLOWLOCATION => true,
//                 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//                 CURLOPT_CUSTOMREQUEST => 'POST',
//                 CURLOPT_POSTFIELDS => json_encode($postData),
//                 CURLOPT_HTTPHEADER => array(
//                     'app-id: ' .$this->appId,
//                     'secret-key: ' . $this->secretKey,
//                     'Content-Type: application/json'
//                 ),
//             ));

//             $response = curl_exec($curl);

//             curl_close($curl);

//             return $response;
//         } catch (\Throwable $th) {
//             return null;
//         }
//     }
// }