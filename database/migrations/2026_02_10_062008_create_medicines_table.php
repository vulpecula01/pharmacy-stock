<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
    Schema::create('medicines', function (Blueprint $table) {
    $table->id();
    $table->string('barcode')->unique();
    $table->string('name');
    $table->string('supplier_name');
    // เก็บยอดสต็อกแยกคลังในตารางเดียวกันเพื่อความง่ายในระยะเริ่มต้น
    $table->integer('stock_master')->default(0);
    $table->integer('stock_a')->default(0);
    $table->integer('stock_b')->default(0);
    $table->integer('stock_c')->default(0);
    $table->timestamps();
});
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
