<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
  public function boot(): void
    {
        // ใช้ View::composer แบบระบุ namespace เต็ม หรือ Import ด้านบน
        View::composer('*', function ($view) {
            
            if (!Schema::hasTable('medicines') || !Schema::hasTable('medicine_lots')) {
                return;
            }

            try {
                $today = Carbon::now()->startOfDay();

                // ดึงข้อมูลยาใกล้หมดอายุ
                $expireList = DB::table('medicine_lots')
                    ->join('medicines', 'medicine_lots.medicine_id', '=', 'medicines.id')
                    ->select(
                        'medicines.name', 
                        'medicine_lots.lot_number', 
                        'medicine_lots.expire_date', 
                        'medicine_lots.quantity as lot_stock' // ตรวจสอบว่าใน DB ชื่อฟิลด์ quantity หรือไม่
                    )
                    ->where('medicine_lots.quantity', '>', 0)
                    ->whereNotNull('medicine_lots.expire_date')
                    ->whereDate('medicine_lots.expire_date', '<=', $today->copy()->addDays(180))
                    ->get()
                    ->map(function($lot) use ($today) {
                        $exp = Carbon::parse($lot->expire_date)->startOfDay();
                        $diff = (int)$today->diffInDays($exp, false);
                        
                        if ($diff < 0) {
                            $lot->alert_msg = "หมดอายุแล้ว " . abs($diff) . " วัน";
                        } elseif ($diff == 0) {
                            $lot->alert_msg = "หมดอายุวันนี้";
                        } else {
                            $lot->alert_msg = "จะหมดในอีก " . $diff . " วัน";
                        }
                        return $lot;
                    })->sortBy('expire_date');

                $allMedicines = \App\Models\Medicine::all();
                $minList = $allMedicines->filter(fn($m) => $m->min_stock > 0 && $m->stock_master <= $m->min_stock);
                $maxList = $allMedicines->filter(fn($m) => $m->max_stock > 0 && $m->stock_master >= $m->max_stock);

                $view->with([
                    'expireList' => $expireList,
                    'minList' => $minList,
                    'maxList' => $maxList,
                    'nearExpiredCount' => $expireList->count(),
                    'minStockCount' => $minList->count(),
                    'maxStockCount' => $maxList->count(),
                ]);

            } catch (\Exception $e) {
                // บันทึก log หากต้องการ: \Log::error($e->getMessage());
            }
        });
    }

}
