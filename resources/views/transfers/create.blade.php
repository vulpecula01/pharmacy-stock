@extends('layouts.app')

@section('title', 'เบิกจ่ายยาไปคลังย่อย - MediStock')

@section('content')
<script src="https://cdn.tailwindcss.com"></script>

<div class="max-w-3xl mx-auto mt-10 p-6 bg-white shadow-xl rounded-2xl border-t-8 border-blue-600">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-black text-gray-800 flex items-center gap-2">
            <i class="fas fa-exchange-alt text-blue-600"></i> เบิกจ่ายยา (Master -> Sub)
        </h2>
        <span class="bg-blue-100 text-blue-700 text-xs font-bold px-3 py-1 rounded-full uppercase">Stock Management</span>
    </div>

    <form action="{{ route('transfers.store') }}" method="POST" id="transferForm">
        @csrf
        
        {{-- โหมดสแกน --}}
        <div class="mb-6 bg-gradient-to-br from-blue-50 to-white p-5 rounded-2xl border border-blue-100 shadow-sm">
            <label class="block text-sm font-black text-blue-700 mb-2 flex items-center gap-2">
                <i class="fas fa-barcode"></i> ⚡ โหมดสแกนบาร์โค้ด (ยิงแล้วระบบจะเลือกยาให้อัตโนมัติ)
            </label>
            <input type="text" id="barcode_scanner" 
                class="w-full border-2 border-blue-200 p-4 rounded-xl outline-none focus:border-blue-500 font-mono text-xl shadow-inner transition-all" 
                placeholder="สแกนที่นี่..." autofocus>
        </div>

        {{-- เลือกยา --}}
        <div class="mb-6">
            <label class="block font-bold text-gray-700 mb-2">📦 รายการยาที่ต้องการเบิก</label>
            <select name="medicine_id" id="medicine_select" class="w-full border-2 border-gray-100 p-3 rounded-xl outline-none focus:border-blue-400 bg-gray-50 text-lg font-medium">
                <option value="">-- ค้นหาหรือเลือกชื่อยา --</option>
                @foreach($medicines as $item)
                    {{-- ดึง unit_name จาก DB มาใส่ใน data-unit --}}
                    <option value="{{ $item->id }}" 
                            data-barcode="{{ $item->barcode }}" 
                            data-unit="{{ $item->unit_name }}"> 
                        {{ $item->name }} (คงเหลือในคลังหลัก: {{ $item->stock_master }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-8 grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- ส่งไปที่ --}}
            <div>
                <label class="block font-bold text-gray-600 mb-2 text-sm">📍 ส่งไปที่คลัง</label>
                <select name="to_location" class="w-full border-2 border-gray-100 p-3 rounded-xl bg-white font-bold text-gray-700">
                    <option value="stock_a">🏥 คลัง A (ห้องพยาบาล)</option>
                </select>
            </div>
            
            {{-- จำนวน --}}
            <div>
                <label class="block font-bold text-gray-600 mb-2 text-sm">🔢 จำนวนที่จะเบิก</label>
                <input type="number" name="quantity" id="quantity_input" 
                    class="w-full border-2 border-gray-100 p-3 rounded-xl text-center font-black text-2xl text-blue-600 outline-none focus:border-blue-500" 
                    min="1" required placeholder="0">
            </div>

            {{-- หน่วยนับ --}}
            <div>
                <label class="block font-bold text-gray-600 mb-2 text-sm">📏 หน่วยนับ</label>
                <select name="unit" id="unit_select" class="w-full border-2 border-gray-100 p-3 rounded-xl bg-gray-50 font-black text-center text-lg text-emerald-600 appearance-none pointer-events-none" tabindex="-1">
                    <option value="">-- หน่วย --</option>
                    @foreach($units as $u)
                        <option value="{{ $u->name }}">{{ $u->name }}</option>
                    @endforeach
                </select>
                <p class="text-[10px] text-gray-400 mt-1 text-center">* อ้างอิงตามข้อมูลยา</p>
            </div>
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-2xl font-black text-xl hover:bg-blue-700 shadow-xl shadow-blue-200 transition-all transform active:scale-95 flex justify-center items-center gap-3">
            <i class="fas fa-check-circle text-2xl"></i>
            <span>ยืนยันการโอนย้ายสต็อก</span>
        </button>
    </form>
</div>

<script>
    // ฟังก์ชันช่วยเลือกหน่วยอัตโนมัติ
    function autoSelectUnit(unitName) {
        const unitSelect = document.getElementById('unit_select');
        // รีเซ็ตค่าก่อน
        unitSelect.value = "";
        
        if (!unitName) return;

        for (let i = 0; i < unitSelect.options.length; i++) {
            if (unitSelect.options[i].value === unitName) {
                unitSelect.selectedIndex = i;
                break;
            }
        }
    }

    // ระบบ Barcode
    document.getElementById('barcode_scanner').addEventListener('keydown', function(e) {
        if (e.key === 'Tab' || e.key === 'Enter') {
            const scannedBarcode = this.value.trim();
            if (scannedBarcode === "") return;
            e.preventDefault();
            
            const select = document.getElementById('medicine_select');
            const options = select.querySelectorAll('option');
            let found = false;

            options.forEach(opt => {
                if (opt.getAttribute('data-barcode') === scannedBarcode) {
                    select.value = opt.value;
                    found = true;
                    
                    // ดึง unit_name จาก Attribute และเลือกให้อัตโนมัติ
                    const unitName = opt.getAttribute('data-unit');
                    autoSelectUnit(unitName);

                    this.value = ''; 
                    const qtyInput = document.getElementById('quantity_input');
                    qtyInput.focus();
                    qtyInput.select();

                    // แจ้งเตือนสั้นๆ ว่าเจอแล้ว
                    this.classList.add('bg-green-50', 'border-green-400');
                    setTimeout(() => this.classList.remove('bg-green-50', 'border-green-400'), 500);
                }
            });

            if (!found) {
                alert('ไม่พบข้อมูลยาสำหรับบาร์โค้ดนี้: ' + scannedBarcode);
                this.value = '';
            }
        }
    });

    // เมื่อเลือกยาด้วยมือ
    document.getElementById('medicine_select').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value !== "") {
            const unitName = selectedOption.getAttribute('data-unit');
            autoSelectUnit(unitName);
            document.getElementById('quantity_input').focus();
        } else {
            autoSelectUnit("");
        }
    });
</script>
@endsection