<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendZaloEndOfMonth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-zalo-end-of-month';

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
        $zns = new \ZNS();
        $zns->sendZaloOAEndMonth();
    }
}
