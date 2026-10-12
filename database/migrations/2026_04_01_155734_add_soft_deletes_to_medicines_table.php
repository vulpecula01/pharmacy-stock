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
        $table->softDeletes(); // เพิ่ม Column deleted_at สำหรับเก็บวันที่ลบ
    });
}

public function down(): void
{
    Schema::table('medicines', function (Blueprint $table) {
        $table->dropSoftDeletes(); // ลบ Column deleted_at ทิ้งถ้าสั่ง rollback
    });
}
};
