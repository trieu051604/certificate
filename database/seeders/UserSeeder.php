<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    //    User::create([
    //         'name' => 'ADMIN',
    //         'email' => 'adminfpt@gmail.com',
    //         'password' => Hash::make('123456'),
    //         'type' => 'ADMIN'
    //    ]);
    //         User::create([
    //             'name' => 'API',
    //             'email' => 'apicall@gmail.com',
    //             'password' => Hash::make('6fy7oM1QG43yOXiD2SuxKnpo0NaYtr'),
    //             'type' => 'API'
    //     ]);
    //     User::create([
    //         'name' => 'ZNS',
    //         'email' => 'zns@gmail.com',
    //         'password' => Hash::make('tQw3FP3ybnEFrEqM3TnpITyZCrFaCe3t'),
    //         'type' => 'ZNS'
    //     ]);
        // User::create([
        //     'name' => 'StudentAdmin',
        //     'email' => 'studentadmin@gmail.com',
        //     'password' => Hash::make('tQw3FP3ybnEFrEqM3TnpITyZCrFaCe3t'),
        //     'type' => 'API'
        // ]);
    }
}
