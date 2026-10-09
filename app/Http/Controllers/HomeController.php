<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $leadAuto = Lead::where('type', 'DEFAULT')->get();
        $leadPerformance = Lead::where('type', 'PERFORMANCE')->get();
        // $leadApi = Lead::where('type', NULL)->where('import_id', 'NULL')->get();

//         ->where('import_id', '<>', NULL)
// ->where('import_id', '<>', NULL)

        $countPendingShareAutoFromImport = (clone $leadAuto)->where('import_id', '<>', NULL)->count();
        $countPendingShareAutoFromAPI = (clone $leadAuto)->where('import_id', NULL)->count();
        $countPendingShareAuto = (clone $leadAuto)->where('status', 'PENDING')->count();
        $countPendingImportAuto = (clone $leadAuto)->where('status_insert', 'PENDING')->count();
        $countPendingConvertAuto = (clone $leadAuto)->where('status_convert', 'PENDING')->count();

        $countPendingSharePerformanceFromImport = (clone $leadPerformance)->where('import_id', '<>', NULL)->count();
        $countPendingSharePerformanceFromAPI = (clone $leadPerformance)->where('import_id', NULL)->count();
        $countPendingSharePerformance = (clone $leadPerformance)->where('status', 'PENDING')->count();
        $countPendingImportPerformance = (clone $leadPerformance)->where('status_insert', 'PENDING')->count();
        $countPendingConvertPerformance = (clone $leadPerformance)->where('status_convert', 'PENDING')->count();

        // $countPendingShareApi = (clone $leadApi)->where('status', 'PENDING')->count();
        // $countPendingImportApi = (clone $leadApi)->where('status_insert', 'PENDING')->count();
        // $countPendingConvertApi = (clone $leadApi)->where('status_convert', 'PENDING')->count();

        return view('admin.index', compact(
            'countPendingShareAuto', 'countPendingImportAuto', 'countPendingConvertAuto', 'countPendingSharePerformance', 
            'countPendingImportPerformance', 'countPendingConvertPerformance', 'leadAuto', 'leadPerformance',
            'countPendingShareAutoFromImport', 'countPendingShareAutoFromAPI', 'countPendingSharePerformanceFromImport', 'countPendingSharePerformanceFromAPI'
            // 'leadApi', 'countPendingShareApi', 'countPendingImportApi', 'countPendingConvertApi'
        ));
    }
}
