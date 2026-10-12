@extends('layouts.app')

@section('title', 'จ่ายยา (คลังหลัก) - MediStock')

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 5px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="max-w-[1600px] mx-auto h-[calc(100vh-100px)] flex flex-col gap-3 p-2 overflow-hidden bg-gray-50">
    
    {{-- ส่วนที่ 1: ข้อมูลพนักงาน --}}
    <div class="bg-white rounded-2xl p-3 shadow-sm border border-blue-100 flex flex-wrap items-center gap-4 shrink-0">
        <div class="flex items-center gap-3 border-r pr-4">
            <div class="bg-blue-600 text-white w-9 h-9 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-user-md text-sm"></i>
            </div>
            <div>
                <p class="text-[9px] text-gray-400 font-bold uppercase">รหัสพนักงาน (คนไข้)</p>
                <input type="text" id="employee_id_search" class="font-mono font-bold text-blue-600 outline-none w-28 text-sm" placeholder="รหัส...">
            </div>
        </div>

        <div class="flex-1 flex gap-4 min-w-0">
            <div class="min-w-[150px]">
                <p class="text-[9px] text-gray-400 font-bold uppercase">ชื่อ-นามสกุล คนไข้</p>
                <div class="flex items-center gap-2">
                    <p id="display_name" class="font-bold text-gray-700 truncate text-sm">-</p>
                    <div id="new_patient_inputs" class="hidden flex gap-1">
                        <input type="text" id="new_fname" class="text-[11px] border-b border-blue-200 outline-none w-20 bg-blue-50/30 p-1" placeholder="ชื่อ">
                        <input type="text" id="new_lname" class="text-[11px] border-b border-blue-200 outline-none w-20 bg-blue-50/30 p-1" placeholder="นามสกุล">
                    </div>
                </div>
            </div>
            
            <div class="w-32">
                <p class="text-[9px] text-blue-400 font-bold uppercase">แผนก</p>
                <input type="text" id="department_input" class="w-full border-b border-dashed border-gray-200 outline-none text-[13px] text-gray-600 font-medium bg-transparent" placeholder="ระบุแผนก...">
            </div>

            <div class="w-44">
                <p class="text-[9px] text-blue-400 font-bold uppercase">บริษัท</p>
                <select id="company_select" class="w-full border-b border-dashed border-gray-200 outline-none text-[12px] text-gray-600 bg-transparent" onchange="toggleOtherCompany(this)">
                    <option value="Covestro">Covestro</option>
                    <option value="Contractor Covestro">Contractor Covestro</option>
                    <option value="Styrenix Performance materials">Styrenix Performance materials</option>
                    <option value="Contractor Styrenix Performance materials">Contractor Styrenix Performance materials</option>
                    <option value="other">อื่นๆ (ระบุเอง)</option>
                </select>
                <input type="text" id="other_company" class="hidden w-full border-b border-blue-200 outline-none text-[12px] mt-1 bg-blue-50" placeholder="ระบุบริษัท...">
            </div>

            <div class="flex-1 grid grid-cols-2 gap-3 min-w-0">
                <div>
                    <p class="text-[9px] text-red-400 font-bold uppercase italic">ประวัติแพ้ยา</p>
                    <input type="text" id="edit_allergy" class="w-full border-b border-dashed border-gray-200 outline-none text-[13px] text-red-600 font-medium bg-transparent truncate" placeholder="ไม่มี...">
                </div>
                <div>
                    <p class="text-[9px] text-orange-400 font-bold uppercase italic">โรคประจำตัว</p>
                    <input type="text" id="edit_chronic" class="w-full border-b border-dashed border-gray-200 outline-none text-[13px] text-orange-600 font-medium bg-transparent truncate" placeholder="-">
                </div>
            </div>
            <p id="display_name" class="font-bold text-gray-700 truncate text-sm">-</p>
                    {{-- ปุ่มประวัติปุ่มเล็ก --}}
                    <button type="button" onclick="showHistoryModal()" class="w-6 h-6 rounded-full bg-blue-50 text-blue-500 hover:bg-blue-500 hover:text-white transition-all flex items-center justify-center shadow-sm border border-blue-100">
                        <i class="fas fa-history text-[10px]"></i>
                    </button>
        </div>
        <span id="patient_status" class="px-2 py-1 rounded-lg bg-gray-100 text-gray-400 text-[9px] font-black uppercase">Ready</span>
    </div>

    <div class="flex-1 flex flex-row gap-3 min-h-0">
        {{-- ฝั่งซ้าย: ตะกร้ายา --}}
        <div class="w-[450px] flex flex-col gap-3 min-h-0">
            <div class="bg-white p-4 rounded-3xl shadow-sm border border-gray-100 shrink-0">
                <h3 class="font-black text-gray-800 flex items-center gap-2 italic mb-3">
                    <i class="fas fa-barcode text-blue-600"></i> สแกนบาร์โค้ดยา
                </h3>
                <input type="text" id="barcode_scanner" class="w-full bg-blue-50 text-blue-700 p-4 rounded-2xl outline-none ring-2 ring-blue-100 focus:ring-blue-500 font-mono text-lg font-bold" placeholder="ยิงบาร์โค้ดที่นี่...">
                <div id="drug_preview" class="mt-3 bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl p-3 flex flex-col items-center justify-center min-h-[80px]">
                    <p class="text-gray-400 text-xs italic">รอสแกนบาร์โค้ด...</p>
                </div>
                <div id="qty_section" class="hidden mt-3">
                    <div class="flex gap-2">
                        <input type="number" id="qty_input" class="flex-1 p-3 text-center text-xl font-black border-2 border-blue-500 rounded-2xl outline-none" value="1">
                        <button type="button" onclick="addToCart()" class="bg-blue-600 text-white px-8 rounded-2xl font-black shadow-lg">เพิ่ม</button>
                    </div>
                </div>
            </div>

            <div class="flex-1 flex flex-col bg-white rounded-3xl shadow-sm border border-gray-100 min-h-0 overflow-hidden">
                <div class="p-4 bg-gray-900 text-white flex justify-between items-center shrink-0">
                    <span class="font-bold text-xs uppercase tracking-wider">รายการยา</span>
                    <span id="cart_count" class="bg-yellow-400 text-black px-3 py-0.5 rounded-full text-xs font-black">0</span>
                </div>
                <div id="cart_items" class="flex-1 overflow-y-auto p-3 space-y-2 bg-[#F8FAFC] custom-scrollbar"></div>
                <div class="p-3 bg-white border-t">
                    <button type="button" id="btn-main-submit" onclick="confirmAndSubmit()" class="w-full bg-[#10B981] hover:bg-[#059669] text-white py-4 rounded-2xl font-black shadow-lg flex items-center justify-center gap-3">
                        <i class="fas fa-check-double text-xl"></i> <span>ยืนยันบันทึกข้อมูล</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ฝั่งขวา: อาการ & Vital Signs --}}
        <div class="flex-1 flex flex-col gap-3 min-h-0">
            {{-- Vital Signs --}}
            <div class="bg-white p-3 rounded-2xl shadow-sm border border-gray-100 shrink-0 grid grid-cols-5 gap-2">
                <div class="bg-red-50/50 p-2 rounded-xl border border-red-100 text-center">
                    <p class="text-[8px] font-black text-red-400 italic uppercase">Temp</p>
                    <input type="text" id="v_temp" class="w-full bg-transparent border-none text-center outline-none text-sm font-bold text-red-600" placeholder="0.0">
                </div>
                <div class="bg-blue-50/50 p-2 rounded-xl border border-blue-100 text-center">
                    <p class="text-[8px] font-black text-blue-400 italic uppercase">BP</p>
                    <input type="text" id="v_bp" class="w-full bg-transparent border-none text-center outline-none text-sm font-bold text-blue-600" placeholder="0/0">
                </div>
                <div class="bg-green-50/50 p-2 rounded-xl border border-green-100 text-center">
                    <p class="text-[8px] font-black text-green-400 italic uppercase">PR</p>
                    <input type="text" id="v_pr" class="w-full bg-transparent border-none text-center outline-none text-sm font-bold text-green-600" placeholder="0">
                </div>
                <div class="bg-orange-50/50 p-2 rounded-xl border border-orange-100 text-center">
                    <p class="text-[8px] font-black text-orange-400 italic uppercase">RR</p>
                    <input type="text" id="v_rr" class="w-full bg-transparent border-none text-center outline-none text-sm font-bold text-orange-600" placeholder="0">
                </div>
                <div class="bg-purple-50/50 p-2 rounded-xl border border-purple-100 text-center">
                    <p class="text-[8px] font-black text-purple-400 italic uppercase">O2 Sat</p>
                    <input type="text" id="v_o2" class="w-full bg-transparent border-none text-center outline-none text-sm font-bold text-purple-600" placeholder="0">
                </div>
            </div>

            {{-- อาการและวินิจฉัย (จุดที่กู้คืนมา) --}}
            <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 space-y-3">
                <div class="flex flex-col gap-2">
                    <label class="text-[10px] font-black text-blue-500 uppercase italic">มาด้วยอาการอะไร / การวินิจฉัย (Diagnosis)</label>
                    <input type="text" id="diagnosis_input" class="w-full bg-blue-50/50 p-3 rounded-xl text-sm font-bold outline-none border border-blue-100 focus:border-blue-400" placeholder="ระบุอาการสำคัญหรือการวินิจฉัยโรคที่นี่...">
                </div>

                <div class="grid grid-cols-2 gap-3 mt-2">
                    <div class="space-y-2">
                        <select id="illness_type" class="w-full text-xs border border-gray-200 rounded-lg p-2 outline-none">
                            <option value="เกี่ยวกับงาน">เกี่ยวกับงาน</option>
                            <option value="ไม่เกี่ยวกับงาน">ไม่เกี่ยวกับงาน</option>
                        </select>
                        <select id="disposition" class="w-full text-xs border border-gray-200 rounded-lg p-2 outline-none" onchange="checkOther(this)">
                            <option value="กลับไปทำงาน">กลับไปทำงาน</option>
                            <option value="กลับบ้าน">กลับบ้าน</option>
                            <option value="ส่งต่อโรงพยาบาล">ส่งต่อโรงพยาบาล</option>
                            <option value="other">อื่นๆ (โปรดระบุ)</option>
                        </select>
                        <input type="text" id="other_disposition" class="hidden text-xs border border-blue-200 rounded-lg p-2 outline-none bg-blue-50" placeholder="ระบุรายละเอียด..." />
                    </div>
                    <div>
                         <textarea id="procedure" class="w-full h-full min-h-[80px] bg-emerald-50/30 p-2 rounded-xl text-xs outline-none border border-emerald-100" placeholder="การพยาบาลอื่นๆ..."></textarea>
                    </div>
                </div>
            </div>

            {{-- Checkboxes กลุ่มอาการ --}}
            <div class="bg-white p-3 rounded-2xl shadow-sm border border-gray-100 flex-1 overflow-y-auto custom-scrollbar">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    {{-- กลุ่มอาการ --}}
                    <div class="bg-blue-50/30 p-3 rounded-2xl border border-blue-100">
                        <p class="text-[10px] font-black text-blue-500 uppercase mb-2 italic">1. กลุ่มอาการ/ระบบอวัยวะ</p>
                        <div class="grid grid-cols-2 gap-1 text-[11px]">
                            @foreach(['ระบบทางเดินหายใจ', 'ระบบทางเดินอาหาร', 'ระบบกระดูกและกล้ามเนื้อ', 'ระบบโรคหัวใจ หลอดเลือด', 'ระบบประสาทสมอง', 'ระบบอวัยวะสืบพันธุ์/ปัสสาวะ', 'ระบบผิวหนัง', 'หู คอ จมูก'] as $item)
                                <label class="flex items-center gap-1 cursor-pointer hover:bg-white p-1 rounded">
                                    <input type="checkbox" name="symptom_groups" value="{{ $item }}" class="rounded text-blue-600">
                                    <span class="truncate">{{ $item }}</span>
                                </label>
                            @endforeach
                            <div class="col-span-full mt-1 border-t border-dashed border-blue-200 pt-1">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" id="check_other_symptom" onchange="toggleOtherInput('check_other_symptom', 'other_symptom_text')">
                                    <span class="text-blue-400 font-bold">อื่นๆ:</span>
                                    <input type="text" id="other_symptom_text" class="hidden flex-1 border-b border-blue-300 outline-none bg-transparent text-[11px]" placeholder="...">
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        {{-- ประเภทอุบัติเหตุ --}}
                        <div class="bg-red-50/30 p-3 rounded-2xl border border-red-100">
                            <p class="text-[10px] font-black text-red-500 uppercase mb-2 italic">2. ประเภทอุบัติเหตุ</p>
                            <div class="grid grid-cols-1 gap-1 text-[11px]">
                                @foreach(['อุบัติเหตุในงาน (ในเวลา)', 'อุบัติเหตุในงาน (นอกเวลา)'] as $item)
                                    <label class="flex items-center gap-1 cursor-pointer">
                                        <input type="checkbox" name="accident_types" value="{{ $item }}" class="rounded text-red-600">
                                        <span>{{ $item }}</span>
                                    </label>
                                @endforeach
                                <label class="flex items-center gap-2 mt-1">
                                    <input type="checkbox" id="check_other_accident" onchange="toggleOtherInput('check_other_accident', 'other_accident_text')">
                                    <span class="text-red-400 font-bold">อื่นๆ:</span>
                                    <input type="text" id="other_accident_text" class="hidden flex-1 border-b border-red-300 outline-none bg-transparent text-[11px]" placeholder="...">
                                </label>
                            </div>
                        </div>

                        {{-- การบริการ --}}
                        <div class="bg-green-50/30 p-3 rounded-2xl border border-green-100">
                            <p class="text-[10px] font-black text-green-500 uppercase mb-2 italic">3. การบริการ</p>
                            <div class="grid grid-cols-2 gap-1 text-[11px]">
                                @foreach(['ปรึกษา', 'หัตถการ', 'Refer', 'เบิกยา', 'พบแพทย์', 'นอนพักสังเกตุอาการ'] as $item)
                                    <label class="flex items-center gap-1 cursor-pointer">
                                        <input type="checkbox" name="service_types" value="{{ $item }}" class="rounded text-green-600">
                                        <span>{{ $item }}</span>
                                    </label>
                                @endforeach
                                <div class="col-span-full">
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" id="check_other_service" onchange="toggleOtherInput('check_other_service', 'other_service_text')">
                                        <span class="text-green-400 font-bold">อื่นๆ:</span>
                                        <input type="text" id="other_service_text" class="hidden flex-1 border-b border-green-300 outline-none bg-transparent text-[11px]" placeholder="...">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="historyModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[80vh]">
        <div class="p-4 bg-blue-600 text-white flex justify-between items-center shrink-0">
            <h3 class="font-bold flex items-center gap-2"><i class="fas fa-history"></i> ประวัติการรับยา</h3>
            <button onclick="closeHistoryModal()" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/20 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="history_modal_content" class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar bg-gray-50 text-sm">
            <p class="text-center py-10 text-gray-400 italic">เลือกคนไข้เพื่อดูประวัติ</p>
        </div>
    </div>
</div>
<script>
    const masterMedicines = @json($medicines);
    let selectedDrug = null;
    let cart = [];
    let currentPatientId = null; // เพิ่มเพื่อรองรับ Controller ประวัติ

    // --- 1. ระบบค้นหาพนักงานคนไข้ ---
    document.getElementById('employee_id_search').addEventListener('change', function() {
        const empId = this.value.trim();
        if (!empId) {
            currentPatientId = null;
            return;
        }

        fetch("{{ url('/pharmacy/patient') }}/" + empId)
            .then(res => res.json())
            .then(data => {
                const statusSpan = document.getElementById('patient_status');
                const newInputs = document.getElementById('new_patient_inputs');
                const displayName = document.getElementById('display_name');
                const deptField = document.getElementById('department_input');
                const compSelect = document.getElementById('company_select');
                const otherCompInput = document.getElementById('other_company');

                if (data && data.employee_id) {
                    currentPatientId = data.id; // เก็บ PK ของคนไข้ไว้ที่นี่
                    newInputs.classList.add('hidden');
                    displayName.classList.remove('hidden');
                    displayName.innerText = `${data.first_name} ${data.last_name}`;
                    deptField.value = data.department || '';
                    
                    if (data.company) {
                        const options = Array.from(compSelect.options).map(opt => opt.value);
                        if (options.includes(data.company)) {
                            compSelect.value = data.company;
                            otherCompInput.classList.add('hidden');
                        } else {
                            compSelect.value = 'other';
                            otherCompInput.value = data.company;
                            otherCompInput.classList.remove('hidden');
                        }
                    }

                    document.getElementById('edit_allergy').value = data.drug_allergy || 'ไม่มี';
                    document.getElementById('edit_chronic').value = data.chronic_disease || 'ไม่มี';
                    statusSpan.innerText = "OLD PATIENT";
                    statusSpan.className = "px-2 py-1 rounded-lg bg-blue-100 text-blue-600 text-[9px] font-black";
                } else {
                    currentPatientId = null;
                    displayName.classList.add('hidden');
                    newInputs.classList.remove('hidden');
                    deptField.value = '';
                    compSelect.selectedIndex = 0;
                    otherCompInput.classList.add('hidden');
                    document.getElementById('edit_allergy').value = '';
                    document.getElementById('edit_chronic').value = '';
                    statusSpan.innerText = "NEW PATIENT";
                    statusSpan.className = "px-2 py-1 rounded-lg bg-green-100 text-green-600 text-[9px] font-black";
                }
            }).catch(err => {
                console.error(err);
                currentPatientId = null;
            });
    });

    // --- 2. ระบบ Barcode & Cart (แก้ไขให้แสดงจำนวนยา) ---
    document.getElementById('barcode_scanner').addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === 'Tab') {
            if (e.key === 'Tab') e.preventDefault();
            const barcode = this.value.trim();
            if (!barcode) return;

            selectedDrug = masterMedicines.find(m => m.barcode === barcode);
            const preview = document.getElementById('drug_preview');
            const qtySection = document.getElementById('qty_section');

            if (selectedDrug) {
                preview.innerHTML = `
                    <div class="w-full text-center">
                        <p class="text-blue-600 font-black text-xl leading-tight">${selectedDrug.name}</p>
                        <span class="text-[10px] bg-blue-100 text-blue-600 px-2 py-0.5 rounded font-bold uppercase italic">
                            คงเหลือในคลัง: ${selectedDrug.stock_master}
                        </span>
                    </div>`;
                preview.className = "bg-blue-50/50 border-2 border-blue-400 rounded-2xl p-4 flex flex-col items-center justify-center min-h-[100px]";
                qtySection.classList.remove('hidden');
                setTimeout(() => {
                    const qi = document.getElementById('qty_input');
                    qi.focus(); qi.select();
                }, 10);
                this.value = '';
            } else {
                Swal.fire('ไม่พบยา', `บาร์โค้ด ${barcode} ไม่ถูกต้อง`, 'error');
                this.value = '';
                resetPreview();
            }
        }
    });

    function addToCart() {
        const qtyInput = document.getElementById('qty_input');
        const qty = parseInt(qtyInput.value);
        
        if (!selectedDrug || qty < 1) return;
        
        if (qty > selectedDrug.stock_master) { 
            Swal.fire('สต็อกไม่พอ', `ในคลังมีแค่ ${selectedDrug.stock_master}`, 'warning'); 
            return; 
        }

        const existing = cart.find(item => item.id === selectedDrug.id);
        if (existing) {
            existing.qty += qty;
        } else {
            // มั่นใจว่ามีค่า id, name, qty ครบถ้วน
            cart.push({ 
                id: selectedDrug.id, 
                name: selectedDrug.name, 
                barcode: selectedDrug.barcode, 
                qty: qty 
            });
        }
        
        renderCart(); 
        resetPreview(); 
        document.getElementById('barcode_scanner').focus();
    }

    function resetPreview() {
        selectedDrug = null;
        document.getElementById('drug_preview').innerHTML = `<p class="text-gray-400 text-sm italic">รอสแกนบาร์โค้ด...</p>`;
        document.getElementById('drug_preview').className = "bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl p-4 flex flex-col items-center justify-center min-h-[100px]";
        document.getElementById('qty_section').classList.add('hidden');
        document.getElementById('qty_input').value = 1;
    }

    function renderCart() {
        const container = document.getElementById('cart_items');
        const countBadge = document.getElementById('cart_count');
        
        countBadge.innerText = cart.length;
        
        if (cart.length === 0) { 
            container.innerHTML = `<p class="text-center py-10 text-gray-300 italic text-xs">ยังไม่มีรายการยา</p>`; 
            return; 
        }

        container.innerHTML = cart.map((item, index) => `
            <div class="bg-white p-3 rounded-2xl border border-blue-50 shadow-sm flex justify-between items-center mb-2">
                <div class="flex-1 min-w-0 pr-2">
                    <p class="text-sm font-bold text-gray-800 truncate">${item.name}</p>
                    <p class="text-[10px] text-gray-400 font-mono italic">${item.barcode || '-'}</p>
                </div>
                <div class="flex items-center gap-4 shrink-0">
                    <span class="font-black text-blue-600 bg-blue-50 px-2 py-1 rounded-lg">x ${item.qty}</span>
                    <button onclick="removeFromCart(${index})" class="text-gray-300 hover:text-red-500 transition-colors">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        `).join('');
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        renderCart();
    }

    // --- 3. ระบบประวัติ (แก้ไขให้เรียกตาม ID จริงของคนไข้) ---
    function showHistoryModal() {
    // 1. ตรวจสอบว่ามี currentPatientId หรือไม่
    if (!currentPatientId) {
        Swal.fire('แจ้งเตือน', 'กรุณาระบุรหัสพนักงานที่ถูกต้องก่อน', 'warning');
        return;
    }

    const modal = document.getElementById('historyModal');
    const content = document.getElementById('history_modal_content');
    
    // แสดง Modal และ Loader ระหว่างรอข้อมูล
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    content.innerHTML = `
        <div class="py-20 text-center text-gray-400">
            <i class="fas fa-spinner fa-spin text-3xl mb-4 text-blue-500"></i>
            <p>กำลังโหลดประวัติการรับยา...</p>
        </div>`;

    // 2. เรียก API ไปที่ getPatientHistory (ตรวจสอบ Route ใน web.php ให้ตรงกัน)
    const fetchUrl = "{{ url('/pharmacy/patient-history') }}/" + currentPatientId;

    fetch(fetchUrl)
        .then(res => {
            if (!res.ok) throw new Error("Server Response Error: " + res.status);
            return res.json();
        })
        .then(res => {
            // 3. ตรวจสอบโครงสร้าง Response (success: true และมีข้อมูลใน dispensing_records)
            if (res.success && res.dispensing_records && res.dispensing_records.length > 0) {
                
                content.innerHTML = res.dispensing_records.map(rec => {
                    // จัดรูปแบบวันที่
                    const date = new Date(rec.created_at).toLocaleString('th-TH', {
                        day:'numeric', month:'short', year:'2-digit', hour:'2-digit', minute:'2-digit'
                    });

                    // กำหนดสี Badge ตามประเภทการป่วย
                    const typeClass = rec.illness_type === 'ในงาน' 
                        ? 'bg-red-100 text-red-600 border-red-200' 
                        : 'bg-blue-100 text-blue-600 border-blue-200';

                    return `
                        <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm mb-3 text-left">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex flex-col gap-1">
                                    <span class="text-[10px] font-bold text-blue-500"><i class="far fa-clock mr-1"></i>${date} น.</span>
                                    <span class="${typeClass} text-[9px] px-2 py-0.5 rounded-full font-bold border w-fit">
                                        ${rec.illness_type || 'ทั่วไป'}
                                    </span>
                                </div>
                                <div class="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full border border-emerald-100 text-[11px] font-black">
                                    ${rec.amount > 0 ? 'x' + rec.amount : 'ปรึกษา'}
                                </div>
                            </div>

                            <div class="mb-3">
                                <h4 class="text-sm font-black text-gray-800">
                                    ${rec.medicine ? rec.medicine.name : (rec.medicine_name || 'ไม่รับยา')}
                                </h4>
                                <p class="text-[11px] text-gray-500 mt-1 italic line-clamp-2">📌 DX: ${rec.diagnosis || '-'}</p>
                            </div>

                            <div class="grid grid-cols-5 border-t border-gray-50 pt-3 text-center">
                                <div><p class="text-[8px] text-gray-400 font-bold uppercase">T</p>
                                     <p class="text-[10px] font-black text-red-500">${rec.temp || '-'}</p></div>
                                <div><p class="text-[8px] text-gray-400 font-bold uppercase">BP</p>
                                     <p class="text-[10px] font-black text-blue-600">${rec.bp || '-'}</p></div>
                                <div><p class="text-[8px] text-gray-400 font-bold uppercase">PR</p>
                                     <p class="text-[10px] font-black text-emerald-500">${rec.pr || '-'}</p></div>
                                <div><p class="text-[8px] text-gray-400 font-bold uppercase">RR</p>
                                     <p class="text-[10px] font-black text-orange-500">${rec.rr || '-'}</p></div>
                                <div><p class="text-[8px] text-gray-400 font-bold uppercase">O2</p>
                                     <p class="text-[10px] font-black text-purple-600">${rec.o2_sat || '-'}</p></div>
                            </div>

                            <div class="mt-2 text-right">
                                <span class="text-[9px] text-gray-300 italic">ผู้จ่าย: ${rec.issued_by || '-'}</span>
                            </div>
                        </div>`;
                }).join('');
            } else {
                content.innerHTML = `
                    <div class="py-20 text-center text-gray-400 italic">
                        <i class="fas fa-folder-open text-3xl mb-2"></i>
                        <p>ไม่พบประวัติการรับยาของพนักงานท่านนี้</p>
                    </div>`;
            }
        })
        .catch(err => {
            console.error("Fetch Error:", err);
            content.innerHTML = `
                <div class="py-20 text-center text-red-500">
                    <i class="fas fa-exclamation-triangle text-3xl mb-2"></i>
                    <p class="font-bold">เกิดข้อผิดพลาดในการดึงข้อมูล</p>
                    <p class="text-[10px]">${err.message}</p>
                </div>`;
        });
}
    function closeHistoryModal() {
        document.getElementById('historyModal').classList.add('hidden');
    }

    // --- 4. ฟังก์ชันควบคุม UI อื่นๆ (คงเดิม) ---
    function toggleOtherInput(checkboxId, inputId) {
        const cb = document.getElementById(checkboxId);
        const input = document.getElementById(inputId);
        if (cb && input) {
            if (cb.checked) { input.classList.remove('hidden'); input.focus(); }
            else { input.classList.add('hidden'); input.value = ''; }
        }
    }

    function toggleOtherCompany(select) {
        const other = document.getElementById('other_company');
        if (select.value === 'other') {
            other.classList.remove('hidden'); other.focus();
        } else {
            other.classList.add('hidden'); other.value = '';
        }
    }

    function checkOther(select) {
        const other = document.getElementById('other_disposition');
        if (select.value === 'other') {
            other.classList.remove('hidden'); other.focus();
        } else {
            other.classList.add('hidden'); other.value = '';
        }
    }

    // --- 5. ยืนยันและการส่งข้อมูล ---
    function confirmAndSubmit() {
        const empId = document.getElementById('employee_id_search').value;
        if (!empId) {
            Swal.fire('ข้อมูลไม่ครบ', 'กรุณาระบุรหัสคนไข้', 'warning');
            return;
        }

        let message = "ยืนยันการบันทึกข้อมูล";
        if (cart.length === 0) {
            message = "ตรวจพบว่าไม่มีการจ่ายยา ต้องการบันทึกเฉพาะประวัติอาการ ใช่หรือไม่?";
        }

        let staffOptions = '<option value="">-- เลือกผู้จ่ายยา/ผู้บันทึก --</option>';
        @foreach($staffs as $staff)
            staffOptions += `<option value="{{ $staff->name }}">{{ $staff->name }}</option>`;
        @endforeach

        Swal.fire({
            title: 'ยืนยันการบันทึก',
            text: message,
            html: `<select id="swal_staff_name" class="swal2-input !w-full">${staffOptions}</select>`,
            showCancelButton: true,
            confirmButtonText: 'บันทึก',
            preConfirm: () => {
                const name = Swal.getPopup().querySelector('#swal_staff_name').value;
                if (!name) Swal.showValidationMessage(`กรุณาเลือกผู้บันทึก`);
                return { staffName: name };
            }
        }).then((res) => {
            if (res.isConfirmed) submitData(res.value.staffName);
        });
    }

    function submitData(staffName) {
        const btn = document.getElementById('btn-main-submit');
        
        let finalCompany = document.getElementById('company_select').value;
        if (finalCompany === 'other') finalCompany = document.getElementById('other_company').value.trim() || 'อื่นๆ';

        let finalDisp = document.getElementById('disposition').value;
        if (finalDisp === 'other') finalDisp = document.getElementById('other_disposition').value.trim() || 'อื่นๆ';

        const symptomGroups = [];
        document.querySelectorAll('input[name="symptom_groups"]:checked').forEach(cb => symptomGroups.push(cb.value));
        if (document.getElementById('check_other_symptom')?.checked) {
            const otherVal = document.getElementById('other_symptom_text')?.value;
            if(otherVal) symptomGroups.push("อื่นๆ: " + otherVal);
        }

        const accidentTypes = [];
        document.querySelectorAll('input[name="accident_types"]:checked').forEach(cb => accidentTypes.push(cb.value));
        if (document.getElementById('check_other_accident')?.checked) {
            const otherVal = document.getElementById('other_accident_text')?.value;
            if(otherVal) accidentTypes.push("อื่นๆ: " + otherVal);
        }

        const serviceTypes = [];
        document.querySelectorAll('input[name="service_types"]:checked').forEach(cb => serviceTypes.push(cb.value));
        if (document.getElementById('check_other_service')?.checked) {
            const otherVal = document.getElementById('other_service_text')?.value;
            if(otherVal) serviceTypes.push("อื่นๆ: " + otherVal);
        }

        const payload = {
            employee_id: document.getElementById('employee_id_search').value,
            first_name: !document.getElementById('new_patient_inputs').classList.contains('hidden') ? document.getElementById('new_fname').value : document.getElementById('display_name').innerText.split(' ')[0],
            last_name: !document.getElementById('new_patient_inputs').classList.contains('hidden') ? document.getElementById('new_lname').value : (document.getElementById('display_name').innerText.split(' ')[1] || '-'),
            department: document.getElementById('department_input').value,
            company: finalCompany,
            drug_allergy: document.getElementById('edit_allergy').value,
            chronic_disease: document.getElementById('edit_chronic').value,
            diagnosis: document.getElementById('diagnosis_input').value || '-',// ปรับให้ดึงจาก input ที่ระบุ
            symptom_groups: symptomGroups.join(', '),
            accident_types: accidentTypes.join(', '),
            service_types: serviceTypes.join(', '),
            disposition: finalDisp,
            v_temp: document.getElementById('v_temp').value,
            v_bp: document.getElementById('v_bp').value,
            v_pr: document.getElementById('v_pr').value,
            v_rr: document.getElementById('v_rr').value,
            v_o2: document.getElementById('v_o2').value,
            illness_type: document.getElementById('illness_type').value,
            procedure: document.getElementById('procedure').value,
            issued_by: staffName,
            cart_data: cart
        };

        btn.disabled = true;
        fetch("{{ route('pharmacy.store') }}", {
            method: "POST",
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                Swal.fire('สำเร็จ', 'บันทึกเรียบร้อย', 'success').then(() => location.reload());
            } else {
                throw new Error(data.message);
            }
        })
        .catch(err => {
            Swal.fire('ผิดพลาด', err.message, 'error');
            btn.disabled = false;
        });
    }

    window.onload = () => document.getElementById('employee_id_search').focus();
</script>
@endsection