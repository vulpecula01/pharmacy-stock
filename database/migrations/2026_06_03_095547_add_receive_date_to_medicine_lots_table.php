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
    Schema::table('medicine_lots', function (Blueprint $table) {
        // เพิ่มคอลัมน์ receive_date ต่อท้าย price_per_unit
        $table->date('receive_date')->nullable()->after('price_per_unit');
    });
}

public function down()
{
    Schema::table('medicine_lots', function (Blueprint $table) {
        $table->dropColumn('receive_date');
    });
}
};
