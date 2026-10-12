@extends('layouts.app')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="max-w-5xl mx-auto p-6">
    <div class="mb-8 flex justify-between items-end">
        <div>
            <h1 class="text-3xl font-black text-gray-800 tracking-tight">ระบบนำเข้าข้อมูลยา</h1>
            <p class="text-gray-500 mt-1">อัปโหลดไฟล์ CSV เพื่อเพิ่มรายการยาและ <span class="text-blue-600 font-bold">ต้นทุนรายล็อต</span> เข้าสู่ระบบ</p>
        </div>
        <div class="hidden md:block">
            <span class="text-xs bg-gray-100 text-gray-500 px-3 py-1 rounded-full border border-gray-200">Format: UTF-8 / CSV only</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- ฝั่งซ้าย: ฟอร์มการอัปโหลด --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-xl shadow-gray-100/50">
                <form action="{{ route('pharmacy.import.process') }}" method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf
                    <div id="dropZone" class="border-3 border-dashed border-gray-200 rounded-[2rem] p-12 text-center hover:border-blue-400 hover:bg-blue-50/30 transition-all group relative cursor-pointer">
                        <i class="fas fa-file-csv text-6xl text-gray-300 group-hover:text-blue-500 mb-4 transition-colors"></i>
                        <div class="flex flex-col items-center">
                            <label class="cursor-pointer">
                                <span class="bg-blue-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all inline-block">
                                    <i class="fas fa-search mr-2"></i> เลือกไฟล์จากเครื่อง
                                </span>
                                <input type="file" name="file" id="fileInput" class="hidden" required accept=".csv">
                            </label>
                            <p class="text-sm text-gray-400 mt-4" id="fileName">หรือลากไฟล์ CSV มาวางที่นี่</p>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="mt-6 bg-emerald-50 border border-emerald-100 text-emerald-700 p-4 rounded-2xl flex items-center gap-3 animate-bounce">
                            <i class="fas fa-check-circle text-xl text-emerald-500"></i>
                            <span class="font-bold">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mt-6 p-4 bg-red-50 border border-red-100 text-red-700 rounded-2xl">
                            <p class="font-bold mb-2 flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> พบข้อผิดพลาด:</p>
                            <ul class="list-disc list-inside text-xs space-y-1 opacity-80">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <button type="submit" id="submitBtn" class="w-full mt-8 bg-gray-900 text-white py-4 rounded-2xl font-bold hover:bg-blue-600 shadow-xl transition-all flex items-center justify-center gap-3 text-lg group">
                        <span id="btnText" class="flex items-center gap-2">
                            เริ่มนำเข้าข้อมูล <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                        </span>
                        <span id="btnLoader" class="hidden items-center gap-2">
                            <i class="fas fa-spinner fa-spin"></i> กำลังวิเคราะห์ข้อมูลและสร้างล็อตยา...
                        </span>
                    </button>
                </form>
            </div>

            {{-- ตารางแสดงผลล่าสุด --}}
            @if(session('recent'))
            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm overflow-hidden animate-fade-in">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-history text-blue-500"></i> รายการที่เพิ่งนำเข้า ({{ count(session('recent')) }} รายการ)
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead>
                            <tr class="bg-gray-50 text-gray-500 uppercase text-[10px] tracking-widest">
                                <th class="px-4 py-3">รายละเอียดเงิน/ยา</th>
                                <th class="px-4 py-3 text-center">ราคาต้นทุน</th>
                                <th class="px-4 py-3 text-center">นำเข้าเพิ่ม</th>
                                <th class="px-4 py-3">วันหมดอายุ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach(session('recent') as $item)
                            <tr class="hover:bg-blue-50/30 transition-colors">
                                <td class="px-4 py-4">
                                    <div class="font-bold text-gray-800">{{ $item->name }}</div>
                                    <div class="text-[10px] font-mono text-gray-400">{{ $item->barcode }}</div>
                                </td>
                                <td class="px-4 py-4 text-center font-black text-emerald-600">
                                    ฿{{ number_format($item->price_per_unit, 2) }}
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded-lg font-bold">
                                        +{{ number_format($item->stock_master) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-gray-500 text-xs">
                                    {{ $item->expire_date ?? 'ไม่ระบุ' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        {{-- ฝั่งขวา: คำแนะนำ --}}
        <div class="space-y-4">
            <div class="bg-gray-900 p-6 rounded-[2rem] text-white shadow-2xl relative overflow-hidden">
                <i class="fas fa-file-invoice-dollar absolute -right-4 -bottom-4 text-8xl opacity-10 italic"></i>
                <h3 class="font-bold mb-4 flex items-center gap-2 text-yellow-400">
                    <i class="fas fa-info-circle"></i> ข้อกำหนดสำคัญ
                </h3>
                <ul class="text-sm space-y-4 relative z-10">
                    <li class="flex gap-3">
                        <i class="fas fa-check-square text-blue-400 mt-1"></i>
                        <span>ไฟล์ต้องเป็นนามสกุล <strong>.csv</strong> เท่านั้น</span>
                    </li>
                    <li class="flex gap-3">
                        <i class="fas fa-check-square text-blue-400 mt-1"></i>
                        <span>ราคา (price) จะถูกนำไปคำนวณกำไรตอนจ่ายยา</span>
                    </li>
                    <li class="flex gap-3">
                        <i class="fas fa-check-square text-blue-400 mt-1"></i>
                        <span>หากบาร์โค้ดตรงกับที่มีอยู่ ระบบจะ <strong>เพิ่มสต็อก</strong> เข้าไปในล็อตใหม่</span>
                    </li>
                </ul>
            </div>

            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm">
                <h3 class="font-bold text-gray-800 mb-3 text-sm">โครงสร้าง Header ที่ถูกต้อง:</h3>
                <div class="bg-gray-50 p-4 rounded-xl font-mono text-[11px] text-blue-600 overflow-x-auto border border-blue-50">
                    barcode,name,category,stock,<span class="bg-yellow-200 px-1 font-bold">priceperunit</span>,min,max,expire,company_name,unit
                </div>
                <p class="text-[10px] text-gray-400 mt-3">* ห้ามเว้นวรรคหลังคอมม่า (Comma)</p>
            </div>

            <a href="{{ asset('templates/medicine_template.csv') }}" download 
                class="block w-full py-4 px-4 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-[1.5rem] text-center text-emerald-700 font-bold transition-all flex items-center justify-center gap-2">
                <i class="fas fa-download"></i> ดาวน์โหลดเทมเพลต CSV
            </a>
        </div>
    </div>
</div>

<script>
    const fileInput = document.getElementById('fileInput');
    const fileName = document.getElementById('fileName');
    const dropZone = document.getElementById('dropZone');

    // สไตล์ตอนลากไฟล์มาวาง
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropZone.classList.add('border-blue-500', 'bg-blue-50');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        }, false);
    });

    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        fileInput.files = files;
        updateFileName(files[0].name);
    });

    fileInput.addEventListener('change', function(e) {
        if (e.target.files[0]) updateFileName(e.target.files[0].name);
    });

    function updateFileName(name) {
        fileName.textContent = "เลือกไฟล์แล้ว: " + name;
        fileName.classList.add('text-blue-600', 'font-black');
    }

    // Loader
    document.getElementById('importForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        const text = document.getElementById('btnText');
        const loader = document.getElementById('btnLoader');

        btn.disabled = true;
        btn.classList.replace('bg-gray-900', 'bg-gray-400');
        text.classList.add('hidden');
        loader.classList.remove('hidden');
        loader.classList.add('flex');
    });
</script>

<style>
    .border-3 { border-width: 3px; }
    @keyframes fade-in {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in { animation: fade-in 0.5s ease-out forwards; }
</style>
@endsection