<?php

namespace App\Jobs;

use CRM;
use ZNS;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class sendZNSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected CRM $crm;
    protected ZNS $zns;
    public function __construct()
    {
        $this->crm = new CRM();
        $this->zns = new ZNS();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $cacheKey = 'send_zns_job';
        if (!Cache::add($cacheKey, true, 20 * 60)) {
            // dd(123);
            return;
        }

        try {
            $leads = $this->crm->getLeadToday();
            // dd($leads);
            foreach ($leads as $lead) {
                $this->zns->sendV2($lead);
            }
        } catch (\Exception $e) {
            // dd($e->getMessage());
        } finally {
            Cache::forget($cacheKey);
        }
    }
}
