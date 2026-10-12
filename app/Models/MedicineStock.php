<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MedicineStock extends Model {
    protected $fillable = ['medicine_name', 'location', 'quantity', 'medicine_id'];

    // เพิ่มฟังก์ชันนี้เพื่อเชื่อมไปหาตารางยาหลัก (ตารางที่มีบาร์โค้ด)
    public function medicine()
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }
}