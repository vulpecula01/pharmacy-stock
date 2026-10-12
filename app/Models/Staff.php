<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    // บังคับให้ใช้ตารางชื่อ staffs (มี s)
    protected $table = 'staffs'; 

    protected $fillable = ['name', 'is_active'];
}