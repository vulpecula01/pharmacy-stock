<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
// อย่าลืม import Medicine model ถ้าอยู่คนละ namespace
use App\Models\Medicine; 

class MedicineLot extends Model
{
    protected $table = 'medicine_lots';

    protected $fillable = ['medicine_id', 'lot_number', 'quantity', 'expire_date','price_per_unit','receive_date'];

    /**
     * ✅ เพิ่ม Relationship ตรงนี้ครับ
     * เพื่อให้คำสั่ง with('medicine') ใน Controller ทำงานได้
     */
    public function medicine() {
        return $this->belongsTo(Medicine::class, 'medicine_id', 'id');
    }

    /**
     * Mutator สำหรับจัดการ Format วันที่ (ที่เราคุยกันก่อนหน้า)
     */
    public function setExpireDateAttribute($value)
    {
        if ($value) {
            try {
                $this->attributes['expire_date'] = Carbon::parse($value)->format('Y-m-d');
            } catch (\Exception $e) {
                $this->attributes['expire_date'] = $value;
            }
        }
    }
    public function firstTransfer()
    {
        return $this->hasOne(Transfer::class, 'medicine_id', 'medicine_id')
                    ->where('from_location', 'stock_master') // ครองเฉพาะขาที่รับเข้าคลังใหญ่
                    ->where('expire_date', $this->expire_date)
                    ->oldest(); // เอาตัวแรกสุดที่บันทึกเข้ามา
    }
}