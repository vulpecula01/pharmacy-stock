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
    Schema::table('dispensing_records', function (Blueprint $table) {
        // เพิ่มเฉพาะ 2 ฟิลด์ที่ขาด
        $table->string('company')->nullable()->after('department');
        $table->text('medical_systems')->nullable()->after('diagnosis');
    });
}

public function down()
{
    Schema::table('dispensing_records', function (Blueprint $table) {
        $table->dropColumn(['company', 'medical_systems']);
    });
}
};
