<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medicine;
use App\Models\Transfer;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    /**
     * หน้าแรก: แสดงยอดคงเหลือของทุกคลัง และประวัติการเบิกจ่ายล่าสุด
     */
    public function index() {
    $medicines = Medicine::all();
    
    // ปรับส่วนนี้: ให้ดึงข้อมูลยาแม้จะถูก Soft Delete ไปแล้วก็ตาม
    $recentTransfers = Transfer::with(['medicine' => function($query) {
        $query->withTrashed(); 
    }])->latest()->take(10)->get();

    return view('transfers.index', compact('medicines', 'recentTransfers'));
}

    /**
     * หน้าฟอร์มเบิกยาจากคลังใหญ่ไปคลังย่อย
     */
    public function create()
    {
        $medicines = Medicine::where('stock_master', '>', 0)->get();
        return view('transfers.create', compact('medicines'));
    }

    /**
     * ระบบบันทึกการเบิกจ่าย (ใช้ Transaction เพื่อความปลอดภัยของข้อมูล)
     */
    public function store(Request $request)
    {
        $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'to_location' => 'required|in:stock_a,stock_b,stock_c',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $medicine = Medicine::findOrFail($request->medicine_id);

                // ตรวจสอบสต็อกในคลังใหญ่ก่อนหัก
                if ($medicine->stock_master < $request->quantity) {
                    throw new \Exception('จำนวนยาในคลังใหญ่ไม่เพียงพอสำหรับการเบิก');
                }

                // 1. หักจำนวนออกจากคลังใหญ่ (Master)
                $medicine->decrement('stock_master', $request->quantity);
                
                // 2. เพิ่มจำนวนเข้าคลังปลายทาง (A, B หรือ C)
                $medicine->increment($request->to_location, $request->quantity);
                
                // 3. บันทึกประวัติการเบิกจ่ายลงในตาราง transfers
                Transfer::create([
                    'medicine_id'   => $request->medicine_id,
                    'from_location' => 'stock_master',
                    'to_location'   => $request->to_location,
                    'quantity'      => $request->quantity,
                    'expire_date'   => $medicine->expire_date, // บันทึกวันหมดอายุ ณ ตอนที่เบิก
                ]);
            });

            return redirect()->route('transfers.index')->with('success', 'เบิกจ่ายยาสำเร็จแล้ว');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * หน้าฟอร์มแก้ไขข้อมูลยา (ชื่อ, บาร์โค้ด, บริษัท)
     */
    public function edit($id)
    {
        $medicine = Medicine::findOrFail($id);
        return view('medicines.edit', compact('medicine'));
    }

    /**
     * อัปเดตข้อมูลยาที่แก้ไข
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'required|string|unique:medicines,barcode,' . $id,
            'company_name' => 'required|string',
        ]);

        $medicine = Medicine::findOrFail($id);
        
        // อัปเดตข้อมูลทั้งหมดที่ส่งมาจากฟอร์ม
        $medicine->update($request->all());

        return redirect()->route('transfers.index')->with('success', 'แก้ไขข้อมูลยาเรียบร้อยแล้ว');
    }

    /**
     * ลบยาออกจากระบบ
     */
    public function destroy($id)
    {
        $medicine = Medicine::findOrFail($id);
        
        // เช็คความปลอดภัย: ห้ามลบหากมียาค้างอยู่ในคลังใดๆ
        $totalStock = $medicine->stock_master + $medicine->stock_a + $medicine->stock_b + $medicine->stock_c;

        if ($totalStock > 0) {
            return back()->with('error', 'ไม่สามารถลบได้เนื่องจากยังมีมียาค้างอยู่ในคลัง (รวม ' . $totalStock . ' ชิ้น)');
        }

        $medicine->delete();
        return redirect()->route('transfers.index')->with('success', 'ลบยาออกจากระบบเรียบร้อยแล้ว');
    }
    public function clearStock($id)
{
    $medicine = Medicine::findOrFail($id);
    
    // ปรับยอดทุกคลังให้เป็น 0
    $medicine->update([
        'stock_master' => 0,
        'stock_a' => 0,
        'stock_b' => 0,
        'stock_c' => 0,
    ]);

    return back()->with('success', 'ล้างสต็อกยา ' . $medicine->name . ' เรียบร้อยแล้ว ตอนนี้คุณสามารถลบยาได้');
}
}