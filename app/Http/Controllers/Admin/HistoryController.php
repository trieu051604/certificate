<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InsertLeadApi;
use App\Models\LogPoint;
use App\Models\ShareLead;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function shareLead($id)
    {
        $shareLeads = ShareLead::find($id);
        if ($shareLeads) {
            $leads = $shareLeads->leads()->paginate(40);
            return view('admin.leads.histories.share-lead', compact('leads'));
        }
        return redirect()->route('admin.lead.share');
    }

    public function importLeadCrm($id)
    {
        $insertLeadApi = InsertLeadApi::find($id);
        if ($insertLeadApi) {
            $leads = $insertLeadApi->leads()->paginate(40);
            return view('admin.leads.histories.import-lead-crm', compact('leads'));
        }
        return redirect()->route('admin.lead.convert');
    }

    public function convertLead($id)
    {
        $logPoint = LogPoint::find($id);
        if ($logPoint) {
            $leads = $logPoint->leads()->paginate(40);
            return view('admin.leads.histories.convert-lead', compact('leads'));
        }
        return redirect()->route('admin.lead.import-crm');
    }
}
