<?php  
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model {
   protected $fillable = ['employee_id', 'first_name', 'last_name', 'drug_allergy', 'chronic_disease','department','company','birth_date','prefix_th','prefix_en','name_en','surname_en','gender'];

    public function dispensingRecords() {
    return $this->hasMany(DispensingRecord::class);
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
}
