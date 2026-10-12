<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transfer extends Model
{
    use HasFactory;

    /**
     * ฟิลด์ที่อนุญาตให้บันทึกข้อมูล (Security: ป้องกัน Mass Assignment Vulnerability)
     */
  protected $fillable = ['medicine_id', 'from_location', 'to_location', 'quantity', 'expire_date'];

    /**
     * การตั้งค่าประเภทข้อมูล (Casting)
     */
    protected $casts = [
        'quantity' => 'integer',
        'medicine_id' => 'integer',
    ];

    /**
     * ความสัมพันธ์: การเบิกจ่าย 1 รายการ ต้องเป็นของ ยา 1 ชนิด
     * (ทำให้เราเรียกใช้ $transfer->medicine->name ได้)
     */
    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
}