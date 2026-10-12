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
            // เพิ่มคอลัมน์ deleted_at เพื่อใช้สำหรับ Soft Delete
            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            // ลบคอลัมน์ deleted_at ออกหากมีการ Rollback Migration
            $table->dropSoftDeletes();
        });
    }
};