<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('dispensing_records', function (Blueprint $table) {
        // ถ้ามีคอลัมน์นี้อยู่แล้ว ให้เปลี่ยนให้มันเป็น Nullable (ยอมรับค่าว่างได้)
        if (Schema::hasColumn('dispensing_records', 'medicine_stock_id')) {
            $table->integer('medicine_stock_id')->nullable()->change();
        }

        // ส่วนเดิมที่เคยทำไว้ (รักษาไว้)
        if (!Schema::hasColumn('dispensing_records', 'medicine_id')) {
            $table->integer('medicine_id')->nullable()->after('patient_id');
        }
        if (!Schema::hasColumn('dispensing_records', 'diagnosis')) {
            $table->text('diagnosis')->nullable()->after('medicine_id');
        }
        if (!Schema::hasColumn('dispensing_records', 'from_location')) {
            $table->string('from_location')->nullable()->after('amount');
        }
    });

    Schema::table('patients', function (Blueprint $table) {
        if (!Schema::hasColumn('patients', 'chronic_disease')) {
            $table->text('chronic_disease')->nullable()->after('drug_allergy');
        }
    });
}

    public function down(): void
    {
        // 
    }
};