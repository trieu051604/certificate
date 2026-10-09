<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Major;
use App\Models\Major_Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MajorGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $majorGroups = Major_Group::all();
        return view("admin.major-groups.index", compact('majorGroups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        $major_group = Major_Group::find($id);
        $majors = Major::where('major_group_id', NULL)
                        ->orWhere('major_group_id', $major_group->id)->get();
        return view('admin.major-groups.update', compact('major_group', 'majors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $major_group = Major_Group::find($id);
        $major_group->update([
            'name' => $major_group->name
        ]);
        DB::transaction(function () use ($major_group, $request) {
            Major::whereIn('id', $request->majorId)->update(['major_group_id' => NULL]);
            Major::whereIn('id', $request->majorId)->update(['major_group_id' => $major_group->id]);
        });
        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
