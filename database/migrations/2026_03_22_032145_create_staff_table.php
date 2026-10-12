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
        Schema::create('staffs', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // ชื่อ-นามสกุล เจ้าหน้าที่
    $table->string('position')->nullable(); // ตำแหน่ง เช่น พยาบาลวิชาชีพ, จพ.สาธารณสุข
    $table->boolean('is_active')->default(true); // สถานะการทำงาน
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
