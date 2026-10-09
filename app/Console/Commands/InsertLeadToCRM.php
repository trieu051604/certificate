<?php

namespace App\Console\Commands;

use App\Models\InsertLeadApi;
use Illuminate\Console\Command;
use App\Jobs\InsertLeadCRM;
use App\Models\Lead;
use CRM;
use Illuminate\Support\Facades\Cache;

class InsertLeadToCRM extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    
    protected $signature = 'app:insert-lead-to-crm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    protected $crm;
    /**
     * Execute the console command.
     */
    public function handle()
    {
        // try {
        //     dispatch(new InsertLeadCRM());
        // } catch (\Exception $e) {
        //     \Log::channel('crm')->info('CALL JOB ERROR InsertLeadCRM' . $e->getMessage());
        // }
        
        $this->crm = new CRM();
        $cacheKey = 'insert_lead_crm';

        if (!Cache::add($cacheKey, true, 20 * 60)) {
            dd("Dính cache");
            return;
        }

        try {
            // $leads = Lead::where('status', '!=', 'PENDING')
            //                 ->where('status_insert', 'PENDING')
            //                 ->get();

            $leads = Lead::where('status_insert', 'PENDING')->take(10)->get();
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
            dd($e->getMessage());
        } finally {
            Cache::forget($cacheKey);
        }
    }
}
