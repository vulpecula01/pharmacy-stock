<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DispensingRecord extends Model

{
    
    // กำหนดฟิลด์ที่อนุญาตให้บันทึก
    protected $fillable = [
    'patient_id',
    'medicine_id', 
    'medicine_stock_id',
    'amount', 
    'diagnosis', 
    'medical_systems',
    'symptom_groups',
    'accident_types',
    'service_types',
    'from_location',
    'issued_by',
    'department', 
    'company',
    'temp', 
    'bp', 
    'pr', 
    'rr', 
    'o2_sat', 
    'illness_type', 
    'procedure',
    'disposition',
    'price_per_unit',
    'usage_instruction', // วิธีใช้ที่พิมพ์จริงบนฉลากยา
    'indication',        // สรรพคุณที่พิมพ์จริงบนฉลากยา
    'side_effect',       // ผลข้างเคียงที่พิมพ์จริงบนฉลากยา
    'usage_instruction_en', // วิธีใช้ (English) ที่พิมพ์จริงบนฉลากยา
    'indication_en',        // สรรพคุณ (English) ที่พิมพ์จริงบนฉลากยา
    'side_effect_en',       // ผลข้างเคียง (English) ที่พิมพ์จริงบนฉลากยา

];
    // เชื่อมกลับไปหาข้อมูลยา (ตาราง Medicines)
    public function medicine()
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    // เชื่อมกลับไปหาคนไข้
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    public function medicineStock()
{
    // เชื่อมไปยังตาราง MedicineStock โดยใช้คอลัมน์ medicine_stock_id
    return $this->belongsTo(MedicineStock::class, 'medicine_stock_id');
}
public function staff() {
    return $this->belongsTo(Staff::class, 'staff_id');
}
public function medicineLot()
{
    // เชื่อมผ่าน medicine_stock_id ไปยังไอดีของตาราง MedicineLot
    return $this->belongsTo(MedicineLot::class, 'medicine_stock_id');
}
public function medicineReturns()
{
    return $this->hasMany(MedicineReturn::class, 'dispensing_record_id');
}
}