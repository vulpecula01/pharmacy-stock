<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medicine;
use App\Models\Transfer;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransferController extends Controller
{
    /**
     * หน้าแรก: แสดงยอดคงเหลือและระบบแจ้งเตือน Alerts
     */
   public function index()
{
    // ดึงข้อมูลยาพร้อมประวัติการทำรายการล่าสุด (Eager Loading)
    $medicines = Medicine::with('latestTransfer')->get();
    $alerts = [];

    foreach ($medicines as $med) {
        // คำนวณสต็อกรวมทุกคลัง
        $totalStock = (int)$med->stock_master + (int)$med->stock_a + (int)$med->stock_b + (int)$med->stock_c;
        $today = \Carbon\Carbon::today();
        
        // ตรวจสอบวันหมดอายุ
        $expiryDate = $med->expire_date ? \Carbon\Carbon::parse($med->expire_date) : null;

        if ($expiryDate) {
            $daysDiff = $today->diffInDays($expiryDate, false);
            if ($daysDiff < 0) {
                $alerts[] = ['type' => 'danger', 'icon' => 'fa-skull-crossbones', 'name' => $med->name, 'msg' => 'หมดอายุแล้ว!'];
            } elseif ($daysDiff <= 30) {
                $alerts[] = ['type' => 'warning', 'icon' => 'fa-hourglass-half', 'name' => $med->name, 'msg' => "จะหมดอายุภายใน $daysDiff วัน"];
            }
        }

        // แจ้งเตือนเกณฑ์ Min/Max
        if ($totalStock <= $med->min_stock) {
            $alerts[] = ['type' => 'error', 'icon' => 'fa-arrow-down', 'name' => $med->name, 'msg' => "ยาใกล้หมด (เหลือรวม: $totalStock / Min: $med->min_stock)"];
        }

        if ($med->max_stock > 0 && $totalStock >= $med->max_stock) {
            $alerts[] = ['type' => 'info', 'icon' => 'fa-arrow-up', 'name' => $med->name, 'msg' => "ยาเกินเกณฑ์ Max (มีรวม: $totalStock / Max: $med->max_stock)"];
        }
    }
    
    // ดึงประวัติการทำรายการ 10 รายการล่าสุดสำหรับส่วนอื่นๆ ในหน้า Dashboard
    $recentTransfers = Transfer::with('medicine')->latest()->take(10)->get();

    return view('transfers.index', compact('medicines', 'recentTransfers', 'alerts'));
}

    /**
     * หน้าฟอร์มเบิกยา (Create)
     */
    public function create()
    {
        // ดึงยาที่มีของในคลังใหญ่มาแสดง
        $medicines = Medicine::where('stock_master', '>', 0)->get();
        return view('transfers.create', compact('medicines'));
    }

    /**
     * บันทึกการเบิกยา (Store)
     */
    public function store(Request $request)
{
    $request->validate([
        'medicine_id' => 'required|exists:medicines,id',
        'to_location' => 'required|in:stock_a,stock_b,stock_c',
        'quantity' => 'required|integer|min:1',
        'unit_name' => 'required'
    ]);

    try {
        DB::transaction(function () use ($request) {
            $medicine = Medicine::findOrFail($request->medicine_id);

            // ตรวจสอบสต็อกคลังใหญ่ก่อนเบิก
            if ($medicine->stock_master < $request->quantity) {
                throw new \Exception('จำนวนยาในคลังใหญ่ไม่เพียงพอสำหรับการเบิก');
            }

            // [1] เก็บยอดยกมา (ยอดเดิมก่อนเปลี่ยนแปลง)
            $prevStockFrom = $medicine->stock_master;              // ยอดยกมาคลังใหญ่
            $prevStockTo   = $medicine->{$request->to_location};   // ยอดยกมาคลังปลายทาง (A, B หรือ C)

            // [2] อัปเดตสต็อกในตาราง medicines
            $medicine->decrement('stock_master', $request->quantity);
            $medicine->increment($request->to_location, $request->quantity);
            
            // [3] บันทึกประวัติลงตาราง transfers พร้อมยอดยกมา
            Transfer::create([
                'medicine_id'     => $request->medicine_id,
                'from_location'   => 'stock_master',
                'to_location'     => $request->to_location,
                'prev_stock_from' => $prevStockFrom, // บันทึกยอดยกมาต้นทาง
                'prev_stock_to'   => $prevStockTo,   // บันทึกยอดยกมาปลายทาง
                'quantity'        => $request->quantity,
                'expire_date'     => $medicine->expire_date,
                'unit_name' => $request->unit_name,
            ]);
        });

        return redirect()->route('transfers.index')->with('success', 'เบิกจ่ายยาสำเร็จแล้ว');
    } catch (\Exception $e) {
        return back()->with('error', $e->getMessage());
    }
}

    public function edit($id)
{
    $medicine = Medicine::with('lots')->findOrFail($id); 
    $units = \App\Models\Unit::orderBy('name', 'asc')->get(); 
    
    // เพิ่ม: ดึงหมวดหมู่ทั้งหมดที่เปิดใช้งาน (is_active)
    $categories = \App\Models\Category::where('is_active', true)->orderBy('name', 'asc')->get();

    // เพิ่ม categories เข้าไปใน compact
    return view('medicines.edit', compact('medicine', 'units', 'categories'));
}

    public function update(Request $request, $id)
{
    $request->validate([
        'name'               => 'required|string|max:255',
        'barcode'            => 'required|string|unique:medicines,barcode,' . $id,
        'category'           => 'required|string', // เพิ่ม: ตรวจสอบหมวดหมู่
        'unit_name'          => 'required|string',
        'company_name'       => 'required|string',
        'min_stock'          => 'nullable|integer|min:0',
        'max_stock'          => 'nullable|integer|min:0',
        'usage_instruction'  => 'nullable|string',
        'indication'         => 'nullable|string',
        'side_effect'        => 'nullable|string',
        'usage_instruction_en' => 'nullable|string',
        'indication_en'        => 'nullable|string',
        'side_effect_en'       => 'nullable|string',
    ]);

    try {
        $medicine = Medicine::findOrFail($id);

        $medicine->update([
            'name'               => $request->name,
            'barcode'            => $request->barcode,
            'category'           => $request->category, // เพิ่ม: อัปเดตหมวดหมู่
            'unit_name'          => $request->unit_name,
            'company_name'       => $request->company_name,
            'min_stock'          => $request->min_stock,
            'max_stock'          => $request->max_stock,
            'usage_instruction'  => $request->usage_instruction,
            'indication'         => $request->indication,
            'side_effect'        => $request->side_effect,
            'usage_instruction_en' => $request->usage_instruction_en,
            'indication_en'        => $request->indication_en,
            'side_effect_en'       => $request->side_effect_en,
        ]);

        return redirect()->route('medicines.edit', $id)
                         ->with('success', 'อัปเดตข้อมูลยา ' . $medicine->name . ' เรียบร้อยแล้ว');

    } catch (\Exception $e) {
        return back()->withErrors(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
}

   public function destroy($id)
{
    // ค้นหายาตัวนี้ก่อน
    $medicine = Medicine::findOrFail($id);
    $name = $medicine->name;

    try {
        // --- ส่วนที่เพิ่มเพื่อลบให้ขาด ---
        
        // 1. ปิดการเช็คความสัมพันธ์ชั่วคราว (สำคัญมากสำหรับ SQLite)
        \DB::statement('PRAGMA foreign_keys = OFF');

        // 2. ลบข้อมูลลูกในตาราง medicine_lots ออกก่อนแบบ Hard Delete
        \DB::table('medicine_lots')->where('medicine_id', $id)->delete();

        // 3. ลบตัวยาหลัก
        $medicine->delete();

        // 4. เปิดการเช็คความสัมพันธ์กลับมา
        \DB::statement('PRAGMA foreign_keys = ON');

        // --- จบส่วนการลบ ---

        return redirect()->back()->with('success', "ลบรายการยา \"$name\" เรียบร้อยแล้ว");

    } catch (\Exception $e) {
        // ถ้าเกิดข้อผิดพลาด ให้เปิดการเช็คกลับคืนและแจ้ง Error
        \DB::statement('PRAGMA foreign_keys = ON');
        
        // บรรทัดนี้จะช่วยให้เราเห็นว่ามันลบไม่ได้เพราะอะไร
        return redirect()->back()->with('error', "ลบไม่ได้เพราะ: " . $e->getMessage());
    }
}

    public function clearStock($id)
    {
        $medicine = Medicine::findOrFail($id);
        $medicine->update(['stock_master' => 0, 'stock_a' => 0, 'stock_b' => 0, 'stock_c' => 0]);
        return back()->with('success', 'ล้างสต็อกเรียบร้อยแล้ว');
    }
    public function updateLot(Request $request, $id)
{
    // 1. เพิ่มกฎ Validation รองรับวันที่รับเข้า (receive_date)
    $request->validate([
        'lot_number'   => 'required|string',
        'quantity'     => 'required|numeric|min:0',
        'expire_date'  => 'required|date',
        'receive_date' => 'required|date', // 💡 เพิ่มบรรทัดนี้เพื่อเช็คความถูกต้องของวันที่รับเข้า
    ]);

    try {
        // 2. ดึงข้อมูล Lot ที่ต้องการแก้ไข
        $lot = \App\Models\MedicineLot::findOrFail($id);
        $medicineId = $lot->medicine_id; 

        // 3. อัปเดตข้อมูล Lot (เพิ่มการบันทึก receive_date ลงไปด้วย)
        $lot->update([
            'lot_number'   => $request->lot_number,
            'quantity'     => $request->quantity,
            'expire_date'  => $request->expire_date,
            'receive_date' => $request->receive_date, // 💡 อัปเดตวันที่รับเข้าให้ล็อกตามฐานข้อมูลจริง ไม่ให้ดีดกลับ
        ]);

        // 4. คำนวณสต็อกรวมใหม่ (Recalculate stock_master)
        $medicine = \App\Models\Medicine::find($medicineId);
        if ($medicine) {
            // รวมจำนวนจากทุก Lot ที่เป็นของยานี้
            $totalStock = \App\Models\MedicineLot::where('medicine_id', $medicineId)->sum('quantity');
            
            // อัปเดตยอดรวมกลับไปที่ตารางยาหลัก
            $medicine->update(['stock_master' => $totalStock]);
        }

        return back()->with('success', 'แก้ไขข้อมูลล็อตเรียบร้อยและอัปเดตสต็อกรวมแล้ว');

    } catch (\Exception $e) {
        return back()->withErrors(['error' => 'ไม่สามารถแก้ไขได้: ' . $e->getMessage()]);
    }
}

/**
 * ฟังก์ชัน Dashboard สำหรับแสดงสถิติและแจ้งเตือนวันหมดอายุ (แยกจาก index)
 */
public function dashboard()
{
    $today = \Carbon\Carbon::today();
    $thresholdDays = 180; // เกณฑ์แจ้งเตือน 6 เดือน

    // 1. ดึงล็อตยาที่มีของอยู่ พร้อมข้อมูลยาหลัก
    $activeLots = \App\Models\MedicineLot::with('medicine')
        ->where('quantity', '>', 0)
        ->whereNotNull('expire_date')
        ->orderBy('expire_date', 'asc')
        ->get();

    // 2. กรองและคำนวณสถานะล็อตยาที่ใกล้หมดอายุ
    $expireList = $activeLots->filter(function($lot) use ($today, $thresholdDays) {
        $expiryDate = \Carbon\Carbon::parse($lot->expire_date);
        return $today->diffInDays($expiryDate, false) <= $thresholdDays;
    })->map(function($lot) use ($today) {
        $expiryDate = \Carbon\Carbon::parse($lot->expire_date);
        $daysDiff = (int)$today->diffInDays($expiryDate, false);

        // กำหนดสถานะสีและข้อความ
        $type = ($daysDiff <= 30) ? 'danger' : 'warning';
        
        if ($daysDiff < 0) {
            $msg = "หมดอายุแล้ว!";
        } elseif ($daysDiff == 0) {
            $msg = "หมดอายุวันนี้!";
            $type = 'danger';
        } else {
            $msg = "อีก $daysDiff วัน";
        }

        return [
            'id'       => $lot->id,
            'name'     => $lot->medicine->name ?? 'ไม่ระบุชื่อ',
            'lot_no'   => $lot->lot_number ?? '-',
            'quantity' => $lot->quantity,
            'unit'     => $lot->medicine->unit_name ?? 'หน่วย',
            'date'     => $expiryDate->format('d/m/Y'),
            'msg'      => $msg,
            'type'     => $type
        ];
    });

    // 3. ดึงข้อมูลสต็อก ต่ำ/เกิน เกณฑ์ (Min/Max)
    $minList = \App\Models\Medicine::whereRaw('stock_master <= min_stock')->where('min_stock', '>', 0)->get();
    $maxList = \App\Models\Medicine::whereRaw('stock_master >= max_stock')->where('max_stock', '>', 0)->get();

    return view('pharmacy.dashboard', [
        'activeLots'       => $activeLots,
        'expireList'       => $expireList,
        'nearExpiredCount' => $expireList->count(),
        'nearExpiryCount'  => $expireList->count(),
        'minList'          => $minList,
        'minStockCount'    => $minList->count(),
        'maxList'          => $maxList,
        'maxStockCount'    => $maxList->count(),
        'patientCount'     => \App\Models\Patient::count(),
    ]);
}
public function updateLotPrice(Request $request, $id)
{
    // 1. ตรวจสอบค่าที่ส่งมาจาก AJAX
    $request->validate([
        'price_per_unit' => 'required|numeric|min:0',
    ]);

    try {
        // 2. ดึงข้อมูลล็อตยา
        $lot = \App\Models\MedicineLot::findOrFail($id);
        
        // 3. อัปเดตราคาในล็อตนั้น
        $lot->update([
            'price_per_unit' => $request->price_per_unit
        ]);

        // 4. อัปเดตราคากลางในตารางยาหลักด้วย (ถ้าต้องการให้ Dashboard เปลี่ยนตาม)
        $medicine = Medicine::find($lot->medicine_id);
        if ($medicine) {
            $medicine->update([
                'price_per_unit' => $request->price_per_unit
            ]);
        }

        return response()->json(['success' => true]);

    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}
}