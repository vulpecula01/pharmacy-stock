@extends('layouts.app')

@section('title', 'แก้ไขข้อมูลยา - MediStock')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8 bg-gray-50/50 min-h-screen">
    
    {{-- ส่วนแจ้งเตือน Success --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 font-semibold rounded-r-xl shadow-sm flex items-center gap-3 animate-fade-in">
            <i class="fas fa-check-circle text-emerald-500 text-xl"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- ส่วนแจ้งเตือน Error --}}
    @if($errors->any())
        <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 text-rose-800 font-semibold rounded-r-xl shadow-sm animate-fade-in">
            <div class="flex items-center gap-3 mb-2">
                <i class="fas fa-exclamation-triangle text-rose-500 text-xl"></i>
                <span class="font-bold">กรุณาตรวจสอบข้อมูลต่อไปนี้:</span>
            </div>
            <ul class="list-disc list-inside pl-6 space-y-1 text-sm text-rose-700/90">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Header ส่วนหัว --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-8 bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
        <div class="flex items-center gap-4">
            <div class="bg-gradient-to-tr from-blue-600 to-blue-500 p-4 rounded-2xl shadow-md shadow-blue-500/20 text-white flex items-center justify-center">
                <i class="fas fa-capsules text-2xl"></i>
            </div>
            <div>
                <span class="text-xs font-bold text-blue-600 uppercase tracking-widest bg-blue-50 px-2.5 py-1 rounded-md">Medicine Management</span>
                <h2 class="text-2xl sm:text-3xl font-black text-gray-800 mt-1">แก้ไขข้อมูลยา</h2>
                <p class="text-gray-500 font-medium text-sm mt-0.5"><i class="fas fa-tag mr-1 text-gray-400"></i> {{ $medicine->name }}</p>
            </div>
        </div>
        <div class="bg-slate-50 px-6 py-4 rounded-2xl border border-slate-100 flex items-center justify-between lg:justify-end gap-6 min-w-[240px]">
            <div class="text-left lg:text-right">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">สต็อกทั้งหมดในคลัง</span>
                <div class="flex items-baseline gap-2 mt-0.5">
                    <span class="text-3xl font-black text-blue-600 tracking-tight">{{ number_format($medicine->stock_master) }}</span>
                    <span class="text-sm font-bold text-slate-500">{{ $medicine->unit_name }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Form หลักสำหรับแก้ไขยา --}}
    <form action="{{ route('medicines.update', $medicine->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            {{-- ฝั่งซ้าย: ข้อมูลทั่วไป + ล็อตยา (2 คอลัมน์) --}}
            <div class="lg:col-span-2 space-y-8">
                
                {{-- 1. ข้อมูลทั่วไป --}}
                <div class="bg-white p-6 sm:p-8 rounded-3xl shadow-sm border border-gray-100/80">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2.5 mb-6">
                        <span class="w-1.5 h-6 bg-blue-600 rounded-full block"></span> ข้อมูลทั่วไปของตัวยา
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">ชื่อตัวยา / เวชภัณฑ์</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                                    <i class="fas fa-file-signature text-sm"></i>
                                </span>
                                <input type="text" name="name" value="{{ $medicine->name }}" class="w-full pl-11 pr-4 py-3.5 border border-gray-200 bg-gray-50/30 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-semibold text-gray-700" required>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">รหัสบาร์โค้ด (Barcode)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                                    <i class="fas fa-barcode text-sm"></i>
                                </span>
                                <input type="text" name="barcode" value="{{ $medicine->barcode }}" class="w-full pl-11 pr-4 py-3.5 border border-gray-200 bg-gray-50/30 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-mono font-medium text-gray-700" required>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">หน่วยนับ</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                                    <i class="fas fa-weight-hanging text-sm"></i>
                                </span>
                                <select name="unit_name" class="w-full pl-11 pr-4 py-3.5 border border-gray-200 bg-gray-50/30 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-semibold text-gray-700 appearance-none" required>
                                    @foreach($units as $u)
                                        <option value="{{ $u->name }}" {{ $medicine->unit_name == $u->name ? 'selected' : '' }}>{{ $u->name }}</option>
                                    @endforeach
                                </select>
                                <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 pointer-events-none">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </span>
                            </div>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">บริษัทผู้จำหน่าย / ผู้ผลิต</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                                    <i class="fas fa-building text-sm"></i>
                                </span>
                                <input type="text" name="company_name" value="{{ $medicine->company_name }}" class="w-full pl-11 pr-4 py-3.5 border border-gray-200 bg-gray-50/30 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-semibold text-gray-700" required>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">หมวดหมู่กลุ่มยา (Category)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                                    <i class="fas fa-layer-group text-sm"></i>
                                </span>
                                <select name="category" class="w-full pl-11 pr-4 py-3.5 border border-gray-200 bg-gray-50/30 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-semibold text-gray-700 appearance-none" required>
                                    <option value="">-- เลือกหมวดหมู่ --</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->name }}" {{ $medicine->category == $cat->name ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 pointer-events-none">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ข้อมูลฉลากยา (ค่าเริ่มต้นสำหรับพิมพ์ตอนจ่ายยาให้คนไข้) --}}
                <div class="bg-white p-6 sm:p-8 rounded-3xl shadow-sm border border-gray-100/80">
                    <div class="flex items-center justify-between mb-6 pb-2 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2.5">
                            <span class="w-1.5 h-6 bg-emerald-600 rounded-full block"></span> ข้อมูลฉลากยา (พิมพ์ตอนจ่ายยา)
                        </h3>
                    </div>
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">วิธีใช้ / ขนาดรับประทาน (ค่าเริ่มต้น)</label>
                            <textarea name="usage_instruction" rows="2" placeholder="เช่น รับประทานครั้งละ 1 เม็ด วันละ 3 ครั้ง หลังอาหาร เช้า กลางวัน เย็น" class="w-full px-4 py-3 border border-gray-200 bg-gray-50/30 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-gray-700">{{ $medicine->usage_instruction }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">สรรพคุณ (ค่าเริ่มต้น)</label>
                            <textarea name="indication" rows="2" placeholder="เช่น แก้เวียนศีรษะ" class="w-full px-4 py-3 border border-gray-200 bg-gray-50/30 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-gray-700">{{ $medicine->indication }}</textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">ผลข้างเคียงสำคัญ (ค่าเริ่มต้น ถ้ามี)</label>
                            <textarea name="side_effect" rows="2" placeholder="เช่น อาจทำให้ง่วงซึม ห้ามขับรถหรือทำงานกับเครื่องจักร" class="w-full px-4 py-3 border border-gray-200 bg-gray-50/30 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-gray-700">{{ $medicine->side_effect }}</textarea>
                        </div>
                        <p class="text-xs text-gray-400 -mt-2">ค่าเหล่านี้จะถูกดึงไปแสดงในฉลากยาอัตโนมัติทุกครั้งที่จ่ายยาตัวนี้ และเภสัชกร/พยาบาลยังสามารถแก้ไขข้อความได้อีกครั้งก่อนพิมพ์ที่หน้าจ่ายยา</p>
                    </div>

                    <div class="mt-6 pt-6 border-t border-dashed border-gray-200">
                        <h4 class="text-sm font-bold text-gray-500 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <span class="w-1.5 h-4 bg-blue-500 rounded-full block"></span> English (สำหรับฉลากยาสองภาษา)
                        </h4>
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Usage instructions (default)</label>
                                <textarea name="usage_instruction_en" rows="2" placeholder="e.g. Take 1 tablet 3 times daily after meals" class="w-full px-4 py-3 border border-gray-200 bg-gray-50/30 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-gray-700">{{ $medicine->usage_instruction_en }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Indication (default)</label>
                                <textarea name="indication_en" rows="2" placeholder="e.g. Relieves dizziness" class="w-full px-4 py-3 border border-gray-200 bg-gray-50/30 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-gray-700">{{ $medicine->indication_en }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Important side effects (default, if any)</label>
                                <textarea name="side_effect_en" rows="2" placeholder="e.g. Causes drowsiness. Do not drive or operate machinery." class="w-full px-4 py-3 border border-gray-200 bg-gray-50/30 rounded-xl focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all text-gray-700">{{ $medicine->side_effect_en }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. รายการล็อตยา --}}
                <div class="bg-white p-6 sm:p-8 rounded-3xl shadow-sm border border-gray-100/80">
                    <div class="flex items-center justify-between mb-6 pb-2 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2.5">
                            <span class="w-1.5 h-6 bg-purple-600 rounded-full block"></span> ตรวจสอบและจัดการล็อตยา
                        </h3>
                        <span class="text-xs font-bold bg-purple-50 text-purple-600 px-3 py-1.5 rounded-full">
                            ทั้งหมด {{ count($medicine->lots) }} ล็อต
                        </span>
                    </div>

                    <div class="space-y-4">
                        @foreach($medicine->lots as $lot)
                        <div class="group bg-white p-5 rounded-2xl border border-gray-200 hover:border-blue-400 hover:shadow-md hover:shadow-blue-500/5 transition-all duration-200">
                            <div class="grid grid-cols-2 md:grid-cols-7 gap-4 items-center">
                                
                                {{-- ชื่อล็อต --}}
                                <div class="col-span-2 md:col-span-1">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-0.5">LOT NO.</span>
                                    <p class="font-extrabold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-lg text-center md:text-left inline-block font-mono text-sm">{{ $lot->lot_number }}</p>
                                </div>

                                {{-- จำนวน --}}
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-0.5">จำนวนรับเข้า</span>
                                    <p class="font-bold text-blue-600 text-lg"><i class="fas fa-boxes text-blue-400/80 text-xs mr-1"></i>{{ number_format($lot->quantity) }}</p>
                                </div>

                                {{-- ราคาต่อหน่วย --}}
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-0.5">ราคา / หน่วย</span>
                                    <p class="font-bold text-slate-700 text-sm"><span class="text-slate-400 text-xs mr-0.5">฿</span>{{ number_format($lot->price_per_unit, 2) }}</p>
                                </div>

                                {{-- ยอดรวมของล็อต --}}
                                <div class="bg-emerald-50/50 px-3 py-2 rounded-xl border border-emerald-100/60">
                                    <span class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest block mb-0.5">มูลค่ารวมล็อต</span>
                                    <p class="font-extrabold text-emerald-600 text-sm">
                                        ฿{{ number_format($lot->quantity * $lot->price_per_unit, 2) }}
                                    </p>
                                </div>

                                {{-- วันที่รับเข้า (กู้คืนส่วนนี้กลับมาเรียบร้อยครับ) --}}
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-0.5">วันที่รับเข้า</span>
                                    <p class="text-xs font-semibold text-blue-500 flex items-center gap-1 mt-1">
                                        <i class="far fa-calendar text-blue-400"></i>
                                        @if(!empty($lot->receive_date) && $lot->receive_date !== '0000-00-00')
                                            {{ \Carbon\Carbon::parse($lot->receive_date)->format('d/m/Y') }}
                                        @else
                                            {{ $lot->created_at ? $lot->created_at->format('d/m/Y') : '-' }}
                                        @endif
                                    </p>
                                </div>

                                {{-- วันหมดอายุ --}}
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-0.5">วันหมดอายุ</span>
                                    @if($lot->expire_date && \Carbon\Carbon::parse($lot->expire_date)->isPast())
                                        <span class="inline-flex items-center gap-1 text-xs font-bold text-rose-600 bg-rose-50 px-2 py-1 rounded-md mt-0.5">
                                            <i class="fas fa-calendar-times text-[10px]"></i> {{ \Carbon\Carbon::parse($lot->expire_date)->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <p class="text-xs font-semibold text-slate-600 flex items-center gap-1 mt-1">
                                            <i class="far fa-calendar-alt text-slate-400"></i>
                                            {{ $lot->expire_date && $lot->expire_date !== '0000-00-00' ? \Carbon\Carbon::parse($lot->expire_date)->format('d/m/Y') : '-' }}
                                        </p>
                                    @endif
                                </div>

                                {{-- โซนปุ่มกดจัดการ --}}
                                <div class="col-span-2 md:col-span-1 flex items-center justify-end gap-2 pt-2 md:pt-0 border-t border-gray-50 md:border-t-0">
                                    <button type="button" 
                                            onclick="openEditModal({{ json_encode($lot) }})"
                                            class="flex-1 md:flex-none p-2.5 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all text-sm font-medium flex items-center justify-center gap-1 shadow-sm shadow-blue-100"
                                            title="แก้ไขล็อตนี้">
                                        <i class="fas fa-edit"></i> <span class="md:hidden">แก้ไข</span>
                                    </button>

                                    <button type="button" 
                                            onclick="confirmDelete({{ $lot->id }}, '{{ $lot->lot_number }}')" 
                                            class="flex-1 md:flex-none p-2.5 bg-rose-50 text-rose-500 rounded-xl hover:bg-rose-500 hover:text-white transition-all text-sm font-medium flex items-center justify-center gap-1 shadow-sm shadow-rose-100"
                                            title="ลบล็อตนี้">
                                        <i class="fas fa-trash-alt"></i> <span class="md:hidden">ลบ</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ฝั่งขวา: สต็อกรวมและเกณฑ์ขั้นต่ำ/สูงสุด (1 คอลัมน์) --}}
            <div class="space-y-6 lg:sticky lg:top-8">
                
                {{-- Card สต็อกรวมดีไซน์โมเดิร์น --}}
                <div class="bg-slate-900 p-6 sm:p-8 rounded-3xl shadow-xl shadow-slate-900/10 text-white relative overflow-hidden">
                    <div class="relative z-10">
                        <span class="text-slate-400 font-bold text-[10px] uppercase tracking-widest bg-slate-800 px-2.5 py-1 rounded-md inline-block mb-3">คลังหลัก (Main Stock)</span>
                        <h4 class="text-slate-400 font-medium text-sm">ยอดสต็อกรวมทุกล็อต</h4>
                        <div class="flex items-baseline gap-2 mt-1">
                            <span class="text-4xl sm:text-5xl font-black text-white tracking-tight">{{ number_format($medicine->stock_master) }}</span>
                            <span class="text-sm font-bold text-slate-400">{{ $medicine->unit_name }}</span>
                        </div>
                        <div class="mt-6 pt-4 border-t border-slate-800 flex items-center gap-2 text-xs text-slate-400">
                            <i class="fas fa-info-circle text-blue-400"></i>
                            <span>ระบบคำนวณยอดรวมจากข้อมูลล็อตให้อัตโนมัติ</span>
                        </div>
                    </div>
                    <div class="absolute -right-6 -bottom-6 text-slate-800/40 text-9xl transform rotate-12 pointer-events-none">
                        <i class="fas fa-box-open"></i>
                    </div>
                </div>

                {{-- Card เกณฑ์การแจ้งเตือน --}}
                <div class="bg-white p-6 sm:p-8 rounded-3xl border border-gray-200/80 shadow-sm space-y-5">
                    <h3 class="text-gray-800 font-extrabold text-base flex items-center gap-2 mb-2">
                        <i class="fas fa-bell text-amber-500"></i> ตั้งค่าเกณฑ์แจ้งเตือนสต็อก
                    </h3>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">เกณฑ์ขั้นต่ำควรมี (Min Stock)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-rose-500">
                                <i class="fas fa-arrow-down-9-1 text-sm"></i>
                            </span>
                            <input type="number" name="min_stock" value="{{ $medicine->min_stock }}" class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl outline-none font-bold text-gray-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wider">เกณฑ์สูงสุดไม่เกิน (Max Stock)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-emerald-500">
                                <i class="fas fa-arrow-up-1-9 text-sm"></i>
                            </span>
                            <input type="number" name="max_stock" value="{{ $medicine->max_stock }}" class="w-full pl-11 pr-4 py-3 border border-gray-200 rounded-xl outline-none font-bold text-gray-700 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all">
                        </div>
                    </div>

                    <div class="pt-4 space-y-3">
                        <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-bold hover:bg-blue-700 shadow-md shadow-blue-500/10 hover:shadow-lg hover:shadow-blue-500/20 active:scale-[0.99] transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i> บันทึกการเปลี่ยนแปลง
                        </button>
                        <a href="{{ route('transfers.index') }}" class="block w-full text-center bg-slate-50 text-slate-600 py-3.5 rounded-2xl font-bold hover:bg-slate-100 transition-all border border-slate-200 text-sm">
                            ยกเลิกและกลับหน้าหลัก
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- Modal หน้าต่าง Pop-up แก้ไขล็อตยา --}}
<div id="editLotModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-3xl p-6 shadow-2xl w-full max-w-md transform scale-95 transition-transform duration-300 mx-4 border border-slate-100">
        <div class="flex items-center justify-between mb-4 pb-4 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <span class="w-1.5 h-5 bg-blue-600 rounded-full block"></span> แก้ไขรายละเอียดล็อตยา
            </h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-50 transition-all">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <form id="editLotForm" class="space-y-4">
            <input type="hidden" id="modal_lot_id">

            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">LOT NUMBER</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                        <i class="fas fa-fingerprint text-xs"></i>
                    </span>
                    <input type="text" id="modal_lot_number" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-mono font-bold text-slate-500 outline-none cursor-not-allowed" readonly>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">จำนวนรับเข้า</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fas fa-boxes text-xs"></i>
                        </span>
                        <input type="text" id="modal_quantity" class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-500 outline-none cursor-not-allowed" readonly>
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">ราคาต่อหน่วย (฿)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-blue-500">
                            <i class="fas fa-tag text-xs"></i>
                        </span>
                        <input type="number" id="modal_price_per_unit" step="0.01" class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none font-bold text-slate-700 transition-all" placeholder="0.00">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">วันที่รับเข้าสินค้า</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                        <i class="far fa-calendar-alt text-xs"></i>
                    </span>
                    <input type="date" id="modal_receive_date" class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">วันหมดอายุ (Expire Date)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                        <i class="fas fa-calendar-times text-xs"></i>
                    </span>
                    <input type="date" id="modal_expire_date" class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl font-semibold text-slate-700 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all">
                </div>
            </div>

            <div class="flex gap-3 pt-3">
                <button type="button" onclick="closeEditModal()" class="flex-1 py-3 border border-slate-200 text-sm font-bold text-slate-500 rounded-xl hover:bg-slate-50 transition-all">ยกเลิก</button>
                <button type="button" onclick="saveLotDataData()" class="flex-1 py-3 bg-blue-600 text-sm font-bold text-white rounded-xl hover:bg-blue-700 shadow-md shadow-blue-500/10 transition-all">บันทึกข้อมูลล็อต</button>
            </div>
        </form>
    </div>
</div>

{{-- Global Delete Form --}}
<form id="global-delete-form" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(id, lotNumber) {
    Swal.fire({
        title: 'ยืนยันการลบล็อตยา?',
        text: "คุณกำลังต้องการที่จะลบล็อต: " + lotNumber + " ออกจากระบบคลังสินค้า",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f43f5e',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'ยืนยันการลบข้อมูล',
        cancelButtonText: 'ยกเลิก',
        customClass: {
            popup: 'rounded-3xl'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let form = document.getElementById('global-delete-form');
            form.action = '/medicine_lots/' + id; 
            form.submit();
        }
    })
}

function openEditModal(lot) {
    const modal = document.getElementById('editLotModal');
    
    document.getElementById('modal_lot_id').value = lot.id;
    document.getElementById('modal_lot_number').value = lot.lot_number;
    document.getElementById('modal_quantity').value = Number(lot.quantity).toLocaleString();
    document.getElementById('modal_price_per_unit').value = lot.price_per_unit;
    
    document.getElementById('modal_receive_date').value = lot.receive_date ? lot.receive_date.substring(0, 10) : '';
    document.getElementById('modal_expire_date').value = lot.expire_date ? lot.expire_date.substring(0, 10) : '';

    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        modal.querySelector('.transform').classList.remove('scale-95');
    }, 10);
}

function closeEditModal() {
    const modal = document.getElementById('editLotModal');
    modal.classList.add('opacity-0');
    modal.querySelector('.transform').classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
    }, 300);
}

function saveLotDataData() {
    const id = document.getElementById('modal_lot_id').value;
    const price = document.getElementById('modal_price_per_unit').value;
    const receiveDate = document.getElementById('modal_receive_date').value;
    const expireDate = document.getElementById('modal_expire_date').value;

    $.ajax({
        url: `/medicine-lots/${id}/update-ajax`, 
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            price_per_unit: price,
            receive_date: receiveDate,
            expire_date: expireDate
        },
        success: function(response) {
            closeEditModal();
            Swal.fire({
                icon: 'success',
                title: 'อัปเดตข้อมูลสำเร็จ',
                text: 'แก้ไขข้อมูลล็อตยาเรียบร้อยแล้ว',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                location.reload();
            });
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: 'ไม่สามารถอัปเดตข้อมูลได้ กรุณาตรวจสอบข้อมูลอีกครั้ง'
            });
        }
    });
}
</script>
@endsection