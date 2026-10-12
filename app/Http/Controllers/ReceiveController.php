<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medicine;
use Illuminate\Support\Facades\DB;

class ReceiveController extends Controller
{
    // หน้าฟอร์มรับยาเข้า
    public function create() {
        $medicines = Medicine::all();
        return view('receives.create', compact('medicines'));
    }

    // ระบบบันทึกการรับเข้า (เพิ่มสต็อก Master)
   public function store(Request $request) {
    $request->validate([
        'barcode'      => 'required|string',
        'name'         => 'required|string',
        'quantity'     => 'required|integer|min:1',
        'company_name' => 'required|string', // บริษัทที่ส่งยามา (เช่น AAA)
        'expire_date'  => 'required|date',
    ]);

    \DB::transaction(function () use ($request) {
        // ใช้ updateOrCreate เพื่อจัดการทั้งยาใหม่และยาเดิม
        $medicine = \App\Models\Medicine::updateOrCreate(
            ['barcode' => $request->barcode], // ค้นหาจากบาร์โค้ด
            [
                'name'          => $request->name,
                'company_name'  => $request->company_name,
                'supplier_name' => $request->company_name, // เพิ่มบรรทัดนี้เพื่อป้องกัน Error 1364
                'expire_date'   => $request->expire_date,
            ]
        );

        // เพิ่มจำนวนในคลังใหญ่
        $medicine->increment('stock_master', $request->quantity);
    });

    return redirect()->route('transfers.index')->with('success', 'บันทึกข้อมูลสำเร็จ');
}
}