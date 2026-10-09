<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\CheckInExport;
use Maatwebsite\Excel\Facades\Excel;

class HelperController extends Controller
{
    public function checkToken($token)
    {
        return env('TOKEN_HELPER') == $token;
    }

    public function getWards(Request $request)
    {
        if (!$this->checkToken($request->token)) {
            return response()->json([
                'status' => 'error'
            ]);
        };

        $wards = \DB::table('vtiger_cpwards')
                    ->select(['cpwardsid', 'name', 'related_cpdistrict', 'cpwards_type'])
                    ->get()
                    ->map(function ($item) {
                    return (object) [
                        'key' => $item->cpwardsid,
                        'label' => $item->name,
                        'type' => $item->cpwards_type,
                        'in_district' => $item->related_cpdistrict
                    ];
                });

        return response()->json([
            'status' => 'success',
             'ListsInfo' => [
                 'contact_student_sub_districts' => $wards
            ]
        ]);
    }

    public function getDistricts(Request $request)
    {

        if (!$this->checkToken($request->token)) {
            return response()->json([
                'status' => 'error'
            ]);
        };

        // $districts = \DB::table('vtiger_cpdistrict')
        //                 ->select(['cpdistrictid', 'name', 'cpdistrict_type', 'accounts_city'])
        //                 ->get()
        $districts = \DB::table('vtiger_cpdistrict')
        ->select(['cpdistrictid', 'name', 'cpdistrict_type', 'accounts_city'])
        ->get()
        ->map(function ($item) {
            return (object)  [
                'key' => $item->cpdistrictid,
                'label' => $item->name,
                'type' => $item->cpdistrict_type,
                'province_id' => $item->accounts_city,
                'in_province' => $item->accounts_city
            ];
        });


        return \response()->json([
            'status' => 'success',
             'ListsInfo' => [
                 'contact_student_districts' => $districts
            ]
            
           
        ]);
    }


     public function getSchools(Request $request)
    {
        if (!$this->checkToken($request->token)) {
            return response()->json([
                'status' => 'error'
            ]);
        };

        // $districts = \DB::table('vtiger_cpdistrict')
        //                 ->select(['cpdistrictid', 'name', 'cpdistrict_type', 'accounts_city'])
        //                 ->get()
        $schools = \DB::table('vtiger_account')
        ->select(['accountid', 'accountname', 'accounts_city'])
        ->get()
        ->map(function ($item) {
            return (object) [
                'key' => $item->accountid,
                'label' => $item->accountname,
                'in_province' => $item->accounts_city
            ];
        });


        return \response()->json([
            'status' => 'success',
           'ListsInfo' => [
                 'schools' => $schools
            ]
           
        ]);
    }

     public function getProvincesNew(Request $request)
    {
        if (!$this->checkToken($request->token)) {
            return response()->json([
                'status' => 'error'
            ]);
        };

        // $districts = \DB::table('vtiger_cpdistrict')
        //                 ->select(['cpdistrictid', 'name', 'cpdistrict_type', 'accounts_city'])
        //                 ->get()
        $provinces = \DB::table('province_certification')
        ->select(['id', 'province'])
        ->get();
        // ->map(function ($item) {
        //     return (object) [
        //         'key' => $item->key,
        //         'label' => $item->name
                
        //     ];
        // });


        return \response()->json([
            'status' => 'success',
           'ListsInfo' => [
                 'provinces' => $provinces
            ]
           
        ]);
    }





    public function getSchoolsNew(Request $request)
    {
        if (!$this->checkToken($request->token) || !($request->id_tinh)) {
            return response()->json([
                'status' => 'error'
            ]);
        };

        $id_tinh = $request->id_tinh;
      
        $schools = \DB::table('school_certification')
        ->select(['id', 'school', 'province_id'])
        ->where('province_id',$id_tinh)
        ->get();
        // ->map(function ($item) {
        //     return (object) [
        //         'key' => $item->accountid,
        //         'label' => $item->accountname,
        //         'in_province' => $item->accounts_city
        //     ];
        // });


        return \response()->json([
            'status' => 'success',
           'ListsInfo' => [
                 'schools' => $schools
            ]
           
        ]);
    }




     public function getProvinces(Request $request)
    {
        if (!$this->checkToken($request->token)) {
            return response()->json([
                'status' => 'error'
            ]);
        };

        // $districts = \DB::table('vtiger_cpdistrict')
        //                 ->select(['cpdistrictid', 'name', 'cpdistrict_type', 'accounts_city'])
        //                 ->get()
        $provinces = \DB::table('tinhtp')
        ->select(['name', 'key'])
        ->get()
        ->map(function ($item) {
            return (object) [
                'key' => $item->key,
                'label' => $item->name
                
            ];
        });


        return \response()->json([
            'status' => 'success',
           'ListsInfo' => [
                 'provinces' => $provinces
            ]
           
        ]);
    }



     public function getSchoolsViaPro(Request $request,$province)
    {
        if (!$this->checkToken($request->token)) {
            return response()->json([
                'status' => 'error'
            ]);
        };

         if (!$province) {
            return response()->json([
                'status' => 'error'
            ]);
        };

        // echo $province;

        // $districts = \DB::table('vtiger_cpdistrict')
        //                 ->select(['cpdistrictid', 'name', 'cpdistrict_type', 'accounts_city'])
        //                 ->get()
        $schools = \DB::table('vtiger_account')
        ->select(['accountid', 'accountname', 'accounts_city'])
        ->get()
        ->map(function ($item) {
            return (object) [
                'key' => $item->accountid,
                'label' => $item->accountname,
                'in_province' => $item->accounts_city
            ];
        });

       $mang_quanhuyen = array();

        foreach ($schools as $key => $value) {
            if($value->in_province == $province){
                $mang_quanhuyen[] = $value;
            }
         }

            return response()->json($mang_quanhuyen);

       
    }

    public function checkIn(Request $request)
    {


        if (!$this->checkToken($request->token)) {
            return response()->json([
                'status' => 'error'
            ]);
        };

        try {

            $checkIn = DB::table('checkin')->insert([
                'fullname' => $request->fullname,
                'email' => $request->email,
                'phone' => $request->phone,
                'province' => $request->province,
                'school' => $request->school,
                'mon_hoc' => $request->mon_hoc,
                'khoa_hoc' => $request->khoa_hoc,
                'ip' => $request->ip(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
    
            return response()->json([
                'status' => $checkIn ? 'success' : 'error'
            ]);
        } catch (Exception $e) {
            // throw $th->getMessage();
            return response()->json([
                'status' => 'error'
            ]);
        }
    }

    public function showCheckIn()
    {
        $query = DB::table('checkin');
        if (request()->phone) {
            $query = $query->where('phone', 'LIKE', '%'.request()->phone.'%');
        }
        if (request()->startDate && request()->endDate) {
            $query = $query->whereDate('created_at', '>=', request()->startDate)
                            ->whereDate('created_at', '<=', request()->endDate);
        }
        $lists = (clone $query)->orderBy('created_at', 'desc')->paginate(100);
        $count = $query->count();
        return view('check-in', compact('lists', 'count'));
    }

    public function exportCheckIn()
    {
        $query = DB::table('checkin');
        if (request()->phone) {
            $query = $query->where('phone', 'LIKE', '%'.request()->phone.'%');
        }
        if (request()->startDate && request()->endDate) {
            $query = $query->whereDate('created_at', '>=', request()->startDate)
                            ->whereDate('created_at', '<=', request()->endDate);
        }
        $data = $query->get();
        return Excel::download(new CheckInExport($data), 'checkin.xlsx');
    }

    public function traCuu(Request $request)
    {
        $cccd = $request->cccd;
        return DB::table('tra-cuu')->where('cccd', $cccd)->first();
    }


}



