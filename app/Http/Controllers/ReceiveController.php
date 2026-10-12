<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medicine;
use App\Models\MedicineLot;
use App\Models\Transfer;
use Illuminate\Support\Facades\DB;
use App\Models\Category;

class ReceiveController extends Controller
{
   public function create()
{
    // ดึงหน่วยนับที่เปิดใช้งาน
    $units = \App\Models\Unit::where('is_active', true)->get();
    
    // เพิ่ม: ดึงหมวดหมู่ที่เปิดใช้งาน เพื่อส่งไปแสดงในหน้า Form
    $categories = \App\Models\Category::where('is_active', true)->orderBy('name', 'asc')->get();

    // แก้ไข: เปลี่ยน path view ให้ตรงกับที่คุณเก็บไฟล์ไว้ (ในที่นี้คือ pharmacy.receives ตามที่เห็นในรูปก่อนหน้า)
    return view('receives.create', compact('units', 'categories'));
}

    /**
     * สำหรับหน้า "รับยาเข้าคลังใหญ่" (เพิ่มสต็อก)
     */
    
public function store(Request $request)
{
    $request->validate([
        'barcode'        => 'required',
        'name'           => 'required',
        'category'       => 'required',
        'quantity'       => 'required|integer|min:1',
        'price_per_unit' => 'required|numeric',
        'expire_date'    => 'required|date',
        'receive_date'   => 'required|date', 
    ]);

    try {
        DB::beginTransaction();

        // 1. ค้นหาหรือเตรียมข้อมูลยา
        $medicine = Medicine::where('barcode', $request->barcode)->first();

        // --- ส่วนการเจนชื่อ Lot อิงตามชื่อยา และ วันที่รับเข้าจริงที่เลือก ---
        $cleanName = str_replace(' ', '', $request->name);
        $prefix = strtoupper(mb_substr($cleanName, 0, 4, 'UTF-8')); 
        
        $chosenDate = \Carbon\Carbon::parse($request->receive_date);
        $autoLotName = $request->lot_number ?: $prefix . '-' . $chosenDate->format('ymd') . date('Hi');

        // 2. จัดการตาราง medicines (Master Table)
        if ($medicine) {
            $medicine->increment('stock_master', $request->quantity);
            $medicine->update([
                'name'           => $request->name,
                'category'       => $request->category,
                'company_name'   => $request->company_name,
                'unit_name'      => $request->unit_name,
                'expire_date'    => $request->expire_date,
                'price_per_unit' => $request->price_per_unit,
                'min_stock'      => $request->min_stock,
                'max_stock'      => $request->max_stock,
            ]);
        } else {
            $medicine = Medicine::create([
                'barcode'        => $request->barcode,
                'name'           => $request->name,
                'category'       => $request->category,
                'price_per_unit' => $request->price_per_unit,
                'stock_master'   => $request->quantity,
                'unit_name'      => $request->unit_name,
                'company_name'   => $request->company_name,
                'expire_date'    => $request->expire_date,
                'min_stock'      => $request->min_stock,
                'max_stock'      => $request->max_stock,
            ]);
        }

        // 3. จัดการตาราง medicine_lots (Stock แยกตามล็อต)
        $lot = MedicineLot::where('medicine_id', $medicine->id)
            ->where('lot_number', $autoLotName)
            ->where('expire_date', $request->expire_date)
            ->first();

        if ($lot) {
            $lot->increment('quantity', $request->quantity);
            $lot->update([
                'price_per_unit' => $request->price_per_unit,
                'receive_date'   => $request->receive_date // อัปเดตวันที่รับเข้าล่าสุดที่นี่
            ]);
        } else {
            $lot = MedicineLot::create([
                'medicine_id'    => $medicine->id,
                'expire_date'    => $request->expire_date,
                'lot_number'     => $autoLotName,
                'quantity'       => $request->quantity,
                'price_per_unit' => $request->price_per_unit,
                'receive_date'   => $request->receive_date, // บันทึกวันที่รับเข้าลงตารางล็อตโดยตรง
            ]);
        }

        // ❌ เอาขั้นตอนที่ 4 (Transfer::create) ออกไปแล้ว ข้อมูลจะไม่ถูกบันทึกลงตาราง transfers อีกต่อไป

        DB::commit();
        return back()->with('success', "รับยาเข้าคลังเรียบร้อย! ล็อตที่สร้าง: $autoLotName");

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withInput()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
    }
}

    /**
     * ตัดสต็อกแบบ FEFO (First Expire, First Out)
     */
    public function withdraw(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $medicine = Medicine::where('barcode', $request->barcode)->first();
            
            if (!$medicine || $medicine->stock_master < $request->quantity) {
                return redirect()->back()->with('error', 'สินค้าไม่พอหรือไม่มีในระบบ');
            }

            $qtyToWithdraw = $request->quantity;
            $remaining = $qtyToWithdraw;

            // ดึง Lot ที่มีของ เรียงตามวันหมดอายุที่ใกล้ที่สุดก่อน
            $lots = MedicineLot::where('medicine_id', $medicine->id)
                                ->where('quantity', '>', 0)
                                ->orderBy('expire_date', 'asc')
                                ->get();

            foreach ($lots as $lot) {
                if ($remaining <= 0) break;

                if ($lot->quantity >= $remaining) {
                    $lot->decrement('quantity', $remaining);
                    $remaining = 0;
                } else {
                    $remaining -= $lot->quantity;
                    $lot->update(['quantity' => 0]);
                }
            }

            // หักยอดรวมจากคลังหลัก
            $medicine->decrement('stock_master', $qtyToWithdraw);

            return redirect()->back()->with('success', 'ตัดสต็อกตามล็อต (FEFO) สำเร็จ');
        });
    }
    public function updateLotPrice(Request $request, $id)
{
    // ตรวจสอบค่าที่ส่งมา
    $request->validate([
        'price_per_unit' => 'required|numeric|min:0',
    ]);

    // อัปเดตข้อมูลลงฐานข้อมูล
    $lot = \App\Models\MedicineLot::findOrFail($id);
    $lot->update([
        'price_per_unit' => $request->price_per_unit
    ]);

    return response()->json(['success' => true]);
}

    
}