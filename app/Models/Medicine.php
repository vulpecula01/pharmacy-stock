<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medicine extends Model
{
    use HasFactory;
    
    /**
     * รายชื่อคอลัมน์ที่อนุญาตให้บันทึกข้อมูลแบบ Mass Assignment
     */
    protected $fillable = [
        'barcode',          // บาร์โค้ด
        'name',             // ชื่อยา
        'supplier_name',    // ผู้จำหน่าย (เดิม)
        'company_name',     // บริษัทที่รับเข้า
        'stock_master',     // ยอดคงเหลือคลังใหญ่
        'stock_a',          // ยอดคงเหลือคลัง A
        'stock_b',          // ยอดคงเหลือคลัง B
        'stock_c', 
        'min_stock', 
        'max_stock', 
        'unit_name',
        'category',
        'price_per_unit',   // ตรวจสอบให้มั่นใจว่าสะกดแบบนี้ และมีเครื่องหมาย ' ปิดท้าย
        'usage_instruction', // วิธีใช้/ขนาดรับประทาน (ค่าเริ่มต้นสำหรับพิมพ์ฉลากยา)
        'indication',        // สรรพคุณ (ค่าเริ่มต้นสำหรับพิมพ์ฉลากยา)
        'side_effect',       // ผลข้างเคียงสำคัญ (ค่าเริ่มต้นสำหรับพิมพ์ฉลากยา)
        'usage_instruction_en', // วิธีใช้ (English)
        'indication_en',        // สรรพคุณ (English)
        'side_effect_en',       // ผลข้างเคียงสำคัญ (English)
    ];

    /**
     * กำหนดการแปลงประเภทข้อมูล (Casts)
     * เพื่อให้ Laravel จัดการวันที่และตัวเลขได้อย่างถูกต้อง
     */
   

    /**
     * ความสัมพันธ์: ยาหนึ่งตัว สามารถมีประวัติการเบิกจ่ายได้หลายครั้ง
     */
    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class);
    }
    public function lastReceive()
{
    return $this->hasOne(Receive::class)->latestOfMany();
}
public function latestTransfer()
{
    return $this->hasOne(Transfer::class)->latestOfMany();
}
public function getTotalStockAttribute()
{
    // นำคลังหลัก + คลังจุดจ่าย A (บวกคลังอื่นเพิ่มได้ถ้ามี)
    // ใช้ (int) เพื่อป้องกันกรณีค่าใน DB เป็น NULL แล้วบวกกันไม่ได้
    return (int)$this->stock_master + (int)$this->stock_a;
}
public function lots() {
        return $this->hasMany(MedicineLot::class, 'medicine_id');
    }
public function getTotalStockFromLots()
    {
        return $this->lots()->sum('quantity');
    }  
    public function predictions()
    {
        // ยา 1 รายการ สามารถมีรายการที่ AI ทำนายอนาคต (รายวัน) ได้หลายรายการ
        return $this->hasMany(StockPrediction::class, 'medicine_id');
    }  
    
}