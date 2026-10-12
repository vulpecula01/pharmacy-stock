@extends('layouts.app')

@section('title', 'Dashboard คลังยา - MediStock')
<style>
    /* สไตล์ปุ่มตอนเอาเมาส์วางให้ขยายขึ้นเล็กน้อย */
    .medicine-card .w-8:hover {
        transform: scale(1.15) rotate(5deg);
    }
    
    /* ปรับแต่ง Scrollbar ให้ดูสะอาดตา */
    ::-webkit-scrollbar {
        width: 8px;
    }
    ::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    ::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://fonts.googleapis.com/css2?family=Sarabun&display=swap" rel="stylesheet">

<div class="max-w-7xl mx-auto space-y-6 p-4">
    
    {{-- 1. Top Bar: ส่วนควบคุมหลัก --}}
    <div class="sticky top-4 z-50 mb-8">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 bg-white/90 backdrop-blur-md p-6 rounded-[2.5rem] border border-gray-100 shadow-xl shadow-blue-900/5">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-200">
                    <i class="fas fa-box text-white text-xl"></i>
                </div>
                <div>
                    <h1 class="text-xl font-black text-gray-800 tracking-tight">คลังยาคงคลังทั้งหมด</h1>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total: {{ $medicines->count() }} Items</p>
                </div>
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                {{-- ช่องค้นหา --}}
                <div class="relative flex-1 md:w-64">
                    <input type="text" id="searchInput" placeholder="ค้นหาด่วน..." 
                        class="w-full pl-10 pr-4 py-2.5 rounded-2xl bg-white border-none ring-1 ring-gray-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm transition-all shadow-inner">
                    <i class="fas fa-search absolute left-4 top-3.5 text-gray-400 text-xs"></i>
                </div>
                
                {{-- ปุ่มรับยาเข้า --}}
                <a href="{{ route('receives.create') }}" class="bg-green-500 hover:bg-green-600 text-white px-5 py-2.5 rounded-2xl font-black text-xs transition-all shadow-lg shadow-green-200 flex items-center gap-2 shrink-0 transform active:scale-95">
                    <i class="fas fa-plus"></i> รับยาเข้า
                </a>
            </div>
        </div>
    </div>

    {{-- 2. Grid Cards: รายการยา --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" id="medicineGrid">
        @foreach($medicines as $medicine)
            @php
                $activeLots = $medicine->lots->where('quantity', '>', 0);
                
                // ดึงค่าให้ตรงกับ Database (min_stock, max_stock)
                $current = $medicine->stock_master;
                $min = $medicine->min_stock ?? 0;
                $max = $medicine->max_stock ?? 0;

                // ตรรกะเช็คสถานะ
                $isLow = $current <= $min;
                $isOver = ($max > 0 && $current > $max);
                
                if ($isLow) {
                    $statusLabel = 'Low Stock';
                    $statusClass = 'bg-red-100 text-red-600 border-red-200';
                    $cardBorder = 'border-red-200 shadow-red-50';
                    $barColor = 'bg-red-500';
                } elseif ($isOver) {
                    $statusLabel = 'Over Stock';
                    $statusClass = 'bg-orange-100 text-orange-600 border-orange-200';
                    $cardBorder = 'border-orange-200 shadow-orange-50';
                    $barColor = 'bg-orange-500';
                } else {
                    $statusLabel = 'Normal Stock';
                    $statusClass = 'bg-emerald-100 text-emerald-600 border-emerald-200';
                    $cardBorder = 'border-gray-100 shadow-blue-900/5';
                    $barColor = 'bg-emerald-500';
                }

                // คำนวณเปอร์เซ็นต์สำหรับ Progress Bar
                $percent = $max > 0 ? min(($current / $max) * 100, 100) : 100;
            @endphp
            
            <div class="medicine-card group bg-white rounded-[2.5rem] border {{ $cardBorder }} p-6 hover:shadow-2xl transition-all duration-500 relative flex flex-col h-full">
                
                {{-- Quick Actions (ลอยที่มุมขวาบน) --}}
                <div class="absolute top-6 right-6 flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                    <a href="{{ route('medicines.edit', $medicine->id) }}" class="w-8 h-8 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all">
                        <i class="fas fa-edit text-[10px]"></i>
                    </a>
                    <button type="button" onclick="confirmDelete('{{ $medicine->id }}', '{{ $medicine->name }}')" 
                        class="w-8 h-8 bg-red-50 text-red-600 rounded-full flex items-center justify-center hover:bg-red-600 hover:text-white transition-all">
                        <i class="fas fa-trash-alt text-[10px]"></i>
                    </button>
                    <form action="{{ route('medicines.destroy', $medicine->id) }}" method="POST" id="delete-form-{{ $medicine->id }}" class="hidden">
                        @csrf 
                        @method('DELETE')
                    </form>
                </div>

                {{-- Status Badge & ID --}}
                <div class="flex justify-between items-center mb-4">
                    <span class="text-[11px] font-black px-3 py-1 rounded-lg border {{ $statusClass }} uppercase tracking-wider">
                        {{ $statusLabel }}
                    </span>
                    <span class="text-xs font-mono font-bold text-gray-400">#{{ $medicine->barcode }}</span>
                </div>

                {{-- Medicine Info --}}
                <div class="mb-5">
                    <h3 class="text-xl font-black text-gray-800 leading-tight line-clamp-2 min-h-[2.5rem]">
                        {{ $medicine->name }}
                    </h3>
                </div>

                {{-- Stock Info Box (ส่วนแสดง Min/Max) --}}
                <div class="mt-auto bg-gray-50 rounded-[1.8rem] p-4 border border-gray-50">
                    <div class="flex justify-between items-end mb-3">
                        <div>
                            <p class="text-xs font-black text-gray-400 uppercase tracking-tight mb-1">คงเหลือปัจจุบัน</p>
                            <p class="text-4xl font-black {{ $isLow ? 'text-red-600' : ($isOver ? 'text-orange-600' : 'text-gray-900') }} tracking-tighter">
                                {{ number_format($current) }}
                                <span class="text-sm text-gray-500 font-bold ml-0.5">{{ $medicine->unit_name ?? 'Unit' }}</span>
                            </p>
                        </div>
                        <div class="text-right pb-1">
                            <p class="text-xs font-black text-gray-400">Min: {{ number_format($min) }}</p>
                            <p class="text-xs font-black text-gray-400">Max: {{ $max > 0 ? number_format($max) : '-' }}</p>
                        </div>
                    </div>

                    {{-- Progress Bar แสดงระดับสต็อก --}}
                    @if($max > 0)
                    <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                        <div class="{{ $barColor }} h-2 transition-all duration-1000" style="width: {{ $percent }}%"></div>
                    </div>
                    @endif
                    
                    <div class="mt-3 flex justify-between items-center">
                        <span class="text-xs font-black text-blue-600 bg-blue-50 px-2.5 py-1 rounded-md uppercase tracking-wide">
                            {{ $activeLots->count() }} Lots Available
                        </span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
    // 1. ระบบค้นหา
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let value = this.value.toLowerCase();
        let cards = document.querySelectorAll('.medicine-card');
        cards.forEach(card => {
            card.style.display = card.innerText.toLowerCase().includes(value) ? '' : 'none';
        });
    });

    // 2. ฟังก์ชันยืนยันการลบ
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: `จะลบ "${name}" ออกถาวรนะครับ`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'ใช่, ลบเลย!',
            cancelButtonText: 'ยกเลิก',
            preConfirm: () => {
                const form = document.getElementById('delete-form-' + id);
                if (form) {
                    form.submit();
                } else {
                    Swal.showValidationMessage('ไม่พบฟอร์มสำหรับลบข้อมูล');
                }
            }
        });
    }
</script>

{{-- 3. แจ้งเตือนเมื่อลบสำเร็จ --}}
@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'สำเร็จ!',
                text: "{{ session('success') }}",
                icon: 'success',
                timer: 2500,
                showConfirmButton: false,
                borderRadius: '2.5rem'
            });
        });
    </script>
@endif
@endsection