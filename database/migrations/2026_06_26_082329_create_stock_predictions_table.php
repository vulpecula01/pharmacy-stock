<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('stock_predictions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('medicine_id');      // ไอดีอ้างอิงรหัสยา
        $table->date('predict_date');                  // วันที่ในอนาคตที่ AI ทำนาย
        $table->double('predicted_quantity')->default(0); // จำนวนยาที่ AI คาดว่าจะถูกเบิกในวันนั้นๆ
        $table->timestamps();

        // ทำ Foreign Key ลิงก์กับตารางยาหลัก (ถ้ามี)
        $table->foreign('medicine_id')->references('id')->on('medicines')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_predictions');
    }
};
