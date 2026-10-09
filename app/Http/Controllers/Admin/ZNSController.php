<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ZNSImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ZNSController extends Controller
{
    public function import()
    {
        return view('admin.zns.index');
    }

    public function importStore(Request $request)
    {
        $file = $request->file('file');

        if ($file) {
            $extension = $file->getClientOriginalExtension();

            if ($extension == 'xls' || $extension == 'xlsx') {
                // $import = Import::create([
                //     'url' => $file->getClientOriginalName(),
                //     'user_id' => auth()->user()->id,
                // ]);

                try {
                    Excel::import(new ZNSImport(), $file);
                    return back()->with('alert', [
                        'status' => 'success',
                        'messange' => 'Import Thành Công'
                    ]);
                } catch (\Throwable $th) {
                    // $import->status = 'ERROR';
                    // $import->messange = 'File thiếu thông tin lead, ' . $th->getMessage();
                    // $import->save();
                    return back()->with('alert', [
                        'status' => 'error',
                        'messange' => 'Thiếu thông tin bắc buộc hoặc sai định dạng thông tin Tên, SĐT, Tỉnh, Trường, Ngành'
                    ]);
                }
            } else {
                return back()->with('alert', [
                    'status' => 'error',
                    'messange' => 'Vui lòng tải lên một tệp Excel (xls hoặc xlsx).'
                ]);
            }
        } else {
            return back()->with('alert', [
                'status' => 'error',
                'messange' => 'Không có tệp được tải lên.'
            ]);
        }
    }
}
