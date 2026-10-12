<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockPrediction extends Model
{
    protected $fillable = ['medicine_id', 'predict_date', 'predicted_quantity'];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class, 'medicine_id');
    }
    public function predictions()
    {
        // ยา 1 รายการ สามารถมีรายการที่ AI ทำนายอนาคต (รายวัน) ได้หลายรายการ
        return $this->hasMany(StockPrediction::class, 'medicine_id');
    }
}