<?php

namespace App\Imports;

use App\Models\Medicine;
use App\Models\MedicineLot;
use App\Models\Category;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MedicinesImport implements OnEachRow, WithHeadingRow
{
    public function onRow(Row $row) 
    {
        $data = $row->toArray();
        
        if (empty($data['barcode'])) {
            return;
        }

        $importStock = (int) ($data['stock'] ?? 0);
        $rawPrice = $data['priceperunit'] ?? 0;
        $importPrice = (float) str_replace(',', '', $rawPrice);
        
        $expireValue = isset($data['expire']) ? trim($data['expire']) : null;
        $expireDate = $this->transformDate($expireValue);
        $categoryName = !empty($data['category']) ? trim($data['category']) : 'อื่นๆ';

        DB::transaction(function () use ($data, $importStock, $importPrice, $expireDate, $categoryName) {
            
            $category = Category::firstOrCreate(
                ['name' => $categoryName],
                ['is_active' => true]
            );

            $medicine = Medicine::where('barcode', $data['barcode'])->first();

            $medicineData = [
                'name'           => $data['name'] ?? 'ไม่ระบุชื่อ',
                'category'       => $category->name,
                'company_name'   => $data['company_name'] ?? ($medicine->company_name ?? 'ไม่ระบุ'),
                'unit_name'      => $data['unit'] ?? ($medicine->unit_name ?? 'ชิ้น'),
                'price_per_unit' => $importPrice > 0 ? $importPrice : ($medicine->price_per_unit ?? 0),
                'expire_date'    => $expireDate ?? ($medicine->expire_date ?? null),
                'min_stock'      => isset($data['min']) ? (int)$data['min'] : ($medicine->min_stock ?? 0),
                'max_stock'      => isset($data['max']) ? (int)$data['max'] : ($medicine->max_stock ?? 9999),
            ];

            if ($medicine) {
                $medicineData['stock_master'] = $medicine->stock_master + $importStock;
                $medicine->update($medicineData);
            } else {
                $medicineData['barcode'] = $data['barcode'];
                $medicineData['stock_master'] = $importStock;
                $medicine = Medicine::create($medicineData); 
            }

            // --- ส่วนที่แก้ไข: สร้างชื่อ Lot จากชื่อยาที่นำเข้า ---
            if ($importStock > 0 && $medicine) {
                // ลบช่องว่างและเอา 4 ตัวแรกมาทำเป็นตัวใหญ่ (เหมือนใน ReceiveController)
                $cleanName = str_replace(' ', '', $medicine->name);
                $prefix = strtoupper(mb_substr($cleanName, 0, 4, 'UTF-8'));
                
                // รูปแบบ: ชื่อย่อ-IMP-วันที่ (เช่น AMOX-IMP-20260411)
                $lotName = $prefix . '-IMP-' . date('YmdHi');

                MedicineLot::create([
                    'medicine_id'    => $medicine->id,
                    'lot_number'     => $lotName, // ใช้ชื่อที่เจนตามยา
                    'quantity'       => $importStock,
                    'price_per_unit' => $importPrice, 
                    'expire_date'    => $expireDate ?? ($medicine->expire_date ?? now()->addYears(2)), 
                ]);
            }
        });
    }

    private function transformDate($value) 
    {
        if (empty($value) || trim($value) == '-' || trim($value) == 'NULL') return null;
        
        try {
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return Carbon::parse(str_replace('/', '-', trim($value)))->format('Y-m-d');
        } catch (\Exception $e) {
            return null; 
        }
    }
}