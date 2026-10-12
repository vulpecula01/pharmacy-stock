@extends('layouts.app')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>

<div class="max-w-xl mx-auto mt-10 p-8 bg-white shadow-xl rounded-2xl border border-gray-100">
    <h2 class="text-xl font-bold text-gray-800 mb-6">แก้ไขล็อตยา: {{ $lot->medicine->name }}</h2>

    <form action="{{ route('medicine_lots.update', $lot->id) }}" method="POST" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">หมายเลขล็อต (Lot Number)</label>
            <input type="text" name="lot_number" value="{{ $lot->lot_number }}" class="w-full border-2 p-3 rounded-xl outline-none focus:border-blue-500">
        </div>

        <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">จำนวนคงเหลือในล็อตนี้</label>
            <input type="number" name="quantity" value="{{ $lot->quantity }}" class="w-full border-2 p-3 rounded-xl outline-none focus:border-blue-500">
        </div>
        <div>
    <label class="block text-sm font-bold text-blue-700 mb-2">ราคาซื้อต่อหน่วย (฿)</label>
    <div class="relative">
        <span class="absolute left-4 top-3 text-gray-400">฿</span>
        <input type="number" name="price_per_unit" step="0.01" 
            value="{{ $lot->price_per_unit }}" 
            class="w-full border-2 border-blue-100 p-3 pl-10 rounded-xl outline-none focus:border-blue-500 font-bold text-blue-600">
    </div>
    <p class="text-[10px] text-gray-400 mt-1">* หากราคาในล็อตนี้เปลี่ยน ระบบจะใช้ราคานี้คำนวณยอดจ่ายยาใหม่</p>
</div>

        <div>
            <label class="block text-sm font-bold text-red-600 mb-2">วันหมดอายุของล็อตนี้</label>
            <input type="date" name="expire_date" value="{{ \Carbon\Carbon::parse($lot->expire_date)->format('Y-m-d') }}" class="w-full border-2 border-red-100 p-3 rounded-xl outline-none focus:border-red-500">
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit" class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700">บันทึกข้อมูลล็อต</button>
            <a href="{{ route('medicines.edit', $lot->medicine_id) }}" class="flex-1 bg-gray-100 text-center py-3 rounded-xl font-bold text-gray-600">กลับไปหน้ายา</a>
        </div>
    </form>
</div>
@endsection