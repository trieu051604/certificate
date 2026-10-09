<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $minuteShareLead = setting() ? setting()->getMeta('minute_share') : 5;
        $minuteConvertLead = setting() ? setting()->getMeta('minute_convert') : 5;
        // $schedule->command('app:share-lead')->cron("*/" . $minuteShareLead . " * * * *");
        // $schedule->command('app:convert-point-to-lead')->cron("*/" . $minuteConvertLead . " * * * *");
        $schedule->command('app:send-zalo-end-of-month')
                    ->sundays()
                    ->at('20:00')
                    ->when(function () {
                        return now()->endOfMonth()->isSunday();
                    });
        // $schedule->command('app:re-import-lead-crm')->everyFiveMinutes();
        $schedule->command('app:insert-lead-to-crm')->everyMinute();
        $schedule->command('app:delete-record-log')->daily();
        $schedule->command('app:send-zns-cron')->everyTenMinutes();
        // $schedule->command('app:re-send-mail-certification')->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
