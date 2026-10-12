<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\ReceiveController;
use App\Http\Controllers\PharmacyController;
use App\Models\Medicine;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\MedicineLotController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MedicineAnalyticsController;
/*
|--------------------------------------------------------------------------
| Public Routes (ไม่ต้อง Login)
|--------------------------------------------------------------------------
*/
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


/*
|--------------------------------------------------------------------------
| Protected Routes (ต้อง Login ก่อนเท่านั้น)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // --- Dashboard / Home ---
    Route::get('/', [TransferController::class, 'index'])->name('transfers.index');
    Route::get('/dashboard', [PharmacyController::class, 'dashboard'])->name('pharmacy.dashboard');
Route::get('/medicines/print-all-barcodes', [App\Http\Controllers\PharmacyController::class, 'printAllBarcodes'])
    ->name('medicines.print-all');
    Route::post('/medicine-lots/{id}/update-ajax', [MedicineLotController::class, 'updateAjax'])->name('medicine-lots.update-ajax');
    // --- 1. ระบบรับยาเข้า (Receive) ---
    Route::controller(ReceiveController::class)->group(function () {
        Route::get('/receive/create', 'create')->name('receives.create');
        Route::post('/receive/store', 'store')->name('receives.store');

    });

    // --- 2. ระบบเบิกจ่ายระหว่างคลัง (Transfer) ---
    Route::controller(TransferController::class)->group(function () {
        Route::get('/transfer/create', 'create')->name('transfers.create');
        Route::post('/transfer/store', 'store')->name('transfers.store');
    });

    // --- 3. ระบบจัดการข้อมูลยา (Medicine) ---
    Route::post('/medicines/{id}/clear', [TransferController::class, 'clearStock'])->name('medicines.clear');
    Route::resource('medicines', TransferController::class)->except(['index', 'create', 'store']);
Route::get('/pharmacy/import', [App\Http\Controllers\PharmacyController::class, 'showImport'])->name('pharmacy.import.view');
Route::resource('staff', StaffController::class)->only(['index', 'store', 'destroy']);
Route::get('/units', [UnitController::class, 'index'])->name('units.index');
Route::post('/units', [UnitController::class, 'store'])->name('units.store');
Route::get('/pharmacy/patient-history/{id}', [App\Http\Controllers\PharmacyController::class, 'getPatientHistory']);
Route::get('/pharmacy/returns-history', [PharmacyController::class, 'returnHistory'])->name('pharmacy.returns.history');
Route::post('/pharmacy/return/{id}', [PharmacyController::class, 'returnMedicine']);
Route::delete('/units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');
// รับไฟล์ CSV ไปเซฟลงฐานข้อมูล
Route::post('/pharmacy/import', [App\Http\Controllers\PharmacyController::class, 'importCSV'])->name('pharmacy.import.process');
Route::resource('medicine_lots', MedicineLotController::class);
Route::get('/patients', [PharmacyController::class, 'patientList'])->name('patients.index');
Route::put('/patients/{id}', [PharmacyController::class, 'updatePatient'])->name('patients.update');
Route::delete('/patients/{id}', [PharmacyController::class, 'destroyPatient'])->name('patients.destroy');
Route::get('/patients/{id}', [PharmacyController::class, 'showPatient'])->name('patients.show');
Route::post('/pharmacy/return/{id}', [PharmacyController::class, 'returnMedicine']);
Route::put('/medicines/{id}', [App\Http\Controllers\PharmacyController::class, 'updateMedicine'])->name('medicines.update');
Route::put('/medicine-lots/{id}', [PharmacyController::class, 'updateLot'])->name('medicine_lots.update');
Route::put('/medicine_lots/{id}', [App\Http\Controllers\TransferController::class, 'updateLotPrice']);
// ใช้เส้นทางเดียวที่สื่อสารชัดเจน
Route::get('/api/patients/{id}/history', [PharmacyController::class, 'getPatientHistory']);
Route::resource('categories', CategoryController::class);
    // --- 4. กลุ่มรายงาน (Reports) ---
    Route::controller(ReportController::class)->group(function () {
        Route::get('/reports/inventory', 'inventoryIndex')->name('reports.inventory');
        Route::get('/reports/dispensing', 'dispensingIndex')->name('reports.dispensing');
        Route::get('/reports/barcode', 'barcodeIndex')->name('reports.barcode');
        Route::get('/reports/print/barcode/{id}', 'printBarcode')->name('report.barcode');
        Route::get('/reports/print/{id}', 'printBarcode')->name('report.barcode.print');

        // Export CSV
        Route::get('/reports/export/inventory', 'exportInventory')->name('export.inventory');
        Route::get('/reports/export/dispensing', 'exportDispensing')->name('export.dispensing');
    });
Route::delete('/medicines/{id}', [TransferController::class, 'destroy'])->name('medicines.destroy');
// หรือถ้าใช้ Resource
    // --- 5. ระบบจ่ายยาให้คนไข้ (Pharmacy) ---
    Route::controller(PharmacyController::class)->group(function () {
        Route::get('/pharmacy', 'index')->name('pharmacy.index');
        Route::post('/pharmacy/store', 'storeDispensing')->name('pharmacy.store');
        Route::get('/pharmacy/patient/{empId}', 'getPatientData')->name('pharmacy.patient.fetch');
        Route::get('/patients', 'patientList')->name('patients.index');
        // คิว Draft: บันทึกไว้ก่อนตอนคนไข้มาถึงแต่ยังไม่จ่ายยาจริง (เก็บสถิติเวลาที่มาใช้บริการ)
        Route::post('/pharmacy/draft', 'storeDraft')->name('pharmacy.draft.store');
        Route::post('/pharmacy/draft/{id}/cancel', 'cancelDraft')->name('pharmacy.draft.cancel');

    });

    // --- 6. API สำหรับสแกนบาร์โค้ด ---
    // --- 6. API สำหรับสแกนบาร์โค้ด ---
// ค้นหายาจากชื่อ/บาร์โค้ด (ใช้ในหน้ารับยาเข้าคลังใหญ่)
Route::get('/api/search-medicine', function (\Illuminate\Http\Request $request) {
    $q = trim((string) $request->query('q', ''));
    if (mb_strlen($q) < 1) {
        return response()->json([]);
    }
    $like = '%' . $q . '%';
    $rows = \App\Models\Medicine::where(function ($w) use ($like) {
            $w->where('name', 'like', $like)->orWhere('barcode', 'like', $like);
        })
        ->orderBy('name')
        ->limit(10)
        ->get(['barcode', 'name', 'unit_name', 'stock_master']);
    return response()->json($rows);
});

Route::get('/api/check-medicine/{barcode}', function ($barcode) {
    $medicine = \App\Models\Medicine::where('barcode', $barcode)->first();
    
    if ($medicine) {
        // 1. ดึง Lot ล่าสุด (เพื่อเอาวันหมดอายุมาโชว์ในช่อง Input เป็นค่าเริ่มต้น)
        $latestLot = \App\Models\MedicineLot::where('medicine_id', $medicine->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // 2. ดึงรายการ Lot ทั้งหมดที่มีของเหลือ (เพื่อเอาไปโชว์ใน Pop-up ประวัติ)
        $allLots = \App\Models\MedicineLot::where('medicine_id', $medicine->id)
            ->where('quantity', '>', 0)
            ->orderBy('expire_date', 'asc')
            ->get();

        return response()->json([
            'exists'       => true,
            'name'         => $medicine->name,
            'unit_name'    => $medicine->unit_name,
            'category'     => $medicine->category,
            'company_name' => $medicine->company_name,
            'expire_date'  => $latestLot ? $latestLot->expire_date : null, 
            'stock_master' => $medicine->stock_master,
            'min_stock'    => $medicine->min_stock,
            'max_stock'    => $medicine->max_stock,
            // --- เพิ่มบรรทัดนี้เพื่อให้ JavaScript ในหน้า View ไม่พัง ---
            'lots'         => $allLots 
        ]);
    }
    return response()->json(['exists' => false]);
});
Route::get('/pharmacy/analytics', [MedicineAnalyticsController::class, 'index'])->name('pharmacy.analytics');
Route::get('/pharmacy/analytics/drug-trend/{id}', [MedicineAnalyticsController::class, 'getDrugTrendApi']);
Route::post('/pharmacy/analytics/trigger-ai', [MedicineAnalyticsController::class, 'triggerAiTrain'])->name('pharmacy.analytics.trigger-ai');

});