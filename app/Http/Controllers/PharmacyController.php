<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Medicine;
use App\Models\Staff;
use App\Models\DispensingRecord;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\MedicineLot;
use App\Models\Category;
use App\Models\MedicineReturn;
use App\Models\PatientVisitDraft;

class PharmacyController extends Controller
{
    /**
     * หน้าแรกของการจ่ายยา
     */
    public function index()
    {
       $medicines = Medicine::where('stock_master', '>', 0)->get();
    // ดึงรายชื่อเจ้าหน้าที่ที่ยังใช้งานอยู่
    $staffs = \App\Models\Staff::where('is_active', true)->get();
    // คิว Draft ที่ยังค้างอยู่ (รอทำต่อ/ยกเลิก) เรียงจากคนที่มาก่อน
    $drafts = PatientVisitDraft::where('status', 'pending')->oldest()->get();

    return view('pharmacy.index', compact('medicines', 'staffs', 'drafts'));
    }

    /**
     * บันทึก "ไว้ก่อน" (Draft) เมื่อคนไข้มาถึงแล้วแต่ยังไม่ได้จ่ายยาจริง
     * (เช่น ต้องรอปรึกษาหมอก่อน แล้วมีคนไข้คนอื่นมาต่อคิว) เพื่อเก็บสถิติเวลาที่มาใช้บริการ
     */
    public function storeDraft(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|string',
            'patient_name' => 'nullable|string',
            'form_data' => 'nullable|array',
        ]);

        try {
            $draft = PatientVisitDraft::create([
                'employee_id'  => $request->employee_id,
                'patient_name' => $request->patient_name ?: $request->employee_id,
                // เก็บ snapshot ของฟอร์มทั้งหมด (มาด้วยอาการอะไร, vital signs, checkbox, ตะกร้ายา ฯลฯ)
                // เพื่อให้กด "ทำต่อ" แล้วข้อมูลที่กรอกไว้กลับมาครบ ไม่ต้องพิมพ์ใหม่
                'form_data'    => $request->form_data,
                'status'       => 'pending',
            ]);

            return response()->json(['success' => true, 'draft' => $draft]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'บันทึก Draft ไม่สำเร็จ: ' . $e->getMessage()], 500);
        }
    }

    /**
     * ยกเลิกคิว Draft (ไม่จ่ายยาต่อ) แต่ยังเก็บแถวข้อมูลไว้เป็นสถิติเวลาที่มาใช้บริการ
     */
    public function cancelDraft($id)
    {
        try {
            $draft = PatientVisitDraft::findOrFail($id);
            $draft->update([
                'status'       => 'cancelled',
                'cancelled_at' => now(),
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'ยกเลิก Draft ไม่สำเร็จ: ' . $e->getMessage()], 500);
        }
    }

public function storeDispensing(Request $request)
    {
        // 1. Validation - รองรับฟิลด์แยกโมดูลใหม่
        $request->validate([
            'employee_id'    => 'required',
            'diagnosis'      => 'required|string',
            'issued_by'      => 'required',
            'symptom_groups' => 'nullable|string', // กลุ่มอาการ
            'accident_types' => 'nullable|string', // ประเภทอุบัติเหตุ
            'service_types'  => 'nullable|string'  // การบริการ
        ]);

        try {
            DB::beginTransaction();

            // 2. บันทึก/อัปเดตข้อมูลคนไข้ (เพิ่มฟิลด์ภาษาอังกฤษ รองรับเครื่องอ่านบัตร Smart Card)
            $patient = Patient::updateOrCreate(
                ['employee_id' => $request->employee_id],
                [
                    'prefix_th'       => $request->prefix_th,
                    'first_name'      => $request->first_name,
                    'last_name'       => $request->last_name,
                    'prefix_en'       => $request->prefix_en,
                    'name_en'         => $request->name_en,      // ✅ เพิ่มภาษาอังกฤษ (ชื่อ)
                    'surname_en'      => $request->surname_en,
                    'gender'          => $request->gender,   // ✅ เพิ่มภาษาอังกฤษ (นามสกุล)
                    'birth_date'      => $request->birth_date,
                    'department'      => $request->department,
                    'company'         => $request->company,
                    'drug_allergy'    => $request->drug_allergy,
                    'chronic_disease' => $request->chronic_disease,
                    'updated_at'      => now()
                ]
            );

            // 3. ตรวจสอบว่ามีการส่งรายการยามาหรือไม่
            $hasCartData = !empty($request->cart_data) && count($request->cart_data) > 0;

            if ($hasCartData) {
                // --- กรณีที่ 1: มีการจ่ายยา ---
                foreach ($request->cart_data as $item) {
                    $medicine = Medicine::lockForUpdate()->find($item['id']);
                    if (!$medicine) continue;

                    $qtyToWithdraw = (int)$item['qty'];

                    // ดึงล็อตยาแบบ FEFO
                    $lots = MedicineLot::where('medicine_id', $medicine->id)
                                        ->where('quantity', '>', 0)
                                        ->orderBy('expire_date', 'asc')
                                        ->lockForUpdate()
                                        ->get();

                    $remaining = $qtyToWithdraw;

                    foreach ($lots as $lot) {
                        if ($remaining <= 0) break;
                        $takeFromLot = min($lot->quantity, $remaining);

                        DispensingRecord::create([
                            'patient_id'        => $patient->id,
                            'medicine_id'       => $medicine->id,
                            'medicine_stock_id' => $lot->id,
                            'lot_number'        => $lot->lot_number,
                            'price_per_unit'    => $lot->price_per_unit,
                            'amount'            => $takeFromLot,
                            'diagnosis'         => $request->diagnosis,

                            // ข้อมูลฉลากยา (ตามที่กรอก/แก้ไขในตะกร้าก่อนบันทึก ใช้พิมพ์ฉลากทันที)
                            'usage_instruction' => $item['usage_instruction'] ?? null,
                            'indication'        => $item['indication'] ?? null,
                            'side_effect'       => $item['side_effect'] ?? null,
                            'usage_instruction_en' => $item['usage_instruction_en'] ?? null,
                            'indication_en'        => $item['indication_en'] ?? null,
                            'side_effect_en'       => $item['side_effect_en'] ?? null,

                            // บันทึกฟิลด์ที่แยกโมดูล
                            'symptom_groups'    => $request->symptom_groups,
                            'accident_types'    => $request->accident_types,
                            'service_types'     => $request->service_types,
                            
                            // แก้ไขจุดนี้: ส่งค่าไปยัง medical_systems เพื่อป้องกัน NOT NULL constraint failed
                            'medical_systems'   => $request->symptom_groups ?? '-', 

                            'from_location'     => 'stock_master',
                            'issued_by'         => $request->issued_by,
                            'department'        => $request->department,
                            'company'           => $request->company,
                            'temp'              => $request->v_temp,
                            'bp'                => $request->v_bp,
                            'pr'                => $request->v_pr,
                            'rr'                => $request->v_rr,
                            'o2_sat'            => $request->v_o2,
                            'illness_type'      => $request->illness_type,
                            'procedure'         => $request->procedure,
                            'disposition'       => $request->disposition,
                            'updated_at'        => now(),
                            'created_at'        => now(),
                        ]);

                        $lot->decrement('quantity', $takeFromLot);
                        $remaining -= $takeFromLot;
                    }

                    if ($remaining > 0) {
                        throw new \Exception("สต็อกยา {$medicine->name} ไม่เพียงพอ");
                    }
                    $medicine->decrement('stock_master', $qtyToWithdraw);
                }
            } else {
                // --- กรณีที่ 2: ไม่มีการจ่ายยา (บันทึกเฉพาะประวัติการตรวจ/บริการอื่นๆ) ---
                DispensingRecord::create([
                    'patient_id'        => $patient->id,
                    'medicine_id'       => null,
                    'medicine_stock_id' => 0,
                    'amount'            => 0,
                    'price_per_unit'    => 0,
                    'diagnosis'         => $request->diagnosis,
                    
                    // บันทึกฟิลด์ที่แยกโมดูล
                    'symptom_groups'    => $request->symptom_groups,
                    'accident_types'    => $request->accident_types,
                    'service_types'     => $request->service_types,

                    // แก้ไขจุดนี้: ส่งค่าไปยัง medical_systems เพื่อป้องกัน NOT NULL constraint failed
                    'medical_systems'   => $request->symptom_groups ?? '-',

                    'from_location'     => 'consultation',
                    'issued_by'         => $request->issued_by,
                    'department'        => $request->department,
                    'company'           => $request->company,
                    'temp'              => $request->v_temp,
                    'bp'                => $request->v_bp,
                    'pr'                => $request->v_pr,
                    'rr'                => $request->v_rr,
                    'o2_sat'            => $request->v_o2,
                    'illness_type'      => $request->illness_type,
                    'procedure'         => $request->procedure,
                    'disposition'       => $request->disposition,
                    'updated_at'        => now(),
                    'created_at'        => now(),
                ]);
            }

            // เคสนี้จ่ายยา/บันทึกจริงสำเร็จแล้ว ถ้ามีคิว Draft ค้างของคนไข้คนนี้อยู่ (มาก่อนสุด) ให้ปิดเป็น "เสร็จแล้ว"
            // เพื่อให้หายไปจากคิว Draft และเก็บสถิติเวลาที่มาใช้บริการ (created_at -> completed_at)
            $pendingDraft = PatientVisitDraft::where('employee_id', $request->employee_id)
                ->where('status', 'pending')
                ->oldest()
                ->first();
            if ($pendingDraft) {
                $pendingDraft->update(['status' => 'completed', 'completed_at' => now()]);
            }

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * API สำหรับดึงข้อมูลพนักงานและประวัติ (เรียกใช้ผ่าน JavaScript)
     */
    public function getPatientData($empId)
{
    try {
        // ค้นหาเฉพาะข้อมูลพนักงานตรงๆ ไม่ต้องโหลดประวัติพ่วง เพื่อตัดปัญหาเรื่อง Relationship พัง
        $patient = Patient::where('employee_id', $empId)->first();

        if ($patient) {
            // คืนค่าข้อมูลพนักงานกลับไปแบบ JSON ปกติ (Status 200)
            return response()->json($patient);
        }

        // หากไม่พบข้อมูล (กรณีคนไข้ใหม่) ให้ส่ง null กลับไปแบบปกติ (Status 200)
        // เพื่อให้ JavaScript รู้ว่าเป็นคนไข้ใหม่และอนุญาตให้กรอกข้อมูลใหม่ได้โดยไม่พัง
        return response()->json(null);

    } catch (\Exception $e) {
        // หากมีอะไรพังในระบบ ให้พ่นข้อความ Error เป็น JSON ออกมาแทนหน้าเว็บพัง
        return response()->json([
            'error' => true,
            'message' => $e->getMessage()
        ], 500);
    }
}


public function dashboard()
{
    $today = \Carbon\Carbon::now()->startOfDay();

    // ดึงล็อตยาที่ยังไม่หมดอายุและมีจำนวน > 0
    $expiryAlerts = \App\Models\MedicineLot::with('medicine')
        ->where('quantity', '>', 0)
        // ->where('expire_date', '>', $today) // แบบเดิม: ไม่โชว์ยาที่หมดวันนี้หรือหมดไปแล้ว
        ->where('expire_date', '<=', $today->copy()->addDays(180)) // โชว์ล่วงหน้า 6 เดือน
        ->get()
        ->groupBy('medicine_id')
        ->map(function ($lots) use ($today) {
            $nearestLot = $lots->sortBy('expire_date')->first();
            
            // คำนวณวันคงเหลือ (ถ้าติดลบแปลว่าหมดอายุแล้ว)
            $daysLeft = (int)$today->diffInDays($nearestLot->expire_date, false);

            return (object) [
                'medicine_id'    => $nearestLot->medicine_id,
                'name'           => $nearestLot->medicine->name,
                'unit_name'      => $nearestLot->medicine->unit_name,
                'total_quantity' => $lots->sum('quantity'),
                'expire_date'    => $nearestLot->expire_date,
                'days_left'      => $daysLeft, 
                'total_lots'     => $lots->count(),
                'lot_number'     => $nearestLot->lot_number,
                'all_lots'       => $lots->sortBy('expire_date')
            ];
        })
        ->values()
        ->sortBy('days_left');

    $minAlerts = \App\Models\Medicine::whereColumn('stock_master', '<=', 'min_stock')->get();
    $maxAlerts = \App\Models\Medicine::whereColumn('stock_master', '>=', 'max_stock')->get();

    return view('dashboard', compact('expiryAlerts', 'minAlerts', 'maxAlerts'));
}

public function showPatient($id)
{
    $patient = Patient::with([
        'dispensingRecords.medicine', 
        'medicineReturns.medicine'
    ])->findOrFail($id);

    // ดึงรายชื่อ Staff ทั้งหมดเพื่อส่งไปให้ Select Box ใน JS
    $staffs = \App\Models\Staff::orderBy('name', 'asc')->get(['id', 'name']);

    return view('patients.show', compact('patient', 'staffs'));
}

    public function patientList()
    {
        $patients = Patient::orderBy('updated_at', 'desc')->get();
        return view('patients.index', compact('patients'));
    }

    public function showImport()
    {
        return view('pharmacy.import', ['title' => 'นำเข้าข้อมูลยา (CSV)']);
    }

    public function importCSV(Request $request) 
    {
        $request->validate(['file' => 'required|mimes:csv,txt,xlsx']);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\MedicinesImport, $request->file('file'));
            $recentData = Medicine::latest('updated_at')->take(10)->get();
            return redirect()->route('pharmacy.import.view')->with([
                'success' => 'นำเข้าข้อมูลยาเรียบร้อยแล้ว!',
                'recent' => $recentData 
            ]);
        } catch (\Exception $e) {
            return redirect()->route('pharmacy.import.view')->withErrors(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }

    public function printAllBarcodes()
    {
        $medicines = Medicine::all();
        return view('medicines.print_all', compact('medicines'));
    }
    public function updatePatient(Request $request, $id)
{
    $request->validate([
        'employee_id' => 'required|string|max:50',
        'prefix_th'   => 'nullable|string|max:50',  
        'first_name'  => 'required|string|max:255',
        'last_name'   => 'nullable|string|max:255',
        'prefix_en'   => 'nullable|string|max:50',  
        'name_en'     => 'nullable|string|max:255', 
        'surname_en'  => 'nullable|string|max:255', 
        'gender'      => 'nullable|string|max:20',   // ✅ เพิ่ม Validation สำหรับเพศ
        'birth_date'  => 'nullable|date',
        'department'  => 'nullable|string|max:255', 
    ]);

    try {
        DB::beginTransaction();

        $patient = Patient::findOrFail($id);
        
        if ($patient->employee_id !== $request->employee_id) {
            $exists = Patient::where('employee_id', $request->employee_id)->where('id', '!=', $id)->exists();
            if ($exists) {
                return back()->withErrors(['error' => 'รหัสพนักงานนี้มีอยู่ในระบบแล้ว']);
            }
        }

        $patient->update([
            'employee_id'     => $request->employee_id,
            'prefix_th'       => $request->prefix_th,    
            'first_name'      => $request->first_name,
            'last_name'       => $request->last_name ?? '',
            'prefix_en'       => $request->prefix_en,    
            'name_en'         => $request->name_en,      
            'surname_en'      => $request->surname_en,   
            'gender'          => $request->gender,       // ✅ อัปเดตข้อมูลเพศ
            'birth_date'      => $request->birth_date,
            'department'      => $request->department,
            'company'         => $request->company, 
            'drug_allergy'    => $request->drug_allergy,
            'chronic_disease' => $request->chronic_disease,
        ]);

        DB::commit();
        return back()->with('success', 'อัปเดตข้อมูลสำเร็จ');

    } catch (\Exception $e) {
        DB::rollback();
        return back()->withErrors(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
}
/**
 * ลบคนไข้ (พร้อมประวัติการรับยา)
 */
public function destroyPatient($id)
{
    try {
        DB::beginTransaction();
        $patient = Patient::findOrFail($id);
        
        // ลบประวัติการจ่ายยาก่อน (ถ้า Database ไม่ได้ตั้ง On Delete Cascade)
        $patient->dispensingRecords()->delete();
        
        $patient->delete();
        
        DB::commit();
        return back()->with('success', 'ลบข้อมูลคนไข้และประวัติเรียบร้อยแล้ว');
    } catch (\Exception $e) {
        DB::rollback();
        return back()->withErrors(['error' => 'ไม่สามารถลบข้อมูลได้: ' . $e->getMessage()]);
    }
}
public function returnMedicine(Request $request, $recordId)
{
    $request->validate([
        'return_qty' => 'required|integer|min:1',
        'remark' => 'nullable|string',
        'staff_name' => 'required|string' 
    ]);

    try {
        DB::beginTransaction();

        // 1. ดึงข้อมูลรายการเบิก
        $record = DispensingRecord::findOrFail($recordId);
        $returnQty = (int)$request->return_qty;

        if ($returnQty > $record->amount) {
            return response()->json(['success' => false, 'message' => 'คืนเกินจำนวนที่เบิกไม่ได้'], 422);
        }

        // 2. หา Lot ยาที่จะคืนเข้า
        $targetLot = MedicineLot::find($record->medicine_stock_id);

        if (!$targetLot) {
            $targetLot = MedicineLot::where('medicine_id', $record->medicine_id)
                ->where('expire_date', '>=', now()->format('Y-m-d'))
                ->orderBy('expire_date', 'asc')
                ->first();
        }

        if ($targetLot) {
            // ใช้ฟิลด์ตามโครงสร้างจริงของคุณ (ถ้าใช้ quantity ให้ใช้ quantity)
            $targetLot->increment('quantity', $returnQty);
        }

        // 3. อัปเดต Master Stock และราคากลาง
        $medicine = Medicine::find($record->medicine_id);
        if ($medicine) {
            $medicine->increment('stock_master', $returnQty);
            if ($targetLot) {
                $medicine->update(['price_per_unit' => $targetLot->price_per_unit]);
            }
        }

        // --- ส่วนที่ 4: บันทึกประวัติลงในตาราง medicine_returns ---
        // ตรวจสอบให้แน่ใจว่าได้เพิ่ม patient_id ลงไปด้วยเพื่อให้ดึงประวัติได้แม่นยำ
        \App\Models\MedicineReturn::create([
            'dispensing_record_id' => $record->id,
            'patient_id'           => $record->patient_id, // เพิ่มเพื่อให้ผูกกับตัวคนไข้โดยตรง
            'medicine_id'          => $record->medicine_id,
            'amount'               => $returnQty,
            'reason'               => $request->remark ?? 'คืนยาจากหน้าประวัติ',
            'returned_by'          => $request->staff_name,
        ]);

        // 5. จัดการ Record การเบิก (แก้ไขใหม่: ห้ามลบ)
        // ลดจำนวนที่ถืออยู่ลงตามจริง แม้จะคืนครบจนเหลือ 0 ก็ให้เก็บแถวข้อมูลนี้ไว้
        // เพื่อให้ Foreign Key ในตาราง returns ยังอ้างอิงกลับมาได้
        $record->decrement('amount', $returnQty);
        
        // บันทึกหมายเหตุล่าสุดไว้ที่รายการหลักด้วย
        if ($request->remark) {
            $record->update(['return_remark' => $request->remark]);
        }

        DB::commit();
        return response()->json(['success' => true]);

    } catch (\Exception $e) {
        DB::rollback();
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

public function getPatientHistory($id)
{
    try {
        // ดึงข้อมูล Patient พร้อมประวัติการจ่ายยาและข้อมูลยา
        $patient = Patient::with(['dispensingRecords' => function($query) {
            $query->with('medicine')->latest(); 
        }])->find($id);

        if (!$patient) {
            return response()->json([
                'success' => false, 
                'message' => 'ไม่พบข้อมูลคนไข้'
            ], 404);
        }

        // ส่งกลับในรูปแบบที่ JS เข้าถึงได้ง่าย
        // หน้าบ้านจะได้รับ object ที่มี key คือ dispensing_records
        return response()->json([
            'success' => true,
            'patient_name' => $patient->first_name . ' ' . $patient->last_name,
            'dispensing_records' => $patient->dispensingRecords 
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
        ], 500);
    }
}
public function returnHistory()
{
    // ดึงประวัติการคืนยาทั้งหมด พร้อมข้อมูลยา และข้อมูลคนไข้ (ผ่าน record)
    $returns = MedicineReturn::with(['medicine', 'dispensingRecord.patient'])
                ->latest()
                ->paginate(20);

    return view('pharmacy.returns_history', compact('returns'));
}

    
}