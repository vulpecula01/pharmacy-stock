<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * เพิ่มฟิลด์สำหรับ "ระบบพิมพ์ฉลากยาให้คนไข้" (patient medicine label)
     * - medicines: ค่าเริ่มต้นของวิธีใช้/สรรพคุณ/ผลข้างเคียง ที่กรอกไว้ล่วงหน้าต่อรายการยา
     * - dispensing_records: ค่าจริงที่พิมพ์ลงฉลากในการจ่ายยาครั้งนั้นๆ (เผื่อถูกแก้ไขก่อนพิมพ์)
     */
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->text('usage_instruction')->nullable()->after('unit_name')
                ->comment('วิธีใช้/ขนาดรับประทาน (ค่าเริ่มต้นสำหรับพิมพ์ฉลาก)');
            $table->text('indication')->nullable()->after('usage_instruction')
                ->comment('สรรพคุณ (ค่าเริ่มต้นสำหรับพิมพ์ฉลาก)');
            $table->text('side_effect')->nullable()->after('indication')
                ->comment('ผลข้างเคียงสำคัญ (ค่าเริ่มต้นสำหรับพิมพ์ฉลาก)');
        });

        Schema::table('dispensing_records', function (Blueprint $table) {
            $table->text('usage_instruction')->nullable()->after('amount')
                ->comment('วิธีใช้ที่พิมพ์จริงบนฉลากยา (แก้ไขได้ตอนจ่ายยา)');
            $table->text('indication')->nullable()->after('usage_instruction')
                ->comment('สรรพคุณที่พิมพ์จริงบนฉลากยา');
            $table->text('side_effect')->nullable()->after('indication')
                ->comment('ผลข้างเคียงที่พิมพ์จริงบนฉลากยา');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn(['usage_instruction', 'indication', 'side_effect']);
        });

        Schema::table('dispensing_records', function (Blueprint $table) {
            $table->dropColumn(['usage_instruction', 'indication', 'side_effect']);
        });
    }
};
