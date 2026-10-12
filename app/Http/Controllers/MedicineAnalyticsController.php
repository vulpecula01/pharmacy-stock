<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\StockPrediction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MedicineAnalyticsController extends Controller
{
    public function index()
    {
        // 1. ดึงรายการยา + ยอดสต็อกปัจจุบัน + ยอดรวมที่ AI ทำนายว่าจะถูกใช้ใน 30 วันข้างหน้า
        $medicines = Medicine::select('id', 'name', 'stock_master')
            ->withSum(['predictions as total_predicted_demand' => function($query) {
                // เอาเฉพาะยอดทำนายตั้งแต่วันนี้ไปอีก 30 วัน
                $query->where('predict_date', '>=', now()->toDateString());
            }], 'predicted_quantity')
            ->get();

        // 2. จัดกลุ่มข้อมูลเพื่อเตรียมส่งไปพลอตกราฟภาพรวมกลุ่มอาการ (Optional)
        // ดึงจากประวัติเก่ามาดูสัดส่วนยอดฮิต
        $symptomTrends = DB::table('dispensing_records')
            ->select('symptom_groups', DB::raw('count(*) as count'))
            ->whereNotNull('symptom_groups')
            ->where('symptom_groups', '!=', '')
            ->groupBy('symptom_groups')
            ->orderBy('count', 'desc')
            ->take(5)
            ->get();

        return view('pharmacy.analytics', compact('medicines', 'symptomTrends'));
    }

    // API ดึงข้อมูลรายวันของยาตัวนั้นๆ เพื่อเอาไปวาดกราฟเส้นแนวโน้มอนาคตในหน้าบ้าน
    public function getDrugTrendApi($id)
    {
        // ดึงยอดเบิกจริงย้อนหลัง 30 วัน
        $history = DB::table('dispensing_records')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total'))
            ->where('medicine_id', $id)
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // ดึงยอดที่ AI ทำนายล่วงหน้า 30 วัน
        $predictions = StockPrediction::select('predict_date as date', 'predicted_quantity as total')
            ->where('medicine_id', $id)
            ->where('predict_date', '>=', now()->toDateString())
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'history' => $history,
            'predictions' => $predictions
        ]);
    }

    // ปุ่มกดสั่งรัน Python Script จากหน้าเว็บโดยตรง เพื่ออัปเดต AI
    public function triggerAiTrain()
    {
        // รันสคริปต์ Python ที่เราเขียนทิ้งไว้ในโปรเจกต์
        $output = shell_exec('python ' . base_path('stock_forecast.py') . ' 2>&1');
        
        return response()->json([
            'success' => true, 
            'message' => 'AI ประมวลผลและอัปเดตแนวโน้มคลังยาเรียบร้อยแล้ว',
            'output' => $output
        ]);
    }
}
