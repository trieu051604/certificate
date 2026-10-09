<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\LeadImport;
use App\Models\Import;
use App\Models\InsertLeadApi;
use App\Models\Lead;
use App\Models\LogPoint;
use App\Models\ShareLead;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LeadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $leads = Lead::orderBy('status', 'desc')->orderBy('created_at', 'desc');
        if (request()->has('import_file') && request()->import_file) {
            $import = Import::find(request()->import_file);
            if ($import && $import->status == 'SUCCESS') {
                $leads = $import->leads();
            }
        }
        if (request()->email) {
            $leads->where('email', 'like', '%' . request()->email . '%');
        }
        if (request()->phone) {
            $leads->where('phone', 'like', '%' . request()->phone . '%');
        }
        if (request()->has('type') && request()->type) {
            $leads->where('type', request()->type);
        }
        if (request()->has('status') && request()->status) {
            $leads->where('status', request()->status);
        }
        if (request()->has('status_insert') && request()->status_insert) {
            $leads->where('status_insert', request()->status_insert);
        }
        if (request()->has('status_convert') && request()->status_convert) {
            $leads->where('status_convert', request()->status_convert);
        }
        if (request()->has('sale_id') && request()->sale_id) {
            $userId = request()->sale_id;
            $leads->whereHas('user', function($q) use ($userId) {
                $q->where('id', $userId);
            });
        }
        $countLead = (clone $leads)->count();
        $leads = (clone $leads)->paginate(40);
        $imports = Import::all();
        $sales = User::where('type', 'MANAGER')->get();
        return view('admin.leads.list', compact('leads', 'imports', 'countLead', 'sales'));
    }

    public function import()
    {
        $imports = Import::orderBy('created_at', 'desc')->paginate(40);
        return view('admin.leads.import', compact('imports'));
    }

    public function share()
    {
        $shares = ShareLead::orderBy('created_at', 'desc')->paginate(40);
        return view('admin.leads.share', compact('shares'));
    }

    public function convert()
    {
        $query = LogPoint::orderBy('created_at', 'desc');
        if (request()->has('sale_id') && request()->sale_id) {
            $userId = request()->sale_id;
            $query->whereHas('user', function($q) use ($userId) {
                $q->where('id', $userId);
            });
        }
        $converts = $query->paginate(40);
        $sales = User::where('type', 'MANAGER')->get();
        return view('admin.leads.convert', compact('converts', 'sales'));
    }

    public function importCrm()
    {
        $imports = InsertLeadApi::orderBy('created_at', 'desc')->paginate(40);
        return view('admin.leads.import-crm', compact('imports'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.leads.add');
    }

    public function createItem()
    {
        return view('admin.leads.create-item');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $file = $request->file('file');

        if ($file) {
            $extension = $file->getClientOriginalExtension();

            if ($extension == 'xls' || $extension == 'xlsx') {
                $import = Import::create([
                    'url' => $file->getClientOriginalName(),
                    'user_id' => auth()->user()->id,
                ]);

                try {
                    Excel::import(new LeadImport($import), $file);
                    return back()->with('alert', [
                        'status' => 'success',
                        'messange' => 'Import Thành Công'
                    ]);
                } catch (\Throwable $th) {
                    $import->status = 'ERROR';
                    $import->messange = 'File thiếu thông tin lead, ' . $th->getMessage();
                    $import->save();
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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
