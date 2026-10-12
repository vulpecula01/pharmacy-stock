<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Staff;

class StaffController extends Controller
{
    public function index()
    {
        $staffs = Staff::orderBy('name', 'asc')->get();
        return view('staff.index', compact('staffs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Staff::create([
            'name' => $request->name,
            'is_active' => true
        ]);

        return back()->with('success', 'เพิ่มรายชื่อเจ้าหน้าที่เรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $staff = Staff::findOrFail($id);
        $staff->delete();

        return back()->with('success', 'ลบรายชื่อเจ้าหน้าที่เรียบร้อยแล้ว');
    }
}