<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // หน้าหลัก: แสดงรายการหมวดหมู่ทั้งหมด
    public function index()
    {
        $categories = Category::orderBy('name', 'asc')->get();
        return view('settings.categories.index', compact('categories'));
    }

    // บันทึกหมวดหมู่ใหม่
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:categories,name|max:255',
        ], [
            'name.unique' => 'มีชื่อหมวดหมู่นี้อยู่ในระบบแล้ว',
        ]);

        Category::create([
            'name' => $request->name,
            'is_active' => true
        ]);

        return back()->with('success', 'เพิ่มหมวดหมู่เรียบร้อยแล้ว');
    }

    // อัปเดตชื่อหมวดหมู่
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|unique:categories,name,' . $category->id,
        ]);

        $category->update([
            'name' => $request->name,
            'is_active' => $request->has('is_active')
        ]);

        return back()->with('success', 'แก้ไขหมวดหมู่เรียบร้อยแล้ว');
    }

    // ลบหมวดหมู่
    public function destroy(Category $category)
    {
        // เช็คก่อนว่ามีเอาไปใช้ในตาราง Medicine หรือยัง (ถ้ามีไม่ควรลบ)
        $count = \App\Models\Medicine::where('category', $category->name)->count();
        
        if ($count > 0) {
            return back()->with('error', 'ไม่สามารถลบได้ เนื่องจากมีสินค้าในหมวดหมู่นี้จำนวน ' . $count . ' รายการ');
        }

        $category->delete();
        return back()->with('success', 'ลบหมวดหมู่เรียบร้อยแล้ว');
    }
}