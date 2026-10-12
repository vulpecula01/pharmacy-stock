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
    Schema::table('medicine_stocks', function (Blueprint $table) {
        // เพิ่มคอลัมน์ medicine_id ต่อท้าย medicine_name
        $table->unsignedBigInteger('medicine_id')->nullable()->after('medicine_name');
        
        // (Optional) ถ้าต้องการทำ Foreign Key
        // $table->foreign('medicine_id')->references('id')->on('medicines')->onDelete('cascade');
    });
}

public function down()
{
    Schema::table('medicine_stocks', function (Blueprint $table) {
        $table->dropColumn('medicine_id');
    });
}
};
