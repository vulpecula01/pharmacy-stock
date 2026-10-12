@extends('layouts.app')
@section('title', 'รายงานการเบิกจ่าย')
@section('content')

<div class="mb-8 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-800">รายงานการเบิกจ่ายยา</h1>
    <form action="{{ route('export.dispensing') }}" method="GET" class="flex items-end gap-3">
        {{-- ส่งค่า Filter ไปยังปุ่ม Export ด้วย --}}
        <input type="hidden" name="start_month" value="{{ request('start_month', date('Y-01')) }}">
        <input type="hidden" name="end_month" value="{{ request('end_month', date('Y-m')) }}">
        <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-green-700 shadow-md">
            <i class="fas fa-file-excel mr-2"></i> Export Excel
        </button>
    </form>
</div>

<div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-6">
    <form action="{{ route('reports.dispensing') }}" method="GET" class="flex flex-wrap items-end gap-4">
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">ตั้งแต่เดือน:</label>
            <input type="month" name="start_month" value="{{ request('start_month', date('Y-01')) }}" class="border border-gray-200 p-2 rounded-xl outline-none focus:border-blue-500">
        </div>
        <div>
            <label class="block text-sm font-bold text-gray-700 mb-1">ถึงเดือน:</label>
            <input type="month" name="end_month" value="{{ request('end_month', date('Y-m')) }}" class="border border-gray-200 p-2 rounded-xl outline-none focus:border-blue-500">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-blue-700 transition-all">
            ค้นหา
        </button>
    </form>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
    <table class="w-full text-left border-collapse min-w-[1800px]"> <thead>
        <tr class="bg-gray-50 border-b border-gray-100">
            <th class="p-4 font-bold text-gray-700">วันที่-เวลา</th>
            <th class="p-4 font-bold text-gray-700">พนักงาน/สังกัด</th> <th class="p-4 font-bold text-gray-700">ระบบที่เกี่ยวข้อง</th> <th class="p-4 font-bold text-gray-700">อาการ/วินิจฉัย</th>
            <th class="p-4 font-bold text-gray-700 text-center">Vitals</th>
            <th class="p-4 font-bold text-gray-700">ยาที่จ่าย</th>
            <th class="p-4 font-bold text-gray-700 text-center">จำนวน</th>
            <th class="p-4 font-bold text-gray-700 text-right text-blue-600">ราคา/หน่วย</th>
            <th class="p-4 font-bold text-gray-700 text-right text-green-600">ราคารวม</th>
            <th class="p-4 font-bold text-gray-700">ผู้จ่ายยา</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-200">
        {{-- 🟢 จัดกลุ่มโดยการแปลง created_at ให้เหลือแค่ วัน/เดือน/ปี ชั่วโมง:นาที เพื่อให้ยาที่คีย์เข้ามาพร้อมกันอยู่กลุ่มเดียวกัน --}}
        @forelse($records->groupBy(function($item) {
            return \Carbon\Carbon::parse($item->created_at)->format('Y-m-d H:i');
        }) as $timeGroup => $group)
            @php 
                $rowCount = $group->count(); // นับจำนวนยาในรอบเวลานี้
                $firstRecord = $group->first(); // ดึงแถวแรกเป็นตัวแทนข้อมูลคนไข้
            @endphp

            @foreach($group as $index => $r)
            <tr class="hover:bg-gray-50/50 transition-all text-sm">
                
                {{-- 🔴 ส่วนที่ 1: ข้อมูลคนไข้และอาการ (แสดงเฉพาะแถวแรกของกลุ่มเวลา และใช้ rowspan ขยายลงมา) --}}
                @if($index === 0)
                    <td class="p-4 text-gray-600 align-top border-b border-gray-100 font-medium" rowspan="{{ $rowCount }}">
                        {{ \Carbon\Carbon::parse($firstRecord->created_at)->format('d/m/Y H:i') }}
                    </td>
                    
                    <td class="p-4 align-top border-b border-gray-100" rowspan="{{ $rowCount }}">
                        <div class="font-bold text-gray-800">{{ $firstRecord->patient->first_name ?? '' }} {{ $firstRecord->patient->last_name ?? '' }}</div>
                        <div class="text-[10px] text-blue-600 font-semibold mt-0.5">{{ $firstRecord->company ?? '-' }}</div> 
                        <div class="text-[10px] text-gray-500 italic">{{ $firstRecord->department ?? '-' }}</div> 
                    </td>
                    
                    <td class="p-4 align-top border-b border-gray-100" rowspan="{{ $rowCount }}">
                        <span class="text-xs bg-yellow-50 text-yellow-700 px-2 py-1 rounded-lg border border-yellow-100 block text-center font-medium">
                            {{ $firstRecord->medical_systems ?? 'ทั่วไป' }}
                        </span>
                    </td>
                    
                    <td class="p-4 text-gray-600 align-top border-b border-gray-100" rowspan="{{ $rowCount }}">
                        <div class="font-semibold text-gray-800">{{ $firstRecord->diagnosis ?? '-' }}</div>
                        <div class="text-[10px] text-red-500 mt-0.5 font-medium">{{ $firstRecord->illness_type }}</div> 
                    </td>
                    
                    <td class="p-4 align-top border-b border-gray-100" rowspan="{{ $rowCount }}">
                        <div class="grid grid-cols-2 gap-1 text-[10px] min-w-[130px] font-mono">
                            <span class="bg-blue-50 p-1 rounded text-blue-700 font-bold">BP: {{ $firstRecord->bp ?? '-' }}</span>
                            <span class="bg-red-50 p-1 rounded text-red-700 font-bold">PR: {{ $firstRecord->pr ?? '-' }}</span>
                            <span class="bg-green-50 p-1 rounded text-green-700 font-bold">RR: {{ $firstRecord->rr ?? '-' }}</span>
                            <span class="bg-orange-50 p-1 rounded text-orange-700 font-bold">T: {{ $firstRecord->temp ?? '-' }}</span>
                            <span class="bg-purple-50 p-1 rounded text-purple-700 col-span-2 text-center font-bold">O2: {{ $firstRecord->o2_sat ?? '-' }}</span>
                        </div>
                    </td>
                @endif

                {{-- 🔵 ส่วนที่ 2: รายการยา (แยกแถวย่อยลงมาตามจำนวนยาที่เบิก) --}}
                <td class="p-4 border-b border-gray-100 bg-white group-hover:bg-gray-50">
                    <div class="font-bold text-gray-800">{{ $r->medicine->name ?? 'ไม่ได้รับยา' }}</div>
                    @if($r->lot_number)
                        <div class="text-[9px] text-gray-400 font-mono mt-0.5">Lot: {{ $r->lot_number }}</div>
                    @endif
                </td>
                
                <td class="p-4 text-center font-black text-base border-b border-gray-100 bg-white group-hover:bg-gray-50">
                    {{ number_format($r->amount) }}
                </td>
                
                <td class="p-4 text-right text-blue-600 font-mono border-b border-gray-100 bg-white group-hover:bg-gray-50">
                    {{ number_format($r->price_per_unit, 2) }}
                </td>
                
                <td class="p-4 text-right font-bold text-green-600 font-mono border-b border-gray-100 bg-white group-hover:bg-gray-50">
                    {{ number_format($r->amount * $r->price_per_unit, 2) }}
                </td>

                {{-- 🔴 ส่วนที่ 3: ผู้จ่ายยา (ยุบรวมแสดงแถวเดียวเท่ากับกลุ่มเวลา) --}}
                @if($index === 0)
                    <td class="p-4 text-gray-500 italic align-top border-b border-gray-100" rowspan="{{ $rowCount }}">
                        <span class="font-semibold text-gray-700">{{ $firstRecord->issued_by ?? '-' }}</span>
                    </td>
                @endif

            </tr>
            @endforeach
        @empty
            <tr>
                <td colspan="10" class="p-8 text-center text-gray-400 font-medium">ไม่พบข้อมูลรายงานในช่วงเวลาดังกล่าว</td>
            </tr>
        @endforelse
    </tbody>
</table>
</div>

@endsection