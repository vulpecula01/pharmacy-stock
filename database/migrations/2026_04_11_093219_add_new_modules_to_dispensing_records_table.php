<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dispensing_records', function (Blueprint $column) {
            // เพิ่มคอลัมน์ใหม่หลังจาก medical_systems (หรือต่อท้าย)
            $column->text('symptom_groups')->nullable()->after('medical_systems')->comment('กลุ่มอาการ/ระบบอวัยวะ');
            $column->text('accident_types')->nullable()->after('symptom_groups')->comment('ประเภทอุบัติเหตุ');
            $column->text('service_types')->nullable()->after('accident_types')->comment('ประเภทการบริการ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dispensing_records', function (Blueprint $column) {
            $column->dropColumn(['symptom_groups', 'accident_types', 'service_types']);
        });
    }
};
