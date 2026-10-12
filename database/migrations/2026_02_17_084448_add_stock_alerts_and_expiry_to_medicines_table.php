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
        Schema::table('medicines', function (Blueprint $table) {
            // เพิ่มคอลัมน์สำหรับแจ้งเตือน Stock และวันหมดอายุ
            $table->integer('min_stock')->default(10)->after('stock_c'); // จุดเตือนให้สั่งซื้อเพิ่ม
            $table->integer('max_stock')->default(100)->after('min_stock'); // จุดเตือนว่ายาเยอะเกินไป
            $table->date('expiry_date')->nullable()->after('max_stock');    // วันหมดอายุ
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            // ลบคอลัมน์ออกหากมีการ Rollback
            $table->dropColumn(['min_stock', 'max_stock', 'expiry_date']);
        });
    }
};