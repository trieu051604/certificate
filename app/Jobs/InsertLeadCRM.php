<?php

namespace App\Jobs;

use App\Models\InsertLeadApi;
use App\Models\Lead;
use CRM;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class InsertLeadCRM implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create a new job instance.
     */
    protected $crm;
    public function __construct()
    {
        $this->crm = new CRM();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $cacheKey = 'insert_lead_crm';

        if (!Cache::add($cacheKey, true, 20 * 60)) {
            return;
        }

        try {
            // $leads = Lead::where('status', '!=', 'PENDING')
            //                 ->where('status_insert', 'PENDING')
            //                 ->get();

            $leads = Lead::where('status_insert', 'PENDING')->take(20)->get();
            if (count($leads) > 0) {
                $insertLead = new InsertLeadApi();
                $insertLead->save();
                try {
                    $this->crm->importLeads($leads);
                    $insertLead->status = 'SUCCESS';
                    $insertLead->messange = 'Thêm leads vào CRM thành công';
                } catch (\Exception $e) {
                    $insertLead->status = 'ERROR';
                    $insertLead->messange = $e->getMessage();
                } finally {
                    $insertLead->save();
                }
            }
        } catch (\Exception $e) {
            \Log::channel('crm')->info($e->getMessage());
        } finally {
            Cache::forget($cacheKey);
        }
    }
}
