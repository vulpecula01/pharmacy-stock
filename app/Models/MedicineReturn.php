<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicineReturn extends Model
{
    protected $fillable = [
    'dispensing_record_id', 
    'medicine_id', 
    'amount', 
    'reason', 
    'returned_by'
];
    public function dispensingRecord() {
        return $this->belongsTo(DispensingRecord::class);
    }
    public function medicineReturns()
    {
        // เชื่อมผ่านตาราง dispensing_records เพื่อดูว่าคนไข้คนนี้เคยคืนยาอะไรบ้าง
        // หรือถ้าในตาราง medicine_returns ไม่มี patient_id 
        // เราจะใช้ HasManyThrough หรือดึงผ่าน DispensingRecord ครับ
        
        // แต่ถ้าคุณออกแบบให้ medicine_returns เก็บแค่ dispensing_record_id 
        // วิธีที่ง่ายที่สุดคือดึงผ่าน relationship ของ DispensingRecord
        return $this->hasManyThrough(
            MedicineReturn::class, 
            DispensingRecord::class,
            'patient_id',           // Foreign key on DispensingRecord table...
            'dispensing_record_id', // Foreign key on MedicineReturn table...
            'id',                   // Local key on Patient table...
            'id'                    // Local key on DispensingRecord table...
        );
    }
    public function medicine()
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }

    /**
     * (เพิ่มเติม) เชื่อมโยงกลับไปยังรายการเบิกเดิม
     */
    
}