<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * คิว "บันทึกไว้ก่อน (Draft)" — ใช้ตอนคนไข้มาถึงแล้วแต่ยังไม่ได้จ่ายยาจริง
 * (เช่น รอปรึกษาหมอก่อน) เพื่อเก็บสถิติเวลาที่มาใช้บริการ และให้กลับมาทำเคสต่อ/ยกเลิกได้ภายหลัง
 */
class PatientVisitDraft extends Model
{
    protected $fillable = [
        'employee_id',
        'patient_name',
        'form_data',
        'status',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'form_data'    => 'array',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];
}
