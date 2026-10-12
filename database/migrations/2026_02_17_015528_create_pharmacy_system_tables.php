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
    // 1. ตารางคนไข้/พนักงาน
    Schema::create('patients', function (Blueprint $table) {
        $table->id();
        $table->string('employee_id')->unique(); // รหัสพนักงาน
        $table->string('first_name');
        $table->string('last_name');
        $table->text('congenital_disease')->nullable(); // โรคประจำตัว
        $table->text('drug_allergy')->nullable();       // ประวัติแพ้ยา
        $table->timestamps();
    });

    // 2. ตารางคลังยา (รองรับ คลังใหญ่, A, B, C)
    Schema::create('medicine_stocks', function (Blueprint $table) {
        $table->id();
        $table->string('medicine_name');
        $table->string('location'); // 'Main', 'A', 'B', 'C'
        $table->integer('quantity')->default(0);
        $table->timestamps();
    });

    // 3. ตารางประวัติการจ่ายยาให้คนไข้
    Schema::create('dispensing_records', function (Blueprint $table) {
        $table->id();
        $table->foreignId('patient_id')->constrained();
        $table->foreignId('medicine_stock_id')->constrained();
        $table->integer('amount');
        $table->string('from_location'); // จ่ายออกจากคลังไหน
        $table->timestamps();
    });
}
};
