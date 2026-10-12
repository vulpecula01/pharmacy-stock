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
    Schema::table('transfers', function (Blueprint $table) {
        // เพิ่มคอลัมน์เก็บยอดยกมาของต้นทางและปลายทาง
        $table->integer('prev_stock_from')->default(0)->after('from_location'); 
        $table->integer('prev_stock_to')->default(0)->after('to_location');
    });
}

public function down(): void
{
    Schema::table('transfers', function (Blueprint $table) {
        $table->dropColumn(['prev_stock_from', 'prev_stock_to']);
    });
}
};
