<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CallApi;
use Carbon\Carbon;

class DeleteRecordLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-record-log';

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
        try {
            $today = Carbon::now()->startOfDay();

            // Xóa tất cả các bản ghi có ngày tạo trước ngày hôm nay
            CallApi::whereDate('created_at', '<', $today)->delete();
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }
}
