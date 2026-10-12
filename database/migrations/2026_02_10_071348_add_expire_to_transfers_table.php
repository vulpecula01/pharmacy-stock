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
        $table->date('expire_date')->nullable()->after('quantity'); // เพิ่มต่อท้าย quantity
    });
}

public function down(): void
{
    Schema::table('transfers', function (Blueprint $table) {
        $table->dropColumn('expire_date');
    });
}
};
