<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use CRM;

class LoginCRM extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:login-crm';

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
        // $crm = new CRM();
        // $token = $crm->login();
        // if ($token) {
        //     setting()->setMeta('token-crm', $token);
        // }
    }
}
