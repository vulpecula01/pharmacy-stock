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
    Schema::table('transfers', function (Blueprint $table) {
        // เพิ่มฟิลด์วันที่รับเข้า สามารถเป็น Nullable ได้หากข้อมูลเก่าไม่มี
        $table->date('receive_date')->nullable()->after('lot_number');
    });
}

public function down()
{
    Schema::table('transfers', function (Blueprint $table) {
        $table->dropColumn('receive_date');
    });
}
};
