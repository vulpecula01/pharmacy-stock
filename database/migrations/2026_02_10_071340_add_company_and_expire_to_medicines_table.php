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
        $table->string('company_name')->nullable()->after('supplier_name'); // เพิ่มต่อท้าย supplier_name
        $table->date('expire_date')->nullable()->after('company_name');
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('medicines', function (Blueprint $table) {
        $table->dropColumn(['company_name', 'expire_date']);
    });
}
};
