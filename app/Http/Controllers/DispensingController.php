<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DispensingController extends Controller
{
    public function dispense(Request $request) {
    // 1. หาหรือสร้างคนไข้ใหม่
    $patient = Patient::firstOrCreate(
        ['employee_id' => $request->employee_id],
        [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'congenital_disease' => $request->congenital_disease,
            'drug_allergy' => $request->drug_allergy
        ]
    );

    // 2. ตัดสต็อกยาจากคลังที่ระบุ (A, B หรือ C)
    $stock = MedicineStock::where('medicine_name', $request->medicine_name)
                          ->where('location', $request->location)
                          ->first();

    if ($stock && $stock->quantity >= $request->amount) {
        $stock->decrement('quantity', $request->amount);

        // 3. บันทึกประวัติ
        DispensingRecord::create([
            'patient_id' => $patient->id,
            'medicine_stock_id' => $stock->id,
            'amount' => $request->amount,
            'from_location' => $request->location
        ]);
        
        return back()->with('success', 'จ่ายยาเรียบร้อย');
    }

    return back()->with('error', 'ยาในคลังไม่พอ');
}

// ฟังก์ชันดึงประวัติคนไข้
public function getHistory($employee_id) {
    $patient = Patient::where('employee_id', $employee_id)->with('dispensingRecords.medicineStock')->first();
    return response()->json($patient);
}
}
