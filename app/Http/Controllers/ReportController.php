<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medicine;
use App\Models\DispensingRecord;
use App\Models\MedicineStock;

class ReportController extends Controller {
    // หน้าดูลายละเอียดสต็อกแยกคลัง
    public function inventoryIndex() {
        $medicines = Medicine::with('lots')->get();
        return view('reports.inventory', compact('medicines'));
    }

    // หน้าดูประวัติการเบิกจ่ายรายเดือน
    public function dispensingIndex(Request $request) {
    // ดึง relation medicineLot เพิ่มเติม
    $query = DispensingRecord::with(['patient', 'medicine', 'medicineLot']);
    
    if ($request->start_month && $request->end_month) {
        $startDate = \Carbon\Carbon::parse($request->start_month)->startOfMonth();
        $endDate = \Carbon\Carbon::parse($request->end_month)->endOfMonth();
        $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    $records = $query->latest()->get();
    return view('reports.dispensing', compact('records'));
}

    // หน้าค้นหายาเพื่อพิมพ์บาร์โค้ด
    public function barcodeIndex() {
        $medicines = Medicine::all();
        return view('reports.barcode_list', compact('medicines'));
    }
    public function printBarcode($id) {
    $medicine = \App\Models\Medicine::findOrFail($id);
    return view('reports.print_barcode', compact('medicine'));
}
public function exportInventory() {
    // แก้ไข: เอา ->where('quantity', '>', 0) ออก เพื่อดึงทุกล็อต (แม้จำนวนจะเหลือ 0)
    // เพิ่มเติม: โหลดความสัมพันธ์ 'firstTransfer' มาร่วมด้วย เพื่อใช้ดึงวันที่รับเข้า
    $lots = \App\Models\MedicineLot::with(['medicine', 'firstTransfer'])
            ->orderBy('medicine_id')
            ->orderBy('expire_date', 'asc')
            ->get();

    $filename = "inventory_report_by_lots_" . date('Ymd_His') . ".csv";

    $callback = function() use($lots) {
        $file = fopen('php://output', 'w');
        fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM สำหรับ Excel อ่านภาษาไทยออกโดยไม่เป็นภาษาต่างดาว

        // หัวข้อคอลัมน์ใน CSV (เพิ่ม 'วันที่รับเข้า')
        fputcsv($file, [
            'บาร์โค้ด', 
            'ชื่อยา', 
            'เลขที่ล็อต', 
            'จำนวนคงเหลือในล็อต', 
            'หน่วย',
            'ราคา/หน่วย (บาท)', 
            'มูลค่ารวมล็อตนี้ (บาท)',
            'วันที่รับเข้า', // เพิ่มคอลัมน์ใหม่
            'วันหมดอายุ'
        ]);

        foreach ($lots as $lot) {
            $medicine = $lot->medicine;
            if (!$medicine) continue;

            $quantity = (int)$lot->quantity;
            $pricePerUnit = (float)($lot->price_per_unit ?? $medicine->price_per_unit ?? 0);
            $totalValue = $quantity * $pricePerUnit;

            // ดึงวันที่รับเข้าแบบเดียวกับที่เราทำในหน้า View (ถ้าไม่มีประวัติย้อนหลัง ให้ดึงวันที่สร้าง created_at แทน)
            if ($lot->firstTransfer && $lot->firstTransfer->receive_date) {
                $receiveDate = \Carbon\Carbon::parse($lot->firstTransfer->receive_date)->format('d/m/Y');
            } else {
                $receiveDate = $lot->created_at ? $lot->created_at->format('d/m/Y') : '-';
            }

            fputcsv($file, [
                $medicine->barcode,
                $medicine->name,
                $lot->lot_number,
                $quantity, // แสดงยอดคงเหลือ (รายการที่หมดเกลี้ยงจะขึ้นเลข 0 ตามต้องการ)
                $medicine->unit_name ?? 'ชิ้น',
                number_format($pricePerUnit, 2, '.', ''),
                number_format($totalValue, 2, '.', ''),
                $receiveDate, // แสดงข้อมูลวันที่รับเข้าที่นี่
                $lot->expire_date ? \Carbon\Carbon::parse($lot->expire_date)->format('d/m/Y') : '-'
            ]);
        }
        fclose($file);
    };

    return response()->stream($callback, 200, [
        "Content-type"        => "text/csv; charset=UTF-8",
        "Content-Disposition" => "attachment; filename=$filename",
    ]);
}

public function exportDispensing(Request $request) {
    while (ob_get_level()) { ob_end_clean(); }

    $startMonth = $request->start_month ?? date('Y-01');
    $endMonth = $request->end_month ?? date('Y-m');
    
    $startDate = \Carbon\Carbon::parse($startMonth)->startOfMonth();
    $endDate = \Carbon\Carbon::parse($endMonth)->endOfMonth();

    $records = DispensingRecord::with(['patient', 'medicine', 'medicineLot'])
        ->whereBetween('created_at', [$startDate, $endDate])
        ->where(function ($query) {
            $query->where('amount', '>', 0)
                  ->orWhere(function ($subQuery) {
                      $subQuery->where('amount', 0)
                               ->whereDoesntHave('medicineReturns'); 
                  });
        })
        ->orderBy('created_at', 'desc')
        ->get();

    // 🟢 จัดกลุ่มข้อมูลตาม วัน-เวลา (ตัดหน่วยวินาทีออก)
    $groupedRecords = $records->groupBy(function($item) {
        return \Carbon\Carbon::parse($item->created_at)->format('Y-m-d H:i');
    });

    $filename = "detailed_dispensing_report_{$startMonth}_to_{$endMonth}.csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $handle = fopen('php://output', 'w');
    fputs($handle, "\xEF\xBB\xBF"); // รองรับภาษาไทยใน Excel

    // หัวคอลัมน์รายงาน
    fputcsv($handle, [
        'วันที่-เวลา', 'รหัสพนักงาน', 'ชื่อ-นามสกุล', 
        'ว/ด/ปี เกิด', 'อายุ', 
        'บริษัท', 'แผนก', 
        'กลุ่มอาการ/ระบบอวัยวะ', 'ประเภทอุบัติเหตุ', 'การบริการ',
        'ประเภทการป่วย', 'อาการ/การวินิจฉัย', 'การพยาบาล',
        'Temp', 'BP', 'PR', 'RR', 'O2 Sat',
        'ชื่อยาที่จ่าย', 'ล็อตยา', 'จำนวน', 'ราคา/หน่วย', 'ราคารวม', 
        'Disposition', 'ผู้จ่ายยา/ผู้บันทึก'
    ]);

    // 🟢 วนลูปผ่านกลุ่มเวลาแทนการซัดแถวดิบเรียงกัน
    foreach ($groupedRecords as $timeKey => $group) {
        foreach ($group as $index => $r) {
            
            // ตรวจสอบว่าเป็นรายการแรกของกลุ่มเวลานี้หรือไม่
            $isFirstRow = ($index === 0);

            // คำนวณอายุและ format วันเกิดเฉพาะแถวแรก (เพื่อความประหยัดทรัพยากร)
            $birthDate = '-';
            $age = '-';
            if ($isFirstRow && $r->patient && $r->patient->birth_date) {
                $birthDate = \Carbon\Carbon::parse($r->patient->birth_date)->format('d/m/Y');
                $age = \Carbon\Carbon::parse($r->patient->birth_date)->age;
            }

            fputcsv($handle, [
                // 🔴 ฝั่งซ้าย (ข้อมูลคนไข้/เคส): ถ้าเป็นแถวแรกในรอบเวลาให้แสดงค่าปกติ ถ้าเป็นตัวที่ 2 ขึ้นไปให้ส่งค่าว่าง ""
                $isFirstRow ? \Carbon\Carbon::parse($r->created_at)->format('d/m/Y H:i') : '',
                $isFirstRow ? ($r->patient->employee_id ?? '-') : '',
                $isFirstRow ? (($r->patient->first_name ?? '') . ' ' . ($r->patient->last_name ?? '')) : '',
                $isFirstRow ? $birthDate : '', 
                $isFirstRow ? $age : '',       
                $isFirstRow ? ($r->company ?? '-') : '',
                $isFirstRow ? ($r->department ?? '-') : '',
                $isFirstRow ? ($r->symptom_groups ?? '-') : '',
                $isFirstRow ? ($r->accident_types ?? '-') : '',
                $isFirstRow ? ($r->service_types ?? '-') : '',
                $isFirstRow ? ($r->illness_type ?? '-') : '',
                $isFirstRow ? ($r->diagnosis ?? '-') : '',
                $isFirstRow ? ($r->procedure ?? '-') : '',
                $isFirstRow ? ($r->temp ?? '-') : '',
                $isFirstRow ? ($r->bp ?? '-') : '',
                $isFirstRow ? ($r->pr ?? '-') : '',
                $isFirstRow ? ($r->rr ?? '-') : '',
                $isFirstRow ? ($r->o2_sat ?? '-') : '',

                // 🔵 ฝั่งขวา (ข้อมูลตัวยาและการจ่าย): บังคับแสดงผล "ทุกแถว" เพื่อให้ยาแยกบรรทัดกัน
                $r->medicine->name ?? 'ไม่ได้รับยา',
                $r->medicineLot->lot_number ?? ($r->lot_number ?? '-'), 
                $r->amount,
                number_format($r->price_per_unit, 2, '.', ''),
                number_format($r->amount * $r->price_per_unit, 2, '.', ''),
                
                // ฝั่งท้ายตาราง (ส่งผลการรักษาและคนคีย์เป็นค่าว่างในแถวถัดไปเช่นกัน)
                $isFirstRow ? ($r->disposition ?? '-') : '',
                $isFirstRow ? ($r->issued_by ?? '-') : ''
            ]);
        }
    }
    
    fclose($handle);
    exit;
}
}