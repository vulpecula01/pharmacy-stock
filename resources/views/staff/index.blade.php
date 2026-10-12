@extends('layouts.app')

@section('title', 'จัดการรายชื่อเจ้าหน้าที่ - MediStock')

@section('content')
<div class="max-w-4xl mx-auto p-4">
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 bg-gradient-to-r from-blue-600 to-blue-500 text-white">
            <h2 class="text-xl font-bold flex items-center gap-2">
                <i class="fas fa-user-nurse"></i> จัดการรายชื่อผู้จ่ายยา (พยาบาล/เจ้าหน้าที่)
            </h2>
            <p class="text-blue-100 text-xs mt-1">เพิ่มหรือลบรายชื่อเจ้าหน้าที่สำหรับเลือกในหน้าจ่ายยา</p>
        </div>

        <div class="p-6">
            {{-- ฟอร์มเพิ่มรายชื่อ --}}
            <form action="{{ route('staff.store') }}" method="POST" class="flex gap-3 mb-8">
                @csrf
                <div class="flex-1">
                    <input type="text" name="name" 
                        class="w-full bg-gray-50 border-2 border-gray-100 rounded-2xl p-3 outline-none focus:border-blue-400 transition-all text-sm font-bold" 
                        placeholder="ชื่อ-นามสกุล เจ้าหน้าที่..." required>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 rounded-2xl font-bold shadow-lg transition-transform active:scale-95 flex items-center gap-2">
                    <i class="fas fa-plus"></i> เพิ่มรายชื่อ
                </button>
            </form>

            {{-- ตารางรายชื่อ --}}
            <div class="space-y-3">
                <h3 class="text-gray-400 text-[10px] font-black uppercase tracking-widest mb-2">รายชื่อปัจจุบัน</h3>
                @forelse($staffs as $staff)
                <div class="flex justify-between items-center bg-gray-50 p-4 rounded-2xl border border-gray-100 hover:border-blue-200 transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-sm text-blue-500 font-bold">
                            {{ mb_substr($staff->name, 0, 1) }}
                        </div>
                        <span class="font-bold text-gray-700">{{ $staff->name }}</span>
                    </div>
                    <form action="{{ route('staff.destroy', $staff->id) }}" method="POST" onsubmit="return confirm('ยืนยันการลบรายชื่อนี้?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-gray-300 group-hover:text-red-500 p-2 transition-colors">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </div>
                @empty
                <div class="text-center py-10 text-gray-400 italic">
                    <i class="fas fa-user-slash text-3xl mb-2 block"></i>
                    ยังไม่มีรายชื่อเจ้าหน้าที่ในระบบ
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection