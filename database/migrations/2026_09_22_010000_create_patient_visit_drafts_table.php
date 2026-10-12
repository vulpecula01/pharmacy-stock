<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * สร้างตาราง "คิว Draft" สำหรับบันทึกไว้ก่อนตอนที่คนไข้มาถึงแต่ยังไม่ได้จ่ายยาจริง
     * (เช่น รอปรึกษาหมอก่อน แล้วมีคนไข้คนอื่นมาต่อคิว) เพื่อเก็บสถิติเวลาที่มาใช้บริการ
     * และให้เภสัชกร/พยาบาลกลับมาทำเคสต่อ หรือยกเลิกได้ภายหลัง
     */
    public function up(): void
    {
        Schema::create('patient_visit_drafts', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->string('patient_name')->nullable()
                ->comment('ชื่อคนไข้ ณ เวลาที่บันทึกไว้ก่อน ใช้แสดงในคิว');
            // pending = รออยู่ในคิว, completed = จ่ายยา/บันทึกจริงเสร็จแล้ว, cancelled = ยกเลิกไปเลย
            $table->string('status')->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_visit_drafts');
    }
};
