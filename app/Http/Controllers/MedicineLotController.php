<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\MedicineLot;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MedicineLotController extends Controller
{
    /**
     * หน้าแก้ไขข้อมูลล็อตยา
     */
    public function edit($id)
    {
        $lot = MedicineLot::with('medicine')->findOrFail($id);
        return view('medicine_lots.edit', compact('lot'));
    }

    /**
     * อัปเดตข้อมูลล็อตผ่าน AJAX (วันหมดอายุ / วันที่รับเข้า / ราคา)
     */
    public function updateAjax(Request $request, $id)
    {
        // 1. ตรวจสอบความถูกต้องของข้อมูล (Validation)
        $request->validate([
            'price_per_unit' => 'required|numeric|min:0',
            'receive_date'   => 'nullable|date',
            'expire_date'    => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();
            
            // 2. หาข้อมูลล็อตที่ต้องการแก้ไข
            $lot = MedicineLot::findOrFail($id);
            
            // 3. จัดการฟอร์แมตวันที่ก่อนบันทึก (แปลงเป็น Y-m-d หรือคงค่า NULL ไว้ถ้าไม่ได้กรอก)
            $receiveDate = $request->receive_date ? Carbon::parse($request->receive_date)->format('Y-m-d') : null;
            $expireDate = $request->expire_date ? Carbon::parse($request->expire_date)->format('Y-m-d') : null;

            // 4. อัปเดตข้อมูลทั้งหมดลงในตาราง Lots
            $lot->update([
                'price_per_unit' => $request->price_per_unit,
                'receive_date'   => $receiveDate,
                'expire_date'    => $expireDate
            ]);

            // 5. อัปเดตราคาในตาราง Medicines (ราคากลางล่าสุดสำหรับหน้าสแกนบาร์โค้ด)
            Medicine::where('id', $lot->medicine_id)->update([
                'price_per_unit' => $request->price_per_unit
            ]);

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => 'ไม่สามารถบันทึกได้: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ฟังก์ชันช่วยคำนวณยอดรวมสต็อกใหม่
     */
    private function updateMasterStock($medicineId)
    {
        $medicine = Medicine::find($medicineId);
        if ($medicine) {
            $totalStock = MedicineLot::where('medicine_id', $medicineId)->sum('quantity');
            $medicine->update([
                'stock_master' => $totalStock
            ]);
        }
    }

    /**
     * ลบล็อตยา
     */
    public function destroy($id)
    {
        try {
            $lot = MedicineLot::findOrFail($id);
            $medicineId = $lot->medicine_id;

            $lot->delete();

            // ใช้ฟังก์ชันที่มีอยู่แล้วคำนวณยอดใหม่ จะแม่นยำกว่า
            $this->updateMasterStock($medicineId);

            return back()->with('success', 'ลบล็อตยาและปรับปรุงยอดสต็อกเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            return back()->with('error', 'ลบไม่สำเร็จ: ' . $e->getMessage());
        }
    }
}