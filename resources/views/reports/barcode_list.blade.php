@extends('layouts.app')
@section('title', 'เลือกยาเพื่อพิมพ์บาร์โค้ด')
@section('content')

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">ระบบพิมพ์บาร์โค้ดยา</h1>
    <p class="text-gray-500">เลือกรายการยาที่ต้องการพิมพ์สติ๊กเกอร์บาร์โค้ด</p>
</div>
<div>
<a href="{{ route('medicines.print-all') }}" target="_blank" class="bg-gray-800 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-black transition-all shadow-sm">
    🖨️ พิมพ์บาร์โค้ดทั้งหมด ({{ $medicines->count() }})
</a>
</div>
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <table class="w-full text-left">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-6 py-4 font-bold text-gray-400 uppercase text-xs">ชื่อยา</th>
                <th class="px-6 py-4 font-bold text-gray-400 uppercase text-xs">เลขบาร์โค้ด</th>
                <th class="px-6 py-4 font-bold text-gray-400 uppercase text-xs">บริษัท</th>
                <th class="px-6 py-4 font-bold text-gray-400 uppercase text-xs text-right">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($medicines as $m)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 font-bold text-gray-700">{{ $m->name }}</td>
                <td class="px-6 py-4 font-mono text-sm">{{ $m->barcode }}</td>
                <td class="px-6 py-4 text-gray-500 text-sm">{{ $m->company_name }}</td>
                <td class="px-6 py-4 text-right">
                    <a href="{{ route('report.barcode.print', $m->id) }}" target="_blank" 
                       class="bg-gray-800 text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-black">
                        <i class="fas fa-print mr-1"></i> พิมพ์
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection