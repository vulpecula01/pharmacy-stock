@extends('layouts.app')
@section('title', 'รายงานสต็อกคงเหลือ')
@section('content')
<div class="mb-8 flex justify-between items-end">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">รายงานสต็อกคงเหลือ</h1>
        <p class="text-gray-500">ตรวจสอบจำนวนยาและวันหมดอายุในคลังหลัก</p>
    </div>
    <a href="{{ route('export.inventory') }}" class="bg-green-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-green-700 shadow-md">
        <i class="fas fa-file-excel mr-2"></i> ดาวน์โหลดรายงาน Excel
    </a>
</div>


@endsection