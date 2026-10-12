<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicine_lots', function (Blueprint $table) {
            $table->id();
            // เชื่อมโยงกับตาราง medicines (ลบยาปุ๊บ ลบ Lot ตาม)
            $table->foreignId('medicine_id')->constrained('medicines')->onDelete('cascade');
            
            $table->string('lot_number')->nullable(); // เลขที่ Lot (ถ้ามี)
            $table->integer('quantity');               // จำนวนคงเหลือใน Lot นี้
            $table->date('expire_date');              // วันหมดอายุของ Lot นี้
            
            $table->timestamps();
            
            // เพิ่ม Index เพื่อให้เรียงลำดับวันหมดอายุได้เร็วขึ้น
            $table->index('expire_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_lots');
    }
};