<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * เพิ่มฟิลด์ภาษาอังกฤษของฉลากยา (วิธีใช้/สรรพคุณ/ผลข้างเคียง)
     * เพื่อรองรับฉลากยาแบบสองภาษา (ไทย + อังกฤษ) ในใบเดียว
     */
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->text('usage_instruction_en')->nullable()->after('usage_instruction');
            $table->text('indication_en')->nullable()->after('indication');
            $table->text('side_effect_en')->nullable()->after('side_effect');
        });

        Schema::table('dispensing_records', function (Blueprint $table) {
            $table->text('usage_instruction_en')->nullable()->after('usage_instruction');
            $table->text('indication_en')->nullable()->after('indication');
            $table->text('side_effect_en')->nullable()->after('side_effect');
        });
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn(['usage_instruction_en', 'indication_en', 'side_effect_en']);
        });

        Schema::table('dispensing_records', function (Blueprint $table) {
            $table->dropColumn(['usage_instruction_en', 'indication_en', 'side_effect_en']);
        });
    }
};
