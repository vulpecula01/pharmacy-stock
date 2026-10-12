<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * เพิ่มฟิลด์เก็บ snapshot ของฟอร์ม (มาด้วยอาการอะไร, vital signs, checkbox ต่างๆ, ตะกร้ายา ฯลฯ)
     * ตอนกด "บันทึกไว้ก่อน (Draft)" เพื่อให้กด "ทำต่อ" แล้วข้อมูลที่กรอกไว้กลับมาครบ ไม่ต้องพิมพ์ใหม่
     */
    public function up(): void
    {
        Schema::table('patient_visit_drafts', function (Blueprint $table) {
            $table->text('form_data')->nullable()->after('patient_name')
                ->comment('JSON snapshot ของฟอร์ม ณ ตอนกด Save Draft (diagnosis, vitals, checkboxes, cart ฯลฯ)');
        });
    }

    public function down(): void
    {
        Schema::table('patient_visit_drafts', function (Blueprint $table) {
            $table->dropColumn('form_data');
        });
    }
};
