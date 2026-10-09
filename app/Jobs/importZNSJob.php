<?php

namespace App\Jobs;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class importZNSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $leads;
    public function __construct($leads)
    {
        $this->leads = $leads;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $keys = [];
        $zns = new \ZNS();
        foreach ($this->leads as $index => $lead) {
            if ($index !== 0) {
                $templateData = [];
                // $templateData["date"] = " ".date('d/m/Y');
                // $templateData["Ma_dang_ky"] = "LEA".rand(1000,10000);
                foreach ($keys as $index => $key) {
                    if ($index >= 2) {
                        $templateData[$key] = $lead[$index];
                    }
                }
                $data = [
                    "phone" => $lead[0],
                    "template_id" => $lead[1],
                    "template_data" => $templateData,
                    "failover" => 1
                ];
                $zns->sendByExcel($data);
            } else {
                $keys = $lead;
            }
        }
    }
}
