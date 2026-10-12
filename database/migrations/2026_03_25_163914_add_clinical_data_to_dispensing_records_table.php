<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('dispensing_records', function (Blueprint $table) {
            // --- ข้อมูลสัญญาณชีพ (Vital Signs) ---
            $table->string('temp')->nullable()->after('amount')->comment('อุณหภูมิ');
            $table->string('bp')->nullable()->after('temp')->comment('ความดันโลหิต');
            $table->string('pr')->nullable()->after('bp')->comment('อัตราการเต้นหัวใจ');
            $table->string('rr')->nullable()->after('pr')->comment('อัตราการหายใจ');
            $table->string('o2_sat')->nullable()->after('rr')->comment('ระดับออกซิเจน');

            // --- ข้อมูลการคัดกรองและผลการรักษา ---
            $table->string('department')->nullable()->after('o2_sat')->comment('แผนกขณะที่มา');
            $table->string('illness_type')->nullable()->after('department')->comment('ประเภท: ในงาน/นอกงาน');
            $table->text('procedure')->nullable()->after('illness_type')->comment('หัตถการ/การพยาบาล');
            $table->string('disposition')->nullable()->after('procedure')->comment('การจำหน่าย: กลับงาน/กลับบ้าน/ส่งต่อ');
        });
    }

    public function down()
    {
        Schema::table('dispensing_records', function (Blueprint $table) {
            $table->dropColumn(['temp', 'bp', 'pr', 'rr', 'o2_sat', 'department', 'illness_type', 'procedure', 'disposition']);
        });
    }
};