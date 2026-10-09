<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\sendZNSJob;
use CRM;
use ZNS;
use Illuminate\Support\Facades\Cache;

class SendZNSCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-zns-cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $cacheKey = 'send_zns_job';
        Cache::forget($cacheKey);
        if (!Cache::add($cacheKey, true, 20 * 60)) {
            var_dump(1);
            return;
        }

        try {
            var_dump(2);
            $crm = new CRM();
            $zns = new ZNS();
            $leads = $crm->getLeadToday();
            foreach ($leads as $lead) {
                $zns->sendV2($lead);
            }
        } catch (\Exception $e) {
            \Log::channel('crm')->info('CALL JOB ERROR sendZNSJob' . $e->getMessage());
        } finally {
            var_dump(3);
            Cache::forget($cacheKey);
        }
    }
}
