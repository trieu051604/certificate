<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class SettingController extends Controller
{
    public function index()
    {
        return view("admin.setting");
    }
    public function store(Request $request)
    {
        $data = $request->all();
        $system = Setting::where('name', 'index')->first();
        $data = Arr::except($data, ['_token', 'page']);
        foreach ($data as $key => $item) {
            $system->setMeta($key, $item);
        }
        return back()->with('success', 'Cập nhật thành công');
    }
}
