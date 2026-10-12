<?php
namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::orderBy('name', 'asc')->get();
        return view('units.index', compact('units'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:units,name']);
        Unit::create(['name' => $request->name]);
        return back()->with('success', 'เพิ่มหน่วยนับเรียบร้อยแล้ว');
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();
        return back()->with('success', 'ลบหน่วยนับเรียบร้อยแล้ว');
    }
}