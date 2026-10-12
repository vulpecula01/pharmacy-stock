<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medicine extends Model
{
    use HasFactory;
    use SoftDeletes; // เพิ่มบรรทัดนี้
    /**
     * รายชื่อคอลัมน์ที่อนุญาตให้บันทึกข้อมูลแบบ Mass Assignment
     */
    protected $fillable = [
        'barcode',          // บาร์โค้ด
        'name',             // ชื่อยา
        'supplier_name',    // ผู้จำหน่าย (เดิม)
        'company_name',     // บริษัทที่รับเข้า (ที่เพิ่มใหม่ เช่น AAA)
        'expire_date',      // วันหมดอายุของยา
        'stock_master',     // ยอดคงเหลือคลังใหญ่
        'stock_a',          // ยอดคงเหลือคลัง A
        'stock_b',          // ยอดคงเหลือคลัง B
        'stock_c',          // ยอดคงเหลือคลัง C
    ];

    /**
     * กำหนดการแปลงประเภทข้อมูล (Casts)
     * เพื่อให้ Laravel จัดการวันที่และตัวเลขได้อย่างถูกต้อง
     */
    protected $casts = [
        'expire_date' => 'date',
        'stock_master' => 'integer',
        'stock_a' => 'integer',
        'stock_b' => 'integer',
        'stock_c' => 'integer',
    ];

    /**
     * ความสัมพันธ์: ยาหนึ่งตัว สามารถมีประวัติการเบิกจ่ายได้หลายครั้ง
     */
    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class);
    }
}