<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use CRM;

class ConvertPointToLead extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'app:convert-point-to-lead';

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
        // $sales = User::where('type', 'MANAGER')->get();
        // if ($sales) {
        //     $crm = new CRM();
        //     foreach ($sales as $sale) {
        //         $crm->convertPointToLead($sale);
        //     }
        // }
    }
}
