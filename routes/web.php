<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\ReceiveController;

// หน้าแรก แสดงสต็อกและประวัติ
Route::get('/', [TransferController::class, 'index'])->name('transfers.index');

// หน้าฟอร์มเบิกยา
Route::post('/medicines/{id}/clear', [TransferController::class, 'clearStock'])->name('medicines.clear');
Route::resource('transfers', TransferController::class);
Route::resource('medicines', TransferController::class)->except(['index', 'create', 'store']);
Route::get('/transfer/create', [TransferController::class, 'create'])->name('transfers.create');
Route::post('/transfer/store', [TransferController::class, 'store'])->name('transfers.store');
Route::get('/receive/create', [ReceiveController::class, 'create'])->name('receives.create');
Route::post('/receive/store', [ReceiveController::class, 'store'])->name('receives.store');
Route::get('/api/check-medicine/{barcode}', function($barcode) {
    $medicine = \App\Models\Medicine::where('barcode', $barcode)->first();
    if ($medicine) {
        return response()->json([
            'exists' => true,
            'name' => $medicine->name
        ]);
    }
    return response()->json(['exists' => false]);
});