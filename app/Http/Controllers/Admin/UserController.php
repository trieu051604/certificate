<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campus;
use App\Models\Major_Group;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $keySearch = ['number_lead', 'number_lead_convert'];
    public function index()
    {
        $stringTimeStart = request()->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $stringTimeEnd = request()->start_end ?? now()->endOfMonth()->format('Y-m-d');
        $startDate =  Carbon::parse($stringTimeStart)->startOfDay();
        $endDate =  Carbon::parse($stringTimeEnd)->endOfDay();

        $users = User::withCount([
            'leads as number_lead' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])->where('status_insert', 'SUCCESS');
            },
            'leads as number_lead_convert' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])->where('status_convert', 'CONVERTED');
            }
        ]);

        if (request()->key && in_array(request()->key, $this->keySearch) && request()->type) {
            $users->orderBy(request()->key, request()->type);
        }

        $users = $users->where('type', '<>', 'ADMIN')->get();
        
        return view('admin.users.index', compact('users', 'stringTimeStart', 'stringTimeEnd'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // $campus = Campus::all();
        // return view('admin.users.create', compact('campus'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'regex:/^(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/'
            ],
            'type' => 'required'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'type' => $request->type,
            'campus_id' => $request->type === 'ADMIN' ? null : $request->campus_id
        ]);

        return redirect()->route('user.index', $request->event_id)->with(['level' => 'success', 'message' => 'Thêm thành công!']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::find($id);
        $majorGroups = Major_Group::all();
        return view('admin.users.update', compact('user', 'majorGroups'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::find($id);

        if ($user) {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);
            $user->major_group()->sync($request->majorGroupId);
            return back();
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);
        if ($user) {
            $user->delete();
            return back()->with(['level' => 'success', 'message' => 'Xóa thành công']);
        }
        return back()->with(['level' => 'error', 'message' => 'Không tồn tại']);
    }

    public function changePass()
    {
        $user = Auth::user();
        return view('admin.users.update', compact('user'));
    }
}
