<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $setting = Setting::create([ 'name' => 'index' ]);
        // $setting->setMeta('url', 'http://103.191.146.26:90/api/WebsiteApijdsfksdhfakjsd.php');
        // $setting->setMeta('username', 'quocviet1904');
        // $setting->setMeta('password', 'QUOCviet2312');
        $setting->setMeta('minute_share', 5);
        $setting->setMeta('number_lead_by_point', 3);
        $setting->setMeta('minute_convert', 120);
        $setting->setMeta('point', 5);
        $setting->setMeta('type_import', 'DEFAULT');
        Artisan::call('app:login-crm');
    }
}
