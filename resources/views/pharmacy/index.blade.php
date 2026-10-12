@extends('layouts.app')

@section('title', 'จ่ายยา (คลังหลัก) - MediStock')

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 8px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>

@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="max-w-[1700px] mx-auto min-h-screen flex flex-col gap-5 p-4 bg-gray-50 pb-24 relative">
    
    {{-- 🔵 ส่วนที่ 1: ข้อมูลพนักงาน --}}
    <div class="bg-white rounded-2xl p-5 shadow-md border border-blue-100 flex flex-wrap items-center gap-6">
        <div class="flex items-center gap-4 border-r border-gray-200 pr-6">
            <div class="bg-blue-600 text-white w-12 h-12 rounded-xl flex items-center justify-center shadow-lg shadow-blue-100">
                <i class="fas fa-user-md text-2xl"></i>
            </div>
            <div>
                <p class="text-sm font-black text-blue-600 uppercase tracking-wider mb-0.5">รหัสพนักงาน (คนไข้)</p>
                <input type="text" id="employee_id_search" class="font-mono font-black text-blue-700 outline-none w-44 text-2xl border-b-2 border-transparent focus:border-blue-500 transition-all" placeholder="กรอกรหัส...">
            </div>
        </div>

        <div class="flex-1 flex gap-6 min-w-0 flex-wrap items-center">
            {{-- ข้อมูลชื่อ-นามสกุล (TH / EN) --}}
            <div class="min-w-[380px]">
                <p class="text-sm font-black text-gray-500 uppercase tracking-wider mb-1">ชื่อ-นามสกุล คนไข้</p>
                <div class="flex flex-col gap-1">
                    <div id="display_name_container" class="flex items-center gap-2">
                        <p id="display_name" class="font-black text-gray-900 text-2xl">-</p>
                    </div>
                    
                    <div id="new_patient_inputs" class="hidden flex flex-col gap-2 bg-blue-50/40 p-3 rounded-xl border border-blue-100">
                        <div class="flex gap-2 items-center">
                            <span class="text-sm text-blue-600 font-black min-w-[30px]">TH:</span>
                            <select id="new_prefix_th" class="text-base font-bold border-b-2 border-blue-200 outline-none w-24 bg-transparent py-0.5">
                                <option value="นาย">นาย</option>
                                <option value="นาง">นาง</option>
                                <option value="นางสาว">นางสาว</option>
                            </select>
                            <input type="text" id="new_fname" class="text-base font-bold border-b-2 border-blue-200 outline-none w-36 bg-transparent p-0.5" placeholder="ชื่อ (ไทย)">
                            <input type="text" id="new_lname" class="text-base font-bold border-b-2 border-blue-200 outline-none w-36 bg-transparent p-0.5" placeholder="นามสกุล (ไทย)">
                        </div>
                        <div class="flex gap-2 items-center">
                            <span class="text-sm text-gray-500 font-black min-w-[30px]">EN:</span>
                            <select id="new_prefix_en" class="text-base font-bold border-b-2 border-gray-200 outline-none w-24 bg-transparent text-gray-600 py-0.5">
                                <option value="Mr.">Mr.</option>
                                <option value="Mrs.">Mrs.</option>
                                <option value="Ms.">Ms.</option>
                            </select>
                            <input type="text" id="new_name_en" class="text-base font-bold border-b-2 border-gray-200 outline-none w-36 bg-transparent p-0.5 text-gray-700" placeholder="First Name">
                            <input type="text" id="new_surname_en" class="text-base font-bold border-b-2 border-gray-200 outline-none w-36 bg-transparent p-0.5 text-gray-700" placeholder="Surname">
                        </div>
                    </div>
                </div>
            </div>

            {{-- เพศ --}}
            <div class="w-28 border-l border-gray-200 pl-4">
                <p class="text-sm font-black text-gray-500 uppercase tracking-wider">เพศ</p>
                <p id="display_gender" class="font-black text-gray-800 text-lg mt-1">-</p>
                <select id="new_gender" class="hidden text-base font-bold border-b-2 border-blue-200 outline-none bg-transparent w-full mt-1 py-0.5">
                    <option value="">เลือกเพศ</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>

            {{-- วันเกิด / อายุ --}}
            <div class="min-w-[220px] flex flex-col border-l border-gray-200 pl-4">
                <p class="text-sm font-black text-gray-500 uppercase tracking-wider mb-1">วันเกิด / อายุ</p>
                <div class="flex flex-col gap-1">
                    <input type="date" id="birth_date" onchange="calculateAge()" class="text-base font-black border-b-2 border-dashed border-gray-300 outline-none bg-transparent text-gray-700 py-0.5">
                    <p id="display_age" class="text-base font-black text-blue-600 mt-0.5">-</p>
                </div>
            </div>

            {{-- แผนก --}}
            <div class="w-48 border-l border-gray-200 pl-4">
                <p class="text-sm font-black text-blue-500 uppercase tracking-wider">แผนก</p>
                <input type="text" id="department_input" class="w-full border-b-2 border-dashed border-gray-200 focus:border-blue-500 outline-none text-lg text-gray-800 font-bold bg-transparent py-0.5 mt-0.5" placeholder="ระบุแผนก...">
            </div>

            {{-- บริษัท --}}
            <div class="w-64 border-l border-gray-200 pl-4">
                <p class="text-sm font-black text-blue-500 uppercase tracking-wider">(บริษัท)</p>
                <select id="company_select" class="w-full border-b-2 border-dashed border-gray-200 focus:border-blue-500 outline-none text-base text-gray-800 font-bold bg-transparent py-0.5 mt-1" onchange="toggleOtherCompany(this)">
                    <option value="Covestro">Covestro</option>
                    <option value="Contractor Covestro">Contractor Covestro</option>
                    <option value="Styrenix Performance materials">Styrenix Performance materials</option>
                    <option value="Contractor Styrenix Performance materials">Contractor Styrenix Performance materials</option>
                    <option value="other">อื่นๆ (ระบุเอง)</option>
                </select>
                <input type="text" id="other_company" class="hidden w-full border-b-2 border-blue-400 outline-none text-base text-gray-800 font-bold mt-1.5 p-1 bg-blue-50 rounded" placeholder="ระบุบริษัท...">
            </div>

            {{-- ประวัติแพ้ยา / โรคประจำตัว --}}
            <div class="flex-1 grid grid-cols-2 gap-4 min-w-[350px] border-l border-gray-200 pl-4">
                <div>
                    <p class="text-sm font-black text-red-500 uppercase tracking-wider italic mb-0.5">⚠️ ประวัติแพ้ยา</p>
                    <input type="text" id="edit_allergy" class="w-full border-b-2 border-dashed border-red-100 focus:border-red-400 outline-none text-lg text-red-600 font-black bg-transparent truncate py-0.5" placeholder="ไม่มี...">
                </div>
                <div>
                    <p class="text-sm font-black text-orange-500 uppercase tracking-wider italic mb-0.5">📋 โรคประจำตัว</p>
                    <input type="text" id="edit_chronic" class="w-full border-b-2 border-dashed border-orange-100 focus:border-orange-400 outline-none text-lg text-orange-600 font-black bg-transparent truncate py-0.5" placeholder="-">
                </div>
            </div>
            
            <button type="button" onclick="showHistoryModal()" class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center shadow-md border border-blue-100 shrink-0" title="ดูประวัติการรับยา">
                <i class="fas fa-history text-lg"></i>
            </button>
        </div>

        <span id="patient_status" class="px-3 py-1.5 rounded-xl bg-gray-100 text-gray-500 text-sm font-black uppercase tracking-wider">Ready</span>
    </div>

    {{-- 🟡 คิว Draft ที่บันทึกไว้ก่อน (รอทำต่อ/ยกเลิก) เช่น คนไข้รอปรึกษาหมอก่อน แล้วมีคนอื่นมาต่อคิว --}}
    <div id="draft_queue_panel" class="hidden bg-amber-50 border-2 border-amber-200 rounded-2xl p-4 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-base font-black text-amber-700 flex items-center gap-2">
                <i class="fas fa-clock text-lg"></i> คิว Draft ที่บันทึกไว้ก่อน (รอทำต่อ)
            </h3>
            <span id="draft_queue_count" class="bg-amber-400 text-white px-3 py-1 rounded-full text-sm font-black">0 คิว</span>
        </div>
        <div id="draft_queue_list" class="flex flex-wrap gap-3"></div>
    </div>

    {{-- Layout หลักส่วนล่าง --}}
    <div class="w-full grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
        
        {{-- 🟢 ฝั่งซ้าย: โซนสแกนบาร์โค้ด และการควบคุม Action --}}
        <div class="lg:col-span-4 flex flex-col gap-4">
            
            {{-- บล็อกสแกนยา --}}
            <div class="bg-white p-5 rounded-2xl shadow-md border border-gray-100">
                <h3 class="text-lg font-black text-gray-800 flex items-center gap-2 italic mb-3">
                    <i class="fas fa-barcode text-2xl text-blue-600"></i> สแกนบาร์โค้ดยา (Barcode Scanner)
                </h3>
                <input type="text" id="barcode_scanner" class="w-full bg-blue-50/70 text-blue-800 p-5 rounded-xl outline-none ring-2 ring-blue-100 focus:ring-blue-500 font-mono text-3xl font-black transition-all text-center placeholder:text-gray-400 placeholder:text-lg placeholder:font-sans" placeholder="คลิกที่นี่แล้วสแกนบาร์โค้ดยา...">

                {{-- 🔍 หรือค้นหาจากชื่อยา (พิมพ์บางส่วนของชื่อ) เช่น พิมพ์ "para" จะเจอ Paracetamol 500mg, 1000mg ให้เลือก --}}
                <div class="relative mt-3">
                    <div class="flex items-center gap-2 mb-1">
                        <i class="fas fa-search text-gray-400 text-sm"></i>
                        <span class="text-sm font-black text-gray-500">หรือค้นหาจากชื่อยา (พิมพ์บางส่วนของชื่อ)</span>
                    </div>
                    <input type="text" id="drug_name_search" autocomplete="off" class="w-full bg-white border-2 border-gray-200 focus:border-blue-400 rounded-xl p-3 outline-none text-base font-bold text-gray-800" placeholder="เช่น พิมพ์ para จะเจอ Paracetamol 500mg, 1000mg...">
                    <div id="drug_name_results" class="hidden absolute z-20 left-0 right-0 mt-1 bg-white border-2 border-blue-200 rounded-xl shadow-xl max-h-64 overflow-y-auto"></div>
                </div>

                <div id="drug_preview" class="mt-4 bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl p-4 flex flex-col items-center justify-center min-h-[100px]">
                    <p class="text-gray-400 text-base italic">รอสแกนบาร์โค้ด...</p>
                </div>
                
                <div id="qty_section" class="hidden mt-4">
                    <div class="flex gap-3">
                        <div class="w-1/3 flex flex-col justify-center text-center">
                            <span class="text-sm font-black text-gray-500 mb-1">ระบุจำนวน</span>
                        </div>
                        <input type="number" id="qty_input" class="w-2/3 p-3 text-center text-4xl font-black border-2 border-blue-500 rounded-xl outline-none text-blue-700 focus:bg-blue-50" value="1" min="1">
                        <button type="button" onclick="addToCart()" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-6 rounded-xl text-xl font-black shadow-lg shadow-blue-100 active:scale-95 transition-all">
                            <i class="fas fa-plus mr-2"></i>เพิ่ม
                        </button>
                    </div>
                </div>
            </div>

            {{-- 🛒 ส่วนจัดการการบันทึกข้อมูล (ปรับปรุงตามภาพ image_5f899d.png) --}}
            <div class="bg-white p-4 rounded-2xl shadow-md border border-gray-100 flex flex-col gap-3">
                <button type="button" onclick="openCartModal()" class="w-full bg-slate-800 hover:bg-slate-900 text-white p-5 rounded-xl font-black flex items-center justify-between transition-all active:scale-[0.98] shadow-md text-lg">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-shopping-basket text-yellow-400 text-2xl"></i>
                        <span>เปิดดูรายการยาในเคสนี้</span>
                    </span>
                    <span id="left_cart_count" class="bg-yellow-400 text-black px-4 py-1.5 rounded-full text-base font-black">0 รายการ</span>
                </button>

                {{-- 📝 บันทึกไว้ก่อน (Draft): เช่น คนไข้รอปรึกษาหมอก่อน ให้คนอื่นมาต่อคิวได้ แต่ยังเก็บสถิติเวลาที่มาไว้ --}}
                <button type="button" onclick="saveDraft()" class="w-full bg-amber-400 hover:bg-amber-500 text-amber-900 p-4 rounded-xl font-black flex items-center justify-center gap-2 transition-all active:scale-[0.98] shadow-md">
                    <i class="fas fa-clock"></i>
                    <span>บันทึกไว้ก่อน (Draft) — ไปเคสอื่นก่อน</span>
                </button>

                {{-- 🔄 เปลี่ยนเป็นปุ่มบันทึกสำหรับเคสไม่รับยา (และล็อกอัตโนมัติหากมียาในตะกร้า) --}}
                <button type="button" id="btn-main-submit" onclick="confirmAndSubmit()" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-5 rounded-xl font-black shadow-lg shadow-emerald-100 flex items-center justify-center gap-2 text-lg transition-all active:scale-[0.99]">
                    <i class="fas fa-comment-medical text-2xl"></i> <span id="main_submit_text">ยืนยันบันทึกข้อมูล (ไม่รับยา)</span>
                </button>
            </div>
        </div>

        {{-- 🟢 ฝั่งขวา: สัญญาณชีพ & การบันทึกอาการ --}}
        <div class="lg:col-span-8 flex flex-col gap-4">
            
            {{-- โซน Vital Signs --}}
            <div class="bg-white p-4 rounded-2xl shadow-md border border-gray-100 grid grid-cols-5 gap-3">
                <div class="bg-red-50/60 p-3 rounded-xl border border-red-100 text-center">
                    <p class="text-sm font-black text-red-600 italic uppercase tracking-wider mb-1">Body Temp (°C)</p>
                    <input type="text" id="v_temp" class="w-full bg-white border border-red-200 rounded-lg p-2 text-center outline-none text-2xl font-black text-red-600 shadow-sm focus:ring-2 focus:ring-red-400" placeholder="0.0">
                </div>
                <div class="bg-blue-50/60 p-3 rounded-xl border border-blue-100 text-center">
                    <p class="text-sm font-black text-blue-600 italic uppercase tracking-wider mb-1">Blood Pressure</p>
                    <input type="text" id="v_bp" class="w-full bg-white border border-blue-200 rounded-lg p-2 text-center outline-none text-2xl font-black text-blue-600 shadow-sm focus:ring-2 focus:ring-blue-400" placeholder="0/0">
                </div>
                <div class="bg-emerald-50/60 p-3 rounded-xl border border-emerald-100 text-center">
                    <p class="text-sm font-black text-emerald-600 italic uppercase tracking-wider mb-1">Pulse Rate (PR)</p>
                    <input type="text" id="v_pr" class="w-full bg-white border border-emerald-200 rounded-lg p-2 text-center outline-none text-2xl font-black text-emerald-600 shadow-sm focus:ring-2 focus:ring-emerald-400" placeholder="0">
                </div>
                <div class="bg-orange-50/60 p-3 rounded-xl border border-orange-100 text-center">
                    <p class="text-sm font-black text-orange-600 italic uppercase tracking-wider mb-1">Respiratory (RR)</p>
                    <input type="text" id="v_rr" class="w-full bg-white border border-orange-200 rounded-lg p-2 text-center outline-none text-2xl font-black text-orange-600 shadow-sm focus:ring-2 focus:ring-orange-400" placeholder="0">
                </div>
                <div class="bg-purple-50/60 p-3 rounded-xl border border-purple-100 text-center">
                    <p class="text-sm font-black text-purple-600 italic uppercase tracking-wider mb-1">O2 Saturation</p>
                    <input type="text" id="v_o2" class="w-full bg-white border border-purple-200 rounded-lg p-2 text-center outline-none text-2xl font-black text-purple-600 shadow-sm focus:ring-2 focus:ring-purple-400" placeholder="0">
                </div>
            </div>

            {{-- โซนบันทึกอาการและวินิจฉัยโรค --}}
            <div class="bg-white p-5 rounded-2xl shadow-md border border-gray-100 space-y-4">
                <div class="flex flex-col gap-2">
                    <label class="text-base font-black text-blue-700 uppercase italic tracking-wider flex items-center gap-1">
                        <i class="fas fa-notes-medical"></i> มาด้วยอาการอะไร / การวินิจฉัยโรค (Diagnosis)
                    </label>
                    <input type="text" id="diagnosis_input" class="w-full bg-blue-50/40 p-5 rounded-xl text-lg font-bold outline-none border border-blue-100 focus:border-blue-400 focus:bg-white transition-all shadow-inner" placeholder="ระบุอาการสำคัญ เหตุผลที่มาโรงพยาบาล หรือการวินิจฉัยโรคที่นี่...">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                    <div class="space-y-3">
                        <label class="block text-sm font-black text-gray-500 uppercase">ประเภทความเจ็บป่วย & การจัดการเคส</label>
                        <select id="illness_type" class="w-full text-base font-bold border-2 border-gray-200 rounded-xl p-3 outline-none focus:border-blue-500 bg-white">
                            <option value="เกี่ยวกับงาน">เกี่ยวกับงาน</option>
                            <option value="ไม่เกี่ยวกับงาน">ไม่เกี่ยวกับงาน</option>
                        </select>
                        <select id="disposition" class="w-full text-base font-bold border-2 border-gray-200 rounded-xl p-3 outline-none focus:border-blue-500 bg-white" onchange="checkOther(this)">
                            <option value="กลับไปทำงาน">กลับไปทำงาน</option>
                            <option value="กลับบ้าน">กลับบ้าน</option>
                            <option value="ส่งต่อโรงพยาบาล">ส่งต่อโรงพยาบาล</option>
                            <option value="other">อื่นๆ (โปรดระบุ)</option>
                        </select>
                        <input type="text" id="other_disposition" class="hidden text-base font-bold border-2 border-blue-300 rounded-xl p-3 outline-none bg-blue-50 w-full" placeholder="ระบุรายละเอียดการจัดการเพิ่มเติม..." />
                    </div>
                    <div class="flex flex-col">
                         <label class="block text-sm font-black text-gray-500 uppercase mb-2">บันทึกการทำหัตถการ / การพยาบาล (Nursing Procedure)</label>
                         <textarea id="procedure" class="w-full flex-1 min-h-[130px] bg-emerald-50/20 p-4 rounded-xl text-base font-bold outline-none border border-emerald-100 focus:border-emerald-400 focus:bg-white shadow-inner" placeholder="ระบุการให้การพยาบาล, เช็ดตัว, ทำแผล หรือหัตถการอื่นๆ..."></textarea>
                    </div>
                </div>
            </div>

            {{-- หมวดหมู่ Checkbox จำแนกกลุ่มอาการ --}}
            <div class="bg-white p-5 rounded-2xl shadow-md border border-gray-100">
                <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                    
                    {{-- 1. ระบบอวัยวะ --}}
                    <div class="bg-blue-50/20 p-4 rounded-2xl border border-blue-100">
                        <p class="text-base font-black text-blue-700 uppercase mb-3 italic tracking-wide"><i class="fas fa-stethoscope mr-1"></i>1. กลุ่มอาการ / ระบบอวัยวะ</p>
                        <div class="grid grid-cols-2 gap-2 text-base font-bold text-gray-700">
                            @foreach(['ระบบทางเดินหายใจ', 'ระบบทางเดินอาหาร', 'ระบบกระดูกและกล้ามเนื้อ', 'ระบบโรคหัวใจ หลอดเลือด', 'ระบบประสาทสมอง', 'ระบบอวัยวะสืบพันธุ์/ปัสสาวะ', 'ระบบผิวหนัง', 'หู คอ จมูก ตา'] as $item)
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-white p-2 rounded-lg border border-transparent hover:border-blue-100 transition-all">
                                    <input type="checkbox" name="symptom_groups" value="{{ $item }}" class="rounded text-blue-600 w-5 h-5">
                                    <span class="truncate text-base">{{ $item }}</span>
                                </label>
                            @endforeach
                            <div class="col-span-full mt-2 border-t border-dashed border-blue-200 pt-2">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" id="check_other_symptom" onchange="toggleOtherInput('check_other_symptom', 'other_symptom_text')" class="w-5 h-5">
                                    <span class="text-blue-600 font-black shrink-0 text-base">อื่นๆ:</span>
                                    <input type="text" id="other_symptom_text" class="hidden flex-1 border-b-2 border-blue-300 outline-none bg-transparent text-base font-bold p-0.5" placeholder="ระบุกลุ่มอาการอื่นๆ...">
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- ขวา: Checkbox อุบัติเหตุและการบริการ --}}
                    <div class="space-y-4">
                        {{-- 2. ประเภทอุบัติเหตุ --}}
                        <div class="bg-red-50/20 p-4 rounded-2xl border border-red-100">
                            <p class="text-base font-black text-red-700 uppercase mb-3 italic tracking-wide"><i class="fas fa-user-injured mr-1"></i>2. ประเภทอุบัติเหตุ (Accident Type)</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-base font-bold text-gray-700">
                                @foreach(['อุบัติเหตุในงาน (ในเวลา)', 'อุบัติเหตุในงาน (นอกเวลา)'] as $item)
                                    <label class="flex items-center gap-2 cursor-pointer hover:bg-white p-2 rounded-lg border border-transparent hover:border-red-100 transition-all">
                                        <input type="checkbox" name="accident_types" value="{{ $item }}" class="rounded text-red-600 w-5 h-5">
                                        <span class="text-base">{{ $item }}</span>
                                    </label>
                                @endforeach
                                <label class="flex items-center gap-2 mt-1 col-span-full">
                                    <input type="checkbox" id="check_other_accident" onchange="toggleOtherInput('check_other_accident', 'other_accident_text')" class="w-5 h-5">
                                    <span class="text-red-600 font-black shrink-0 text-base">อื่นๆ:</span>
                                    <input type="text" id="other_accident_text" class="hidden flex-1 border-b-2 border-red-300 outline-none bg-transparent text-base font-bold p-0.5" placeholder="ระบุอุบัติเหตุอื่นๆ...">
                                </label>
                            </div>
                        </div>

                        {{-- 3. การบริการ --}}
                        <div class="bg-green-50/20 p-4 rounded-2xl border border-green-100">
                            <p class="text-base font-black text-green-700 uppercase mb-3 italic tracking-wide"><i class="fas fa-hand-holding-medical mr-1"></i>3. การบริการ (Service Type)</p>
                            <div class="grid grid-cols-2 gap-2 text-base font-bold text-gray-700">
                                @foreach(['ปรึกษา', 'หัตถการ', 'Refer', 'เบิกยา', 'พบแพทย์', 'นอนพักสังเกตุอาการ'] as $item)
                                    <label class="flex items-center gap-2 cursor-pointer hover:bg-white p-2 rounded-lg border border-transparent hover:border-green-100 transition-all">
                                        <input type="checkbox" name="service_types" value="{{ $item }}" class="rounded text-green-600 w-5 h-5">
                                        <span class="text-base">{{ $item }}</span>
                                    </label>
                                @endforeach
                                <div class="col-span-full border-t border-dashed border-green-200 pt-1.5 mt-1">
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" id="check_other_service" onchange="toggleOtherInput('check_other_service', 'other_service_text')" class="w-5 h-5">
                                        <span class="text-green-600 font-black shrink-0 text-base">อื่นๆ:</span>
                                        <input type="text" id="other_service_text" class="hidden flex-1 border-b-2 border-green-300 outline-none bg-transparent text-base font-bold p-0.5" placeholder="ระบุหัตถการบริการอื่นๆ...">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    {{-- 🛒 ปุ่มลอยเปิดตะกร้ายา (Floating Button) --}}
    <button type="button" onclick="openCartModal()" class="fixed bottom-6 right-6 z-40 bg-gray-900 text-white p-6 rounded-full shadow-2xl hover:scale-110 active:scale-95 transition-all flex items-center justify-center gap-3 border-2 border-yellow-400 group">
        <div class="relative">
            <i class="fas fa-shopping-basket text-3xl text-yellow-400"></i>
            <span id="floating_cart_badge" class="absolute -top-3 -right-3 bg-red-500 text-white text-sm font-black px-2.5 py-0.5 rounded-full border border-white">0</span>
        </div>
        <span class="font-black text-base pr-1 max-w-0 overflow-hidden group-hover:max-w-xs transition-all duration-300 whitespace-nowrap">ดูรายการยาในเคสนี้</span>
    </button>
</div>

{{-- 🚨 Pop-up (Modal) ตะกร้ายาเบิกในเคสนี้ --}}
<div id="cartModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[85vh]">
        <div class="p-5 bg-gray-900 text-white flex justify-between items-center shrink-0">
            <h3 class="font-black text-xl flex items-center gap-2">
                <i class="fas fa-shopping-basket text-yellow-400 text-2xl"></i> รายการยาในเคสนี้ (ตะกร้ายา)
            </h3>
            <span id="modal_cart_count" class="bg-yellow-400 text-black px-4 py-1 rounded-full text-base font-black shadow-md ml-auto mr-4">0</span>
            <button onclick="closeCartModal()" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-white/20 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <div id="modal_cart_items" class="flex-1 overflow-y-auto p-6 space-y-3 bg-slate-50 custom-scrollbar max-h-[550px] min-h-[200px]">
            <p class="text-center py-12 text-gray-400 italic text-base">ยังไม่มีรายการยาในเคสนี้</p>
        </div>
        
        <div class="p-4 bg-white border-t border-gray-100 flex gap-3 shrink-0">
            <button type="button" onclick="closeCartModal()" class="w-1/3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-black rounded-xl transition-colors text-base py-4">
                ปิดหน้าต่าง
            </button>
            <button type="button" onclick="triggerMainSubmitFromModal()" class="w-2/3 bg-emerald-500 hover:bg-emerald-600 text-white py-4 rounded-xl font-black shadow-lg flex items-center justify-center gap-2 text-lg transition-all active:scale-[0.99]">
                <i class="fas fa-check-double text-xl"></i> ยืนยันบันทึกข้อมูล (แบบจ่ายยา)
            </button>
        </div>
    </div>
</div>

{{-- Modal ประวัติ --}}
<div id="historyModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden flex flex-col max-h-[85vh]">
        <div class="p-5 bg-blue-600 text-white flex justify-between items-center shrink-0">
            <h3 class="font-black text-xl flex items-center gap-2"><i class="fas fa-history text-2xl"></i> ประวัติการเข้ารับยาและการรักษา</h3>
            <button onclick="closeHistoryModal()" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-white/20 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="history_modal_content" class="flex-1 overflow-y-auto p-5 space-y-4 custom-scrollbar bg-gray-50 text-base">
            <p class="text-center py-10 text-gray-400 italic">เลือกคนไข้เพื่อดูประวัติ</p>
        </div>
    </div>
</div>

@php
    // เตรียมโลโก้ห้องพยาบาลเป็น data URI ฝังไว้ในหน้าพิมพ์โดยตรง
    // (กันปัญหาโลโก้ไม่ขึ้นตอนพิมพ์ เพราะหน้าต่างพิมพ์เป็นหน้าต่างเปล่าที่เปิดแยกต่างหาก ไม่โหลดไฟล์รูปจากเซิร์ฟเวอร์)
    $labelLogoPath = public_path(config('pharmacy_label.logo_path'));
    $labelLogoDataUri = file_exists($labelLogoPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($labelLogoPath))
        : null;
@endphp
{{-- โค้ดพิมพ์ฉลากยา (isThaiText, escapeForHtml, printDispenseLabels ฯลฯ) แยกเป็นไฟล์กลาง
     ใช้ร่วมกับหน้า "ประวัติรับยาคนไข้" (patients/show.blade.php) เพื่อไม่ให้โค้ดซ้ำกันสองที่ --}}
<script>
    window.PHARMACY_LABEL_CONFIG = {
        hospitalName: @json(config('pharmacy_label.hospital_name')),
        hospitalNameEn: @json(config('pharmacy_label.hospital_name_en')),
        logoDataUri: @json($labelLogoDataUri),
        widthMm: {{ (int) config('pharmacy_label.width_mm', 100) }},
        heightMm: {{ (int) config('pharmacy_label.height_mm', 50) }}
    };
</script>
<script src="{{ asset('js/dispense-label.js') }}"></script>
<script>
    const masterMedicines = @json($medicines);
    let selectedDrug = null;
    let cart = [];
    let currentPatientId = null;
    let currentPatientRaw = null;  // เก็บข้อมูลคนไข้ดิบ (ทั้ง prefix_th/first_name/last_name และ prefix_en/name_en/surname_en) ไว้ใช้ตัดสินใจว่าจะพิมพ์ชื่อบนฉลากเป็นภาษาไทยหรืออังกฤษ
    let lastHistoryRecords = [];   // เก็บผลประวัติการจ่ายยาล่าสุดที่โหลดไว้ ใช้สำหรับปุ่ม "พิมพ์ฉลากซ้ำ"
    let lastHistoryPatientName = '';
    let draftQueue = @json($drafts); // คิว Draft ที่ค้างอยู่ (โหลดจาก server ตอนเปิดหน้า)

    // isThaiText, formatDateThai, escapeForHtml, toEnglishUnit, printDispenseLabels
    // ย้ายไปอยู่ในไฟล์กลาง public/js/dispense-label.js แล้ว (โหลดไว้ก่อนหน้าบล็อกสคริปต์นี้)

    // --- ระบบควบคุม Pop-up ตะกร้ายา ---
    function openCartModal() {
        const modal = document.getElementById('cartModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        renderCart();
    }

    function closeCartModal() {
        document.getElementById('cartModal').classList.add('hidden');
    }

    function triggerMainSubmitFromModal() {
        closeCartModal();
        confirmAndSubmit(true); // ส่ง true แปลว่าบันทึกแบบมียาแน่ๆ
    }

    // --- 1. ระบบค้นหาพนักงานคนไข้ ---
    // แยกออกมาเป็นฟังก์ชันกลาง (ไม่ได้ผูกกับ event โดยตรง) เพื่อให้ปุ่ม "ทำต่อ" จากคิว Draft
    // เรียกใช้โหลดข้อมูลคนไข้แบบเดียวกับตอนกดค้นหาด้วยรหัสพนักงานเองได้
    function lookupPatientByEmpId(empId) {
        const birthInput = document.getElementById('birth_date');
        const displayAge = document.getElementById('display_age');
        const displayGender = document.getElementById('display_gender');
        const newGenderSelect = document.getElementById('new_gender');

        if (!empId) { currentPatientId = null; return Promise.resolve(); }

        return fetch("{{ url('/pharmacy/patient') }}/" + empId)
                .then(res => res.json())
                .then(data => {
                    const statusSpan = document.getElementById('patient_status');
                    const newInputs = document.getElementById('new_patient_inputs');
                    const displayName = document.getElementById('display_name');
                    const deptField = document.getElementById('department_input');
                    const compSelect = document.getElementById('company_select');
                    const otherCompInput = document.getElementById('other_company');

                    if (data && data.employee_id) {
                        currentPatientId = data.id;
                        currentPatientRaw = data;
                        let fullNameText = `${data.prefix_th || ''}${data.first_name || ''} ${data.last_name || ''}`;
                        if(data.name_en) fullNameText += ` (${data.prefix_en || ''}${data.name_en} ${data.surname_en || ''})`;
                        
                        if (displayName) { displayName.innerText = fullNameText; displayName.classList.remove('hidden'); }
                        if (newInputs) newInputs.classList.add('hidden');
                        if (displayGender) { displayGender.innerText = data.gender || '-'; displayGender.classList.remove('hidden'); }
                        if (newGenderSelect) newGenderSelect.classList.add('hidden');
                        if (deptField) deptField.value = data.department || '';
                        if (birthInput) { birthInput.value = data.birth_date || ''; calculateAge(); }

                        if (compSelect && data.company) {
                            const options = Array.from(compSelect.options).map(opt => opt.value);
                            if (options.includes(data.company)) {
                                compSelect.value = data.company;
                                if (otherCompInput) otherCompInput.classList.add('hidden');
                            } else {
                                compSelect.value = 'other';
                                if (otherCompInput) { otherCompInput.value = data.company; otherCompInput.classList.remove('hidden'); }
                            }
                        }

                        document.getElementById('edit_allergy').value = data.drug_allergy || 'ไม่มี';
                        document.getElementById('edit_chronic').value = data.chronic_disease || 'ไม่มี';
                        
                        if (data.drug_allergy && data.drug_allergy !== 'ไม่มี' && data.drug_allergy.trim() !== '-' && data.drug_allergy.trim() !== '') {
                            Swal.fire({
                                title: '⚠️ ตรวจพบประวัติการแพ้ยา!',
                                html: `<div class="bg-red-50 p-4 rounded-2xl border-2 border-red-200 mt-2"><p class="text-red-600 font-black text-2xl uppercase mb-1">แพ้ยา: ${data.drug_allergy}</p></div>`,
                                icon: 'warning',
                                confirmButtonColor: '#ef4444',
                                confirmButtonText: 'รับทราบข้อมูลการแพ้ยา',
                                allowOutsideClick: false 
                            });
                        }
                        statusSpan.innerText = "OLD PATIENT";
                        statusSpan.className = "px-3 py-1.5 rounded-xl bg-blue-100 text-blue-600 text-sm font-black";
                        if (deptField) deptField.focus();
                    } else {
                        currentPatientId = null;
                        currentPatientRaw = null;
                        if (displayName) { displayName.innerText = '-'; displayName.classList.add('hidden'); }
                        if (newInputs) newInputs.classList.remove('hidden');
                        if (displayGender) { displayGender.innerText = '-'; displayGender.classList.add('hidden'); }
                        if (newGenderSelect) { newGenderSelect.value = ''; newGenderSelect.classList.remove('hidden'); }

                        document.getElementById('new_prefix_th').selectedIndex = 0;
                        document.getElementById('new_fname').value = '';
                        document.getElementById('new_lname').value = '';
                        document.getElementById('new_prefix_en').selectedIndex = 0;
                        document.getElementById('new_name_en').value = '';
                        document.getElementById('new_surname_en').value = '';
                        if (deptField) deptField.value = '';
                        if (compSelect) compSelect.selectedIndex = 0;
                        if (otherCompInput) { otherCompInput.value = ''; otherCompInput.classList.add('hidden'); }
                        document.getElementById('edit_allergy').value = '';
                        document.getElementById('edit_chronic').value = '';
                        if (birthInput) birthInput.value = ''; 
                        if (displayAge) displayAge.innerText = "-"; 

                        statusSpan.innerText = "NEW PATIENT";
                        statusSpan.className = "px-3 py-1.5 rounded-xl bg-green-100 text-green-600 text-sm font-black";
                        document.getElementById('new_fname').focus();
                    }
                }).catch(err => { console.error(err); currentPatientId = null; currentPatientRaw = null; });
    }

    document.getElementById('employee_id_search').addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === 'Tab') {
            if (e.key === 'Tab') e.preventDefault();
            lookupPatientByEmpId(this.value.trim());
        }
    });

    // --- 2. ระบบ Barcode & Cart ---
    // ตั้งค่ายาที่เลือกไว้ + แสดง preview + เปิดช่องกรอกจำนวน
    // ใช้ร่วมกันทั้งจากการสแกนบาร์โค้ด และจากการค้นหา/เลือกด้วยชื่อยา
    function selectDrug(drug) {
        selectedDrug = drug;
        const preview = document.getElementById('drug_preview');
        const qtySection = document.getElementById('qty_section');
        if (!drug) { resetPreview(); return; }

        preview.innerHTML = `
            <div class="w-full text-center">
                <p class="text-blue-600 font-black text-3xl leading-tight mb-2">${escapeForHtml(drug.name)}</p>
                <span class="text-sm bg-blue-100 text-blue-700 px-4 py-1.5 rounded-lg font-black uppercase italic">คงเหลือในคลัง: ${drug.stock_master}</span>
            </div>`;
        preview.className = "bg-blue-50/50 border-2 border-blue-400 rounded-xl p-4 flex flex-col items-center justify-center min-h-[100px]";
        qtySection.classList.remove('hidden');
        setTimeout(() => { const qi = document.getElementById('qty_input'); qi.focus(); qi.select(); }, 10);
    }

    document.getElementById('barcode_scanner').addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === 'Tab') {
            if (e.key === 'Tab') e.preventDefault();
            const barcode = this.value.trim();
            if (!barcode) return;

            const found = masterMedicines.find(m => m.barcode === barcode);
            if (found) {
                selectDrug(found);
                this.value = '';
            } else {
                Swal.fire('ไม่พบยา', `บาร์โค้ด ${barcode} ไม่ถูกต้อง`, 'error');
                this.value = ''; resetPreview();
            }
        }
    });

    // --- 2b. ค้นหายาด้วยการพิมพ์ชื่อ (แสดง list ให้เลือกความแรง/ขนาด เช่น พิมพ์ "para" เจอ Paracetamol 500mg, 1000mg) ---
    document.getElementById('drug_name_search').addEventListener('input', function() {
        const q = this.value.trim().toLowerCase();
        const resultsBox = document.getElementById('drug_name_results');
        if (!q) { resultsBox.innerHTML = ''; resultsBox.classList.add('hidden'); return; }

        const matches = masterMedicines.filter(m =>
            (m.name || '').toLowerCase().includes(q) || (m.barcode || '').toLowerCase().includes(q)
        ).slice(0, 8);

        if (matches.length === 0) {
            resultsBox.innerHTML = `<div class="p-3 text-sm text-gray-400 italic">ไม่พบยาที่ตรงกับ "${escapeForHtml(this.value)}"</div>`;
        } else {
            resultsBox.innerHTML = matches.map(m => `
                <div class="p-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-0" onclick="pickDrugFromSearch(${m.id})">
                    <p class="font-black text-gray-800">${escapeForHtml(m.name)}</p>
                    <p class="text-xs text-gray-500 mt-0.5">หน่วย: ${escapeForHtml(m.unit_name || '-')} • คงเหลือ: ${m.stock_master}</p>
                </div>`).join('');
        }
        resultsBox.classList.remove('hidden');
    });

    function pickDrugFromSearch(id) {
        const drug = masterMedicines.find(m => m.id === id);
        if (!drug) return;
        selectDrug(drug);
        document.getElementById('drug_name_search').value = '';
        document.getElementById('drug_name_results').innerHTML = '';
        document.getElementById('drug_name_results').classList.add('hidden');
    }

    // ปิด dropdown รายการค้นหาชื่อยา เมื่อคลิกที่อื่นนอกกล่อง
    document.addEventListener('click', function(e) {
        const box = document.getElementById('drug_name_results');
        const input = document.getElementById('drug_name_search');
        if (box && input && e.target !== input && !box.contains(e.target)) {
            box.classList.add('hidden');
        }
    });

    // --- 2c. คิว Draft: บันทึกไว้ก่อน / ทำต่อ / ยกเลิก ---
    function formatElapsedMinutes(fromDateStr) {
        const from = new Date(fromDateStr);
        if (isNaN(from.getTime())) return '';
        const mins = Math.max(0, Math.floor((Date.now() - from.getTime()) / 60000));
        if (mins < 60) return `${mins} นาที`;
        return `${Math.floor(mins / 60)} ชม. ${mins % 60} นาที`;
    }

    function renderDraftQueue() {
        const panel = document.getElementById('draft_queue_panel');
        const list = document.getElementById('draft_queue_list');
        const countBadge = document.getElementById('draft_queue_count');

        if (!draftQueue || draftQueue.length === 0) {
            panel.classList.add('hidden');
            list.innerHTML = '';
            return;
        }

        panel.classList.remove('hidden');
        countBadge.innerText = `${draftQueue.length} คิว`;
        list.innerHTML = draftQueue.map(d => `
            <div class="bg-white border-2 border-amber-200 rounded-xl px-4 py-3 flex items-center gap-4 shadow-sm">
                <div>
                    <p class="font-black text-gray-800">${escapeForHtml(d.patient_name || d.employee_id)}</p>
                    <p class="text-xs text-gray-500 font-mono">รหัส: ${escapeForHtml(d.employee_id)} • รออยู่ ${formatElapsedMinutes(d.created_at)}</p>
                </div>
                <button type="button" onclick="resumeDraft('${escapeForHtml(d.employee_id).replace(/'/g, "\\'")}')" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-black px-3 py-2 rounded-lg transition-all active:scale-95">
                    <i class="fas fa-play mr-1"></i>ทำต่อ
                </button>
                <button type="button" onclick="cancelDraftItem(${d.id}, '${escapeForHtml(d.patient_name || d.employee_id).replace(/'/g, "\\'")}')" class="bg-red-50 hover:bg-red-100 text-red-600 text-sm font-black px-3 py-2 rounded-lg transition-all active:scale-95 border border-red-200">
                    <i class="fas fa-times mr-1"></i>ยกเลิก
                </button>
            </div>`).join('');
    }

    // เก็บ snapshot ของฟอร์มทั้งหมด ณ ตอนกด "บันทึกไว้ก่อน" (มาด้วยอาการอะไร, vital signs, checkbox, ตะกร้ายา ฯลฯ)
    // เพื่อให้กด "ทำต่อ" แล้วข้อมูลที่กรอกไว้กลับมาครบ ไม่ต้องพิมพ์ใหม่ทั้งหมด
    function collectFormSnapshot() {
        const symptomGroups = [];
        document.querySelectorAll('input[name="symptom_groups"]:checked').forEach(cb => symptomGroups.push(cb.value));
        const accidentTypes = [];
        document.querySelectorAll('input[name="accident_types"]:checked').forEach(cb => accidentTypes.push(cb.value));
        const serviceTypes = [];
        document.querySelectorAll('input[name="service_types"]:checked').forEach(cb => serviceTypes.push(cb.value));

        const isNewPatient = !document.getElementById('new_patient_inputs').classList.contains('hidden');

        return {
            is_new_patient: isNewPatient,
            new_prefix_th: document.getElementById('new_prefix_th')?.value || '',
            new_fname: document.getElementById('new_fname')?.value || '',
            new_lname: document.getElementById('new_lname')?.value || '',
            new_prefix_en: document.getElementById('new_prefix_en')?.value || '',
            new_name_en: document.getElementById('new_name_en')?.value || '',
            new_surname_en: document.getElementById('new_surname_en')?.value || '',
            new_gender: document.getElementById('new_gender')?.value || '',
            department: document.getElementById('department_input')?.value || '',
            company_select: document.getElementById('company_select')?.value || '',
            other_company: document.getElementById('other_company')?.value || '',
            edit_allergy: document.getElementById('edit_allergy')?.value || '',
            edit_chronic: document.getElementById('edit_chronic')?.value || '',
            birth_date: document.getElementById('birth_date')?.value || '',
            diagnosis: document.getElementById('diagnosis_input')?.value || '',
            v_temp: document.getElementById('v_temp')?.value || '',
            v_bp: document.getElementById('v_bp')?.value || '',
            v_pr: document.getElementById('v_pr')?.value || '',
            v_rr: document.getElementById('v_rr')?.value || '',
            v_o2: document.getElementById('v_o2')?.value || '',
            illness_type: document.getElementById('illness_type')?.value || '',
            disposition: document.getElementById('disposition')?.value || '',
            other_disposition: document.getElementById('other_disposition')?.value || '',
            procedure: document.getElementById('procedure')?.value || '',
            symptom_groups: symptomGroups,
            other_symptom_checked: !!document.getElementById('check_other_symptom')?.checked,
            other_symptom_text: document.getElementById('other_symptom_text')?.value || '',
            accident_types: accidentTypes,
            other_accident_checked: !!document.getElementById('check_other_accident')?.checked,
            other_accident_text: document.getElementById('other_accident_text')?.value || '',
            service_types: serviceTypes,
            other_service_checked: !!document.getElementById('check_other_service')?.checked,
            other_service_text: document.getElementById('other_service_text')?.value || '',
            cart: cart
        };
    }

    // นำ snapshot ที่เก็บไว้ตอน Save Draft กลับมาใส่ในฟอร์ม (เรียกหลังจาก lookupPatientByEmpId โหลดข้อมูลคนไข้เสร็จแล้ว)
    function applyFormSnapshot(snap) {
        if (!snap) return;

        if (snap.is_new_patient) {
            const newInputs = document.getElementById('new_patient_inputs');
            if (newInputs && !newInputs.classList.contains('hidden')) {
                if (snap.new_prefix_th) document.getElementById('new_prefix_th').value = snap.new_prefix_th;
                document.getElementById('new_fname').value = snap.new_fname || '';
                document.getElementById('new_lname').value = snap.new_lname || '';
                if (snap.new_prefix_en) document.getElementById('new_prefix_en').value = snap.new_prefix_en;
                document.getElementById('new_name_en').value = snap.new_name_en || '';
                document.getElementById('new_surname_en').value = snap.new_surname_en || '';
                if (snap.new_gender) document.getElementById('new_gender').value = snap.new_gender;
            }
        }

        if (snap.department) document.getElementById('department_input').value = snap.department;
        if (snap.company_select) {
            document.getElementById('company_select').value = snap.company_select;
            if (snap.company_select === 'other') {
                document.getElementById('other_company').value = snap.other_company || '';
                document.getElementById('other_company').classList.remove('hidden');
            }
        }
        if (snap.edit_allergy) document.getElementById('edit_allergy').value = snap.edit_allergy;
        if (snap.edit_chronic) document.getElementById('edit_chronic').value = snap.edit_chronic;
        if (snap.birth_date) { document.getElementById('birth_date').value = snap.birth_date; calculateAge(); }

        document.getElementById('diagnosis_input').value = snap.diagnosis || '';
        document.getElementById('v_temp').value = snap.v_temp || '';
        document.getElementById('v_bp').value = snap.v_bp || '';
        document.getElementById('v_pr').value = snap.v_pr || '';
        document.getElementById('v_rr').value = snap.v_rr || '';
        document.getElementById('v_o2').value = snap.v_o2 || '';
        if (snap.illness_type) document.getElementById('illness_type').value = snap.illness_type;
        if (snap.disposition) {
            document.getElementById('disposition').value = snap.disposition;
            if (snap.disposition === 'other') {
                document.getElementById('other_disposition').value = snap.other_disposition || '';
                document.getElementById('other_disposition').classList.remove('hidden');
            }
        }
        document.getElementById('procedure').value = snap.procedure || '';

        (snap.symptom_groups || []).forEach(val => {
            document.querySelectorAll('input[name="symptom_groups"]').forEach(cb => { if (cb.value === val) cb.checked = true; });
        });
        if (snap.other_symptom_checked) {
            document.getElementById('check_other_symptom').checked = true;
            document.getElementById('other_symptom_text').value = snap.other_symptom_text || '';
            document.getElementById('other_symptom_text').classList.remove('hidden');
        }
        (snap.accident_types || []).forEach(val => {
            document.querySelectorAll('input[name="accident_types"]').forEach(cb => { if (cb.value === val) cb.checked = true; });
        });
        if (snap.other_accident_checked) {
            document.getElementById('check_other_accident').checked = true;
            document.getElementById('other_accident_text').value = snap.other_accident_text || '';
            document.getElementById('other_accident_text').classList.remove('hidden');
        }
        (snap.service_types || []).forEach(val => {
            document.querySelectorAll('input[name="service_types"]').forEach(cb => { if (cb.value === val) cb.checked = true; });
        });
        if (snap.other_service_checked) {
            document.getElementById('check_other_service').checked = true;
            document.getElementById('other_service_text').value = snap.other_service_text || '';
            document.getElementById('other_service_text').classList.remove('hidden');
        }

        if (Array.isArray(snap.cart) && snap.cart.length > 0) {
            cart = snap.cart;
            updateCartBadges();
        }
    }

    function resumeDraft(empId) {
        const input = document.getElementById('employee_id_search');
        input.value = empId;
        const draft = (draftQueue || []).find(d => d.employee_id === empId);

        Promise.resolve(lookupPatientByEmpId(empId)).then(() => {
            if (draft && draft.form_data) applyFormSnapshot(draft.form_data);
        });

        input.scrollIntoView({ behavior: 'smooth', block: 'center' });
        const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1500, timerProgressBar: true });
        Toast.fire({ icon: 'info', title: 'โหลดข้อมูลคนไข้จากคิว Draft แล้ว' });
    }

    function cancelDraftItem(id, name) {
        Swal.fire({
            title: 'ยกเลิกคิว Draft นี้?',
            html: `ยกเลิกคิวของ <b>${escapeForHtml(name)}</b> — ระบบจะยังเก็บสถิติเวลาที่มาไว้ แต่จะไม่แสดงในคิวรออีก`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ยืนยันยกเลิก',
            cancelButtonText: 'ไม่ยกเลิก',
            confirmButtonColor: '#ef4444'
        }).then(res => {
            if (!res.isConfirmed) return;
            fetch(`{{ url('/pharmacy/draft') }}/${id}/cancel`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) { location.reload(); }
                else { Swal.fire('ผิดพลาด', data.message || 'ยกเลิกไม่สำเร็จ', 'error'); }
            })
            .catch(err => Swal.fire('ผิดพลาด', err.message, 'error'));
        });
    }

    function saveDraft() {
        const empId = document.getElementById('employee_id_search').value.trim();
        if (!empId) { Swal.fire('ข้อมูลไม่ครบ', 'กรุณาระบุรหัสคนไข้ก่อนบันทึกไว้ก่อน (Draft)', 'warning'); return; }

        // ชื่อที่จะแสดงในคิว Draft: คนไข้เก่าใช้ข้อมูลที่ดึงมา, คนไข้ใหม่ใช้ค่าที่กำลังกรอกอยู่
        const isNewPatient = !document.getElementById('new_patient_inputs').classList.contains('hidden');
        let displayNameForDraft = empId;
        if (isNewPatient) {
            const fn = document.getElementById('new_fname').value.trim();
            const ln = document.getElementById('new_lname').value.trim();
            if (fn || ln) displayNameForDraft = `${fn} ${ln}`.trim();
        } else if (currentPatientRaw) {
            displayNameForDraft = `${currentPatientRaw.prefix_th || ''}${currentPatientRaw.first_name || ''} ${currentPatientRaw.last_name || ''}`.trim() || empId;
        }

        fetch("{{ route('pharmacy.draft.store') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ employee_id: empId, patient_name: displayNameForDraft, form_data: collectFormSnapshot() })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1800, timerProgressBar: true });
                Toast.fire({ icon: 'success', title: `บันทึกคิวไว้ก่อนแล้ว: ${displayNameForDraft}` });
                setTimeout(() => location.reload(), 900);
            } else {
                Swal.fire('ผิดพลาด', data.message || 'บันทึกไม่สำเร็จ', 'error');
            }
        })
        .catch(err => Swal.fire('ผิดพลาด', err.message, 'error'));
    }

    renderDraftQueue();

    function addToCart() {
        const qtyInput = document.getElementById('qty_input');
        const qty = parseInt(qtyInput.value);
        if (!selectedDrug || qty < 1) return;
        if (qty > selectedDrug.stock_master) { Swal.fire('สต็อกไม่พอ', `ในคลังมีแค่ ${selectedDrug.stock_master}`, 'warning'); return; }
        
        const existing = cart.find(item => item.id === selectedDrug.id);
        if (existing) {
            existing.qty += qty;
            // เผื่อกรณีรายการนี้ถูกเพิ่มเข้าตะกร้าไว้ตั้งแต่ก่อนหน้านี้ (เช่น ค้างในหน่วยความจำจากตอนที่ยังไม่มีข้อมูลฉลากยา)
            // ให้เติมฟิลด์ฉลากยาที่ยังไม่เคยถูกตั้งค่าไว้เลย (undefined) จากข้อมูลยาปัจจุบัน โดยไม่ทับข้อความที่ผู้ใช้แก้ไขไว้แล้ว (แม้จะเป็นค่าว่างก็ตาม)
            const labelDefaults = {
                unit_name: selectedDrug.unit_name || '',
                usage_instruction: selectedDrug.usage_instruction || '',
                indication: selectedDrug.indication || '',
                side_effect: selectedDrug.side_effect || '',
                usage_instruction_en: selectedDrug.usage_instruction_en || '',
                indication_en: selectedDrug.indication_en || '',
                side_effect_en: selectedDrug.side_effect_en || ''
            };
            Object.keys(labelDefaults).forEach(key => {
                if (existing[key] === undefined) existing[key] = labelDefaults[key];
            });
        } else {
            cart.push({
                id: selectedDrug.id,
                name: selectedDrug.name,
                barcode: selectedDrug.barcode,
                qty: qty,
                unit_name: selectedDrug.unit_name || '',
                // ค่าเริ่มต้นของฉลากยา ดึงมาจากข้อมูลยา (แก้ไขได้อีกครั้งในตะกร้าก่อนบันทึก)
                usage_instruction: selectedDrug.usage_instruction || '',
                indication: selectedDrug.indication || '',
                side_effect: selectedDrug.side_effect || '',
                usage_instruction_en: selectedDrug.usage_instruction_en || '',
                indication_en: selectedDrug.indication_en || '',
                side_effect_en: selectedDrug.side_effect_en || ''
            });
        }
        
        updateCartBadges();
        if (!document.getElementById('cartModal').classList.contains('hidden')) { renderCart(); } else {
            const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 1500, timerProgressBar: true });
            Toast.fire({ icon: 'success', title: `เพิ่ม ${selectedDrug.name} เข้าตะกร้าแล้ว` });
        }
        resetPreview(); 
        document.getElementById('barcode_scanner').focus();
    }

    function resetPreview() {
        selectedDrug = null;
        document.getElementById('drug_preview').innerHTML = `<p class="text-gray-400 text-base italic">รอสแกนบาร์โค้ด...</p>`;
        document.getElementById('drug_preview').className = "bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl p-4 flex flex-col items-center justify-center min-h-[100px]";
        document.getElementById('qty_section').classList.add('hidden');
        document.getElementById('qty_input').value = 1;
    }

    // 🔄 เพิ่ม Logic ควบคุมสถานะปุ่มหน้าหลักตามจำนวนยาในตะกร้า
    function updateCartBadges() {
        document.getElementById('left_cart_count').innerText = `${cart.length} รายการ`;
        document.getElementById('floating_cart_badge').innerText = cart.length;
        document.getElementById('modal_cart_count').innerText = cart.length;

        const mainSubmitBtn = document.getElementById('btn-main-submit');
        const mainSubmitText = document.getElementById('main_submit_text');

        if (cart.length > 0) {
            // มียาในตะกร้า -> ล็อกปุ่ม "ไม่รับยา" ที่หน้าแรก เพื่อบังคับให้กดจากตะกร้า
            mainSubmitBtn.className = "w-full bg-slate-300 text-slate-500 py-5 rounded-xl font-black flex items-center justify-center gap-2 text-lg cursor-not-allowed";
            mainSubmitText.innerText = "กรุณาบันทึกผ่านตะกร้ายา (พบรายการยา)";
        } else {
            // ไม่มีนาในตะกร้า -> เปิดปุ่ม "บันทึกข้อมูล (ไม่รับยา)" ให้กดได้ตามปกติ
            mainSubmitBtn.className = "w-full bg-emerald-500 hover:bg-emerald-600 text-white py-5 rounded-xl font-black shadow-lg shadow-emerald-100 flex items-center justify-center gap-2 text-lg transition-all active:scale-[0.99]";
            mainSubmitText.innerText = "ยืนยันบันทึกข้อมูล (ไม่รับยา)";
        }
    }

    // escapeForHtml มาจากไฟล์กลาง public/js/dispense-label.js แล้ว

    function renderCart() {
        const modalContainer = document.getElementById('modal_cart_items');
        updateCartBadges();
        if (cart.length === 0) {
            modalContainer.innerHTML = `<p class="text-center py-12 text-gray-400 italic text-base">ยังไม่มีรายการยาในเคสนี้</p>`;
            return;
        }
        modalContainer.innerHTML = cart.map((item, index) => `
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                <div class="flex justify-between items-center">
                    <div class="flex-1 min-w-0 pr-3">
                        <p class="text-lg font-black text-gray-800 truncate">${item.name}</p>
                        <p class="text-sm text-gray-400 font-mono italic mt-0.5">${item.barcode || '-'}</p>
                    </div>
                    <div class="flex items-center gap-4 shrink-0">
                        <span class="font-black text-xl text-blue-600 bg-blue-50 px-4 py-1 rounded-xl border border-blue-100">x ${item.qty}</span>
                        <button onclick="removeFromCart(${index})" class="text-gray-300 hover:text-red-500 text-2xl transition-colors p-1"><i class="fas fa-trash-alt"></i></button>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-dashed border-gray-200">
                    <p class="text-[10px] font-bold text-gray-400 uppercase mb-2"><i class="fas fa-tag mr-1"></i> ข้อความบนฉลากยา ภาษาไทย (แก้ไขได้ก่อนพิมพ์)</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">วิธีใช้</label>
                            <textarea rows="2" oninput="updateCartLabel(${index}, 'usage_instruction', this.value)" class="w-full text-xs px-2 py-1.5 border border-gray-200 rounded-lg outline-none focus:border-blue-400" placeholder="วิธีใช้...">${escapeForHtml(item.usage_instruction)}</textarea>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">สรรพคุณ</label>
                            <textarea rows="2" oninput="updateCartLabel(${index}, 'indication', this.value)" class="w-full text-xs px-2 py-1.5 border border-gray-200 rounded-lg outline-none focus:border-blue-400" placeholder="สรรพคุณ...">${escapeForHtml(item.indication)}</textarea>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">ผลข้างเคียงสำคัญ</label>
                            <textarea rows="2" oninput="updateCartLabel(${index}, 'side_effect', this.value)" class="w-full text-xs px-2 py-1.5 border border-gray-200 rounded-lg outline-none focus:border-blue-400" placeholder="ถ้ามี...">${escapeForHtml(item.side_effect)}</textarea>
                        </div>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-dashed border-blue-100">
                    <p class="text-[10px] font-bold text-blue-400 uppercase mb-2"><i class="fas fa-language mr-1"></i> Label text (English) — editable before printing</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Usage</label>
                            <textarea rows="2" oninput="updateCartLabel(${index}, 'usage_instruction_en', this.value)" class="w-full text-xs px-2 py-1.5 border border-gray-200 rounded-lg outline-none focus:border-blue-400" placeholder="Usage...">${escapeForHtml(item.usage_instruction_en)}</textarea>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Indication</label>
                            <textarea rows="2" oninput="updateCartLabel(${index}, 'indication_en', this.value)" class="w-full text-xs px-2 py-1.5 border border-gray-200 rounded-lg outline-none focus:border-blue-400" placeholder="Indication...">${escapeForHtml(item.indication_en)}</textarea>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Side effects</label>
                            <textarea rows="2" oninput="updateCartLabel(${index}, 'side_effect_en', this.value)" class="w-full text-xs px-2 py-1.5 border border-gray-200 rounded-lg outline-none focus:border-blue-400" placeholder="If any...">${escapeForHtml(item.side_effect_en)}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function updateCartLabel(index, field, value) {
        if (cart[index]) cart[index][field] = value;
    }

    function removeFromCart(index) {
        cart.splice(index, 1);
        renderCart();
    }

    // --- 3. ระบบแสดงประวัติคนไข้ ---
    function showHistoryModal() {
        if (!currentPatientId) { Swal.fire('แจ้งเตือน', 'กรุณาระบุรหัสพนักงานที่ถูกต้องก่อน', 'warning'); return; }
        const modal = document.getElementById('historyModal');
        const content = document.getElementById('history_modal_content');
        modal.classList.remove('hidden'); modal.classList.add('flex');
        content.innerHTML = `<div class="py-20 text-center text-gray-400"><i class="fas fa-spinner fa-spin text-3xl mb-4 text-blue-500"></i><p>กำลังโหลดประวัติการรับยา...</p></div>`;
        
        fetch("{{ url('/pharmacy/patient-history') }}/" + currentPatientId)
            .then(res => res.json())
            .then(res => {
                if (res.success && res.dispensing_records && res.dispensing_records.length > 0) {
                    lastHistoryRecords = res.dispensing_records;
                    lastHistoryPatientName = res.patient_name || '';
                    content.innerHTML = res.dispensing_records.map((rec, idx) => {
                        const dateDisplay = formatDateThai(rec.created_at, true);
                        const typeClass = rec.illness_type === 'ในงาน' ? 'bg-red-100 text-red-600 border-red-200' : 'bg-blue-100 text-blue-600 border-blue-200';
                        const canReprint = rec.amount > 0 && rec.medicine;
                        return `<div class="bg-white p-4 rounded-2xl border border-gray-200 shadow-sm text-left">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-sm font-bold text-blue-500"><i class="far fa-clock mr-1"></i>${dateDisplay} น.</span>
                                        <span class="${typeClass} text-xs px-2 py-0.5 rounded-full font-bold border w-fit">${rec.illness_type || 'ทั่วไป'}</span>
                                    </div>
                                    <div class="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full border border-emerald-100 text-sm font-black">${rec.amount > 0 ? 'x' + rec.amount : 'ไม่รับยา/ปรึกษา'}</div>
                                </div>
                                <div class="mb-3">
                                    <h4 class="text-lg font-black text-gray-800">${rec.medicine ? rec.medicine.name : (rec.medicine_name || 'ไม่รับยา')}</h4>
                                    <p class="text-sm text-gray-500 mt-1 italic line-clamp-2">📌 DX: ${rec.diagnosis || '-'}</p>
                                </div>
                                ${canReprint ? `<button onclick="reprintLabelFromHistory(${idx})" class="w-full bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold py-2 rounded-xl flex items-center justify-center gap-2 transition-all active:scale-[0.98]"><i class="fas fa-print"></i> พิมพ์ฉลากซ้ำ</button>` : ''}
                            </div>`;
                    }).join('');
                } else {
                    lastHistoryRecords = [];
                    lastHistoryPatientName = '';
                    content.innerHTML = `<div class="py-20 text-center text-gray-400 italic"><i class="fas fa-folder-open text-3xl mb-2"></i><p>ไม่พบประวัติของพนักงานท่านนี้</p></div>`;
                }
            }).catch(err => { content.innerHTML = `<div class="py-20 text-center text-red-500"><p>เกิดข้อผิดพลาดในการดึงข้อมูล</p></div>`; });
    }

    // พิมพ์ฉลากยาซ้ำจากประวัติการจ่ายยาเดิม (เผื่อสติกเกอร์เดิมหายหรือพิมพ์ไม่ติด)
    // ใช้ข้อมูล usage_instruction/indication/side_effect ที่บันทึกไว้ ณ วันที่จ่ายจริงก่อน
    // แต่ถ้าตอนนั้นยังไม่มีข้อมูล (รายการเก่าก่อนมีระบบฉลากยา/ยังไม่ได้กรอกข้อมูลยาไว้) ให้ใช้ข้อมูลปัจจุบันของยาแทน
    // ดีกว่าปล่อยให้สติกเกอร์ที่พิมพ์ซ้ำว่างเปล่าไม่มีวิธีใช้/สรรพคุณ/คำเตือนเลย
    function reprintLabelFromHistory(index) {
        const rec = lastHistoryRecords[index];
        if (!rec || !rec.medicine) { Swal.fire('ผิดพลาด', 'ไม่พบข้อมูลรายการยานี้', 'error'); return; }
        const item = {
            name: rec.medicine.name,
            qty: rec.amount,
            unit_name: rec.medicine.unit_name || '',
            usage_instruction: rec.usage_instruction || rec.medicine.usage_instruction || '',
            indication: rec.indication || rec.medicine.indication || '',
            side_effect: rec.side_effect || rec.medicine.side_effect || '',
            usage_instruction_en: rec.usage_instruction_en || rec.medicine.usage_instruction_en || '',
            indication_en: rec.indication_en || rec.medicine.indication_en || '',
            side_effect_en: rec.side_effect_en || rec.medicine.side_effect_en || ''
        };
        printDispenseLabels([item], lastHistoryPatientName, rec.created_at);
    }

    function closeHistoryModal() { document.getElementById('historyModal').classList.add('hidden'); }
    function toggleOtherInput(cbId, inputId) {
        const cb = document.getElementById(cbId); const input = document.getElementById(inputId);
        if (cb && input) { if (cb.checked) { input.classList.remove('hidden'); input.focus(); } else { input.classList.add('hidden'); input.value = ''; } }
    }
    function toggleOtherCompany(select) {
        const other = document.getElementById('other_company');
        if (select.value === 'other') { other.classList.remove('hidden'); other.focus(); } else { other.classList.add('hidden'); other.value = ''; }
    }
    function checkOther(select) {
        const other = document.getElementById('other_disposition');
        if (select.value === 'other') { other.classList.remove('hidden'); other.focus(); } else { other.classList.add('hidden'); other.value = ''; }
    }

    // --- 4. ระบบยืนยันการบันทึก ---
    function confirmAndSubmit(isFromModal = false) {
        // หากผู้ใช้กดปุ่มหน้าหลักแต่ในตะกร้ามียาอยู่ จะไม่ทำงาน บังคับให้ใช้ Modal ตะกร้า
        if (!isFromModal && cart.length > 0) {
            Swal.fire('ตรวจพบยาในตะกร้า', 'กรุณากดเปิดดูรายการยาในเคสนี้แล้วคลิกปุ่มบันทึกจากในหน้าต่างเพื่อความถูกต้องครับ', 'warning');
            return;
        }

        const empId = document.getElementById('employee_id_search').value;
        if (!empId) { Swal.fire('ข้อมูลไม่ครบ', 'กรุณาระบุรหัสคนไข้', 'warning'); return; }
        
        let staffOptions = '<option value="">-- เลือกผู้จ่ายยา/ผู้บันทึก --</option>';
        @foreach($staffs as $staff) staffOptions += `<option value="{{ $staff->name }}">{{ $staff->name }}</option>`; @endforeach
        
        const currentTitle = isFromModal ? 'ยืนยันบันทึกข้อมูล (แบบจ่ายยา)' : 'ยืนยันบันทึกข้อมูล (ไม่รับยา)';

        Swal.fire({
            title: currentTitle,
            html: `<select id="swal_staff_name" class="swal2-input !w-full" style="font-size: 1.15rem; font-weight: bold;">${staffOptions}</select>`,
            showCancelButton: true,
            confirmButtonText: 'บันทึกข้อมูล',
            cancelButtonText: 'ยกเลิก',
            preConfirm: () => {
                const name = Swal.getPopup().querySelector('#swal_staff_name').value;
                if (!name) Swal.showValidationMessage(`กรุณาเลือกผู้บันทึก`);
                return { staffName: name };
            }
        }).then((res) => { if (res.isConfirmed) submitData(res.value.staffName); });
    }

    // --- 5. การจัดส่งข้อมูลไปยัง Controller ---
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

        let prefixTh = '', firstName = '', lastName = '';
        let prefixEn = '', nameEn = '', surnameEn = '';
        let gender = '';

        const isNewPatient = !document.getElementById('new_patient_inputs').classList.contains('hidden');

        if (isNewPatient) {
            prefixTh = document.getElementById('new_prefix_th').value;
            firstName = document.getElementById('new_fname').value;
            lastName = document.getElementById('new_lname').value;
            prefixEn = document.getElementById('new_prefix_en').value;
            nameEn = document.getElementById('new_name_en').value;
            surnameEn = document.getElementById('new_surname_en').value;
            gender = document.getElementById('new_gender').value;
        } else {
            const displayNameElem = document.getElementById('display_name');
            const rawNameText = displayNameElem ? displayNameElem.innerText.split(' (')[0] : '-';
            firstName = rawNameText.split(' ')[0] || '-';
            lastName = rawNameText.split(' ')[1] || '-';
            gender = document.getElementById('display_gender')?.innerText || '-';
            if (currentPatientRaw) {
                prefixEn = currentPatientRaw.prefix_en || '';
                nameEn = currentPatientRaw.name_en || '';
                surnameEn = currentPatientRaw.surname_en || '';
            }
        }

        // ชื่อคนไข้ที่จะพิมพ์บนฉลากยา: ถ้าชื่อพนักงานเป็นไทย ใช้ชื่อไทย (ติดกับคำนำหน้าแบบไทย)
        // ถ้าไม่ใช่ (พนักงานต่างชาติ ซึ่งบางครั้งถูกกรอกชื่ออังกฤษไว้ในช่อง "ไทย" โดยตรงเพราะไม่มีชื่อไทย)
        // ให้ใช้ชื่ออังกฤษแทน (มีเว้นวรรคระหว่างคำนำหน้ากับชื่อตามธรรมเนียมอังกฤษ)
        const labelPatientName = isThaiText(firstName)
            ? `${prefixTh || ''}${firstName || ''} ${lastName || ''}`.trim()
            : `${prefixEn || prefixTh || ''} ${nameEn || firstName || ''} ${surnameEn || lastName || ''}`.replace(/\s+/g, ' ').trim();

        const payload = {
            employee_id: document.getElementById('employee_id_search').value,
            prefix_th: prefixTh, first_name: firstName, last_name: lastName,
            prefix_en: prefixEn, name_en: nameEn, surname_en: surnameEn,
            gender: gender, birth_date: document.getElementById('birth_date')?.value || null, 
            department: document.getElementById('department_input').value,
            company: finalCompany,
            drug_allergy: document.getElementById('edit_allergy').value,
            chronic_disease: document.getElementById('edit_chronic').value,
            diagnosis: document.getElementById('diagnosis_input').value || '-',
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
                // มีรายการยา -> เปิดหน้าต่างพิมพ์ฉลากยาให้คนไข้อัตโนมัติ ก่อนแจ้งเตือนสำเร็จ
                if (cart.length > 0) {
                    printDispenseLabels(cart, labelPatientName);
                }
                Swal.fire('สำเร็จ', 'บันทึกเรียบร้อย', 'success').then(() => location.reload());
            }
            else { throw new Error(data.message); }
        })
        .catch(err => { Swal.fire('ผิดพลาด', err.message, 'error'); btn.disabled = false; });
    }

    // UNIT_NAME_EN_MAP, toEnglishUnit, printDispenseLabels มาจากไฟล์กลาง public/js/dispense-label.js แล้ว

    function calculateAge() {
        const birthInputElement = document.getElementById('birth_date');
        if (!birthInputElement) return; 
        const birthDateInput = birthInputElement.value;
        const displayAge = document.getElementById('display_age');
        if (!birthDateInput) { if(displayAge) displayAge.innerText = "-"; return; }

        const birthDate = new Date(birthDateInput);
        const today = new Date();
        let years = today.getFullYear() - birthDate.getFullYear();
        let months = today.getMonth() - birthDate.getMonth();
        let days = today.getDate() - birthDate.getDate();
        
        if (months < 0 || (months === 0 && days < 0)) { years--; months += 12; }
        if (days < 0) {
            const prevMonthLastDay = new Date(today.getFullYear(), today.getMonth(), 0).getDate();
            months--; days += prevMonthLastDay;
            if (months < 0) months = 11;
        }
        if(displayAge) displayAge.innerText = `อายุ ${years} ปี ${months} เดือน ${days} วัน`;
    }

    window.onload = () => document.getElementById('employee_id_search').focus();
</script>
@endsection