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
    Schema::create('medicine_returns', function (Blueprint $table) {
        $table->id();
        // อ้างอิง ID ของการจ่ายยาต้นทาง
        $table->foreignId('dispensing_record_id')->constrained('dispensing_records')->onDelete('cascade');
        $table->foreignId('medicine_id')->constrained('medicines');
        $table->integer('amount'); // จำนวนที่คืน
        $table->string('reason')->nullable(); // เหตุผลการคืน
        $table->string('returned_by'); // ใครเป็นคนทำรายการคืน
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_returns');
    }
};
