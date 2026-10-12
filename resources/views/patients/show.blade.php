@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    // เตรียมโลโก้ห้องพยาบาลเป็น data URI สำหรับ "พิมพ์ฉลากซ้ำ" จากหน้านี้ (เหมือนหน้าจ่ายยา)
    $labelLogoPath = public_path(config('pharmacy_label.logo_path'));
    $labelLogoDataUri = file_exists($labelLogoPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($labelLogoPath))
        : null;
@endphp

<div class="max-w-5xl mx-auto p-6">
    <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 mb-6 flex justify-between items-center">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('patients.index') }}" class="text-gray-400 hover:text-blue-600"><i class="fas fa-arrow-left"></i></a>
                <h1 class="text-2xl font-black text-gray-800">{{ $patient->first_name }} {{ $patient->last_name }}</h1>
            </div>
            <p class="text-blue-600 font-mono ml-7">ID: {{ $patient->employee_id }}</p>
        </div>
        <div class="text-right">
            <span class="text-xs text-gray-400 block uppercase font-bold tracking-wider">โรคประจำตัว</span>
            <span class="font-bold text-orange-600">{{ $patient->chronic_disease ?? '-' }}</span>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <div class="p-5 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
            <h3 class="font-bold text-gray-700 uppercase text-xs tracking-widest"><i class="fas fa-history mr-2 text-blue-500"></i> ประวัติการเบิกยา</h3>
            <span class="text-[10px] text-gray-400 italic bg-white px-3 py-1 rounded-full border border-gray-100">* คลิกปุ่มคืนยาเพื่อนำยากลับเข้าสต็อก</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] uppercase text-gray-400 border-b bg-gray-50/30">
                        <th class="px-6 py-4 font-black">วันที่เบิก</th>
                        <th class="px-6 py-4 font-black">รายการยา</th>
                        <th class="px-6 py-4 text-center font-black">จำนวนที่ถืออยู่</th>
                        <th class="px-6 py-4 font-black">ผู้ออกยา</th>
                        <th class="px-6 py-4 font-black">สถานะ</th>
                        <th class="px-6 py-4 text-right font-black">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    {{-- 🟢 ปรับปรุง: ใช้ sortByDesc เพื่อนำรายการที่บันทึกล่าสุด (ปัจจุบัน) ขึ้นก่อน --}}
                    @forelse($patient->dispensingRecords->sortByDesc('created_at') as $record)
                    <tr class="hover:bg-gray-50/50 transition-colors {{ $record->amount == 0 ? 'bg-gray-50/80 opacity-60' : '' }}">
                        <td class="px-6 py-4 text-sm font-medium text-gray-500">
                            {{ $record->created_at->timezone('Asia/Bangkok')->format('d/m/Y') }}
                            <span class="block text-[10px] text-gray-300">{{ $record->created_at->format('H:i') }} น.</span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-bold text-gray-800">{{ $record->medicine?->name ?? 'ไม่รับยา / บริการอื่นๆ' }}</p>
                            @if($record->return_remark)
                                <p class="text-[9px] text-orange-500 font-bold bg-orange-50 px-2 py-0.5 rounded-md mt-1 w-fit">
                                    <i class="fas fa-info-circle mr-1"></i> {{ $record->return_remark }}
                                </p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-mono font-black text-blue-600 text-lg">{{ $record->amount }}</span>
                        </td>
                        <td class="px-6 py-4 text-xs font-bold text-gray-500">{{ $record->issued_by }}</td>
                        <td class="px-6 py-4">
                            @if($record->amount == 0)
                                <span class="text-[9px] bg-red-100 text-red-600 px-2 py-1 rounded-lg font-black uppercase tracking-tighter">คืนครบแล้ว</span>
                            @elseif($record->returns_count > 0) 
                                <span class="text-[9px] bg-blue-100 text-blue-600 px-2 py-1 rounded-lg font-black uppercase tracking-tighter">คืนบางส่วน</span>
                            @else
                                <span class="text-[9px] bg-green-100 text-green-600 px-2 py-1 rounded-lg font-black uppercase tracking-tighter">ปกติ</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if($record->amount > 0 && $record->medicine)
                            <div class="flex flex-col gap-2 items-end">
                                <button onclick="reprintFromHistoryPage({{ $record->id }})"
                                    class="bg-white border border-blue-200 text-blue-600 hover:bg-blue-600 hover:text-white px-4 py-2 rounded-xl transition-all text-[11px] font-black flex items-center gap-2 ml-auto shadow-sm w-fit">
                                    <i class="fas fa-print"></i> พิมพ์ฉลากซ้ำ
                                </button>
                                <button onclick="returnMedicine({{ $record->id }}, '{{ $record->medicine->name }}', {{ $record->amount }})"
                                    class="bg-white border border-orange-200 text-orange-600 hover:bg-orange-600 hover:text-white px-4 py-2 rounded-xl transition-all text-[11px] font-black flex items-center gap-2 ml-auto shadow-sm w-fit">
                                    <i class="fas fa-undo-alt"></i> คืนยา
                                </button>
                            </div>
                            @else
                                <span class="text-gray-300 text-[10px] italic font-medium pr-4">ไม่มีรายการค้าง</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-box-open text-4xl text-gray-100 mb-3"></i>
                                <p class="text-gray-400 italic text-sm">ไม่พบประวัติการเบิกยาของคนไข้รายนี้</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-red-100 overflow-hidden">
        <div class="p-5 border-b border-red-50 bg-red-50/30 flex justify-between items-center">
            <h3 class="font-bold text-red-700 uppercase text-xs tracking-widest"><i class="fas fa-undo-alt mr-2"></i> บันทึกประวัติการคืนยา (Audit Log)</h3>
            <span class="text-[10px] text-red-400 font-bold uppercase">ประวัติการทำรายการ</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-[10px] uppercase text-gray-400 border-b bg-gray-50/30">
                        <th class="px-6 py-4 font-black">วันที่คืน</th>
                        <th class="px-6 py-4 font-black">รายการยา</th>
                        <th class="px-6 py-4 text-center font-black">จำนวนที่คืน</th>
                        <th class="px-6 py-4 font-black">สาเหตุ</th>
                        <th class="px-6 py-4 font-black">ผู้รับคืน</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-xs">
                    {{-- 🟢 ปรับปรุง: ใช้ sortByDesc เพื่อนำรายการที่เพิ่งคืนล่าสุด (ปัจจุบัน) ขึ้นก่อนเช่นกัน --}}
                    @forelse(($patient->medicineReturns ?? collect())->sortByDesc('created_at') as $return)
                    <tr>
                        <td class="px-6 py-4 text-gray-500">
                            {{ $return->created_at->timezone('Asia/Bangkok')->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 font-bold text-gray-700">
                            {{ $return->medicine?->name ?? 'รายการยาถูกลบ' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="text-red-600 font-black">+ {{ $return->amount }}</span>
                        </td>
                        <td class="px-6 py-4 text-gray-500 italic">
                            {{ $return->reason ?? '-' }}
                        </td>
                        <td class="px-6 py-4 font-bold text-blue-600">
                            {{ $return->returned_by }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-300 italic">
                            ยังไม่มีบันทึกการคืนยา
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- โค้ดพิมพ์ฉลากยา (isThaiText, escapeForHtml, printDispenseLabels ฯลฯ) ใช้ไฟล์กลางร่วมกับหน้าจ่ายยา --}}
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
    // ข้อมูลประวัติการเบิกยาทั้งหมดของคนไข้คนนี้ (พร้อมข้อมูลยา) ไว้ใช้ตอนกด "พิมพ์ฉลากซ้ำ"
    const patientDispensingRecords = @json($patient->dispensingRecords);
    const patientRawForLabel = @json($patient);

    // ชื่อคนไข้ที่จะพิมพ์บนฉลาก: ถ้าชื่อเป็นไทยใช้รูปแบบไทย ถ้าเป็นอังกฤษ (พนักงานต่างชาติ) ใช้รูปแบบอังกฤษ
    // (กฎเดียวกับหน้าจ่ายยา ไม่ผสมสองภาษาในใบเดียว)
    function getLabelPatientNameForHistory() {
        const firstName = patientRawForLabel.first_name || '';
        const lastName = patientRawForLabel.last_name || '';
        const prefixTh = patientRawForLabel.prefix_th || '';
        const prefixEn = patientRawForLabel.prefix_en || '';
        const nameEn = patientRawForLabel.name_en || '';
        const surnameEn = patientRawForLabel.surname_en || '';
        return isThaiText(firstName)
            ? `${prefixTh}${firstName} ${lastName}`.trim()
            : `${prefixEn || prefixTh} ${nameEn || firstName} ${surnameEn || lastName}`.replace(/\s+/g, ' ').trim();
    }

    // พิมพ์ฉลากยาซ้ำจากประวัติการจ่ายยาเดิม (เผื่อสติกเกอร์เดิมหายหรือพิมพ์ไม่ติด)
    // ใช้ข้อมูล usage_instruction/indication/side_effect ที่บันทึกไว้ ณ วันที่จ่ายจริงก่อน
    // แต่ถ้าตอนนั้นยังไม่มีข้อมูล (รายการเก่าก่อนมีระบบฉลากยา/ยังไม่ได้กรอกข้อมูลยาไว้) ให้ใช้ข้อมูลปัจจุบันของยาแทน
    // ดีกว่าปล่อยให้สติกเกอร์ที่พิมพ์ซ้ำว่างเปล่าไม่มีวิธีใช้/สรรพคุณ/คำเตือนเลย
    function reprintFromHistoryPage(recordId) {
        const rec = patientDispensingRecords.find(r => r.id === recordId);
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
        printDispenseLabels([item], getLabelPatientNameForHistory(), rec.created_at);
    }
</script>

<script>
function returnMedicine(id, name, maxQty) {
    const staffs = @json($staffs ?? []);
    
    let staffOptions = '<option value="" disabled selected>-- เลือกรายชื่อเจ้าหน้าที่ --</option>';
    staffs.forEach(staff => {
        staffOptions += `<option value="${staff.name}">${staff.name}</option>`;
    });

    Swal.fire({
        title: `คืนยาเข้าคลัง`,
        html: `
            <div class="text-left p-2">
                <div class="mb-4 bg-blue-50 p-3 rounded-2xl border border-blue-100">
                    <p class="text-[10px] uppercase font-bold text-blue-400 mb-1">รายการยา</p>
                    <p class="text-sm font-black text-blue-700">${name}</p>
                </div>

                <label class="block text-[10px] font-bold text-gray-400 mb-1 uppercase tracking-wider">จำนวนที่คืน (สูงสุด ${maxQty})</label>
                <input type="number" id="return_qty" class="swal2-input !m-0 w-full rounded-xl border-gray-200 font-black text-center" 
                    value="${maxQty}" min="1" max="${maxQty}">
                
                <label class="block text-[10px] font-bold text-gray-400 mt-4 mb-1 uppercase tracking-wider">ผู้รับคืนยา</label>
                <select id="staff_name" class="swal2-input !m-0 w-full rounded-xl border-gray-200 text-sm appearance-none">
                    ${staffOptions}
                </select>

                <label class="block text-[10px] font-bold text-gray-400 mt-4 mb-1 uppercase tracking-wider">สาเหตุการคืน</label>
                <input type="text" id="return_remark" class="swal2-input !m-0 w-full rounded-xl border-gray-200 text-sm" 
                    placeholder="ระบุเหตุผล เช่น คีย์ผิด, คนไข้เปลี่ยนยา">
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'ยืนยันคืนยา',
        confirmButtonColor: '#f97316',
        cancelButtonText: 'ยกเลิก',
        reverseButtons: true,
        focusConfirm: false,
        preConfirm: () => {
            const qty = document.getElementById('return_qty').value;
            const staff = document.getElementById('staff_name').value;
            const remark = document.getElementById('return_remark').value;

            if (!staff) {
                Swal.showValidationMessage(`กรุณาเลือกรายชื่อผู้รับคืนยา`);
                return false;
            }
            if (!qty || qty <= 0 || qty > maxQty) {
                Swal.showValidationMessage(`จำนวนต้องอยู่ระหว่าง 1 - ${maxQty}`);
                return false;
            }

            return { 
                return_qty: qty, 
                staff_name: staff,
                remark: remark
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({ 
                title: 'กำลังบันทึกประวัติ...', 
                allowOutsideClick: false, 
                didOpen: () => Swal.showLoading() 
            });

            fetch(`/pharmacy/return/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(result.value)
            })
            .then(async response => {
                const data = await response.json();
                if (response.ok && data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'คืนยาสำเร็จ',
                        text: 'สต็อกและประวัติถูกอัปเดตแล้ว',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    throw new Error(data.message || 'ระบบไม่สามารถประมวลผลได้');
                }
            })
            .catch(error => {
                Swal.fire('เกิดข้อผิดพลาด', error.message, 'error');
            });
        }
    });
}
</script>
@endsection