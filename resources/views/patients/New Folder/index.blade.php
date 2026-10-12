@extends('layouts.app')

@section('title', 'รายชื่อคนไข้ - MediStock')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 p-4">
    
    <!-- Header & Search -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <span class="w-2 h-8 bg-blue-600 rounded-full"></span>
                รายชื่อคนไข้และประวัติการรักษา
            </h1>
            <p class="text-sm text-gray-500">จัดการข้อมูลและประวัติการแพ้ยาของพนักงาน</p>
        </div>
        
        <div class="relative w-full lg:w-96">
            <input type="text" id="patientSearch" placeholder="ค้นหารหัสพนักงาน ชื่อ-นามสกุล หรือโรค..." 
                class="w-full pl-12 pr-4 py-3 rounded-2xl border border-gray-200 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all shadow-sm">
            <i class="fas fa-search absolute left-4 top-4 text-gray-400"></i>
        </div>
    </div>

    <!-- Patient Table -->
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto max-h-[600px] scrollbar-custom">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 bg-white shadow-sm z-10">
                    <tr class="text-gray-400 text-[11px] uppercase tracking-wider font-bold border-b">
                        <th class="px-6 py-4">รหัสพนักงาน</th>
                        <th class="px-6 py-4">ชื่อ-นามสกุล</th>
                        <th class="px-6 py-4">โรคประจำตัว</th>
                        <th class="px-6 py-4">ประวัติการแพ้ยา</th>
                        <th class="px-6 py-4 text-right">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50" id="patientTable">
                    @foreach($patients as $patient)
                    <tr class="hover:bg-blue-50/50 transition-all group">
                        <td class="px-6 py-4 font-mono font-bold text-blue-600 text-sm">
                            {{ $patient->employee_id }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-bold text-gray-800">{{ $patient->first_name }} {{ $patient->last_name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($patient->chronic_disease)
                                <span class="bg-orange-50 text-orange-600 px-3 py-1 rounded-full text-xs font-bold border border-orange-100">
                                    {{ $patient->chronic_disease }}
                                </span>
                            @else
                                <span class="text-gray-300 text-xs">- ไม่มี -</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($patient->drug_allergy)
                                <span class="bg-red-50 text-red-600 px-3 py-1 rounded-full text-xs font-bold border border-red-100 flex items-center gap-1 w-fit">
                                    <i class="fas fa-exclamation-triangle"></i> {{ $patient->drug_allergy }}
                                </span>
                            @else
                                <span class="text-gray-300 text-xs">- ไม่พบประวัติ -</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('patients.show', $patient->id) }}" 
                                   class="p-2 bg-gray-100 text-gray-600 rounded-xl hover:bg-gray-800 hover:text-white transition-all shadow-sm" title="ดูประวัติและคืนยา">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                                <button onclick="editPatient({{ json_encode($patient) }})" 
                                    class="p-2 bg-blue-50 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all shadow-sm">
                                    <i class="fas fa-edit text-xs"></i>
                                </button>
                                <button onclick="deletePatient({{ $patient->id }}, '{{ $patient->first_name }}')" 
                                    class="p-2 bg-red-50 text-red-600 rounded-xl hover:bg-red-600 hover:text-white transition-all shadow-sm">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                                <button onclick="viewFullHistory({{ $patient->id }}, '{{ $patient->first_name }} {{ $patient->last_name }}')" 
                                    class="p-2 bg-emerald-50 text-emerald-600 rounded-xl hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="ประวัติการรักษาละเอียด">
                                    <i class="fas fa-notes-medical text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex justify-between items-center px-4 py-2">
        <p class="text-xs text-gray-400 italic">* ระบบจะคัดกรองข้อมูลตามคำค้นหาอัตโนมัติ</p>
        <div class="text-xs font-bold text-gray-500">
            จำนวนคนไข้ทั้งหมด: <span class="text-blue-600">{{ $patients->count() }}</span> คน
        </div>
    </div>
</div>

<!-- Modal: แก้ไขข้อมูล -->
<div id="editModal" class="fixed inset-0 bg-black/50 z-[100] hidden items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-3xl w-full max-w-md overflow-hidden shadow-2xl animate-in zoom-in duration-200">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-blue-50/50">
            <h3 class="font-black text-blue-900 flex items-center gap-2">
                <i class="fas fa-user-edit"></i> แก้ไขข้อมูลคนไข้
            </h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form id="editForm" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="p-6 space-y-4">
                <div>
                    <label class="text-[10px] font-bold text-blue-600 uppercase tracking-wider">รหัสพนักงาน (Employee ID)</label>
                    <input type="text" name="employee_id" id="edit_employee_id" required
                        class="w-full bg-blue-50 border-2 border-blue-100 rounded-xl p-3 text-sm font-bold text-blue-700 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase">ชื่อ</label>
                        <input type="text" name="first_name" id="edit_first_name" required
                            class="w-full bg-gray-50 border-none rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase">นามสกุล</label>
                        <input type="text" name="last_name" id="edit_last_name"
                            class="w-full bg-gray-50 border-none rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
    <div>
        <label class="text-[10px] font-bold text-gray-400 uppercase">แผนก (Department)</label>
        <input type="text" name="department" id="edit_department"
            class="w-full bg-gray-50 border-none rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500" 
            placeholder="ระบุแผนก...">
    </div>
    <div>
        <label class="text-[10px] font-bold text-gray-400 uppercase">บริษัท (Company)</label>
        <select name="company" id="edit_company"
            class="w-full bg-gray-50 border-none rounded-xl p-3 text-sm focus:ring-2 focus:ring-blue-500 appearance-none">
            <option value="">-- เลือกบริษัท --</option>
            <option value="Covestro">Covestro</option>
            <option value="Contractor Covestro">Contractor Covestro</option>
            <option value="Styrenix Performance materials">Styrenix Performance materials</option>
            <option value="Contractor Styrenix Performance materials">Contractor Styrenix Performance materials</option>
            <option value="อื่นๆ (ระบุเอง)">อื่นๆ (ระบุเอง)</option>
        </select>
    </div>
</div>

                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase">โรคประจำตัว</label>
                    <input type="text" name="chronic_disease" id="edit_chronic_disease"
                        class="w-full bg-orange-50/50 border-none rounded-xl p-3 text-sm focus:ring-2 focus:ring-orange-500" placeholder="ระบุโรคประจำตัว...">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-gray-400 uppercase text-red-400">ประวัติการแพ้ยา</label>
                    <textarea name="drug_allergy" id="edit_drug_allergy" rows="2"
                        class="w-full bg-red-50/50 border-none rounded-xl p-3 text-sm focus:ring-2 focus:ring-red-500" placeholder="ระบุยาที่แพ้..."></textarea>
                </div>
            </div>
            <div class="p-6 bg-gray-50 flex gap-3">
                <button type="button" onclick="closeModal()" class="flex-1 py-3 text-sm font-bold text-gray-500">ยกเลิก</button>
                <button type="submit" class="flex-[2] bg-blue-600 text-white py-3 rounded-2xl font-black shadow-lg shadow-blue-200 hover:bg-blue-700 transition-all">
                    บันทึกการแก้ไข
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: ประวัติละเอียด -->
<div id="historyModal" class="fixed inset-0 bg-black/60 z-[110] hidden items-center justify-center p-4 backdrop-blur-md">
    <div class="bg-white rounded-3xl w-full max-w-4xl max-h-[90vh] overflow-hidden shadow-2xl flex flex-col animate-in slide-in-from-bottom duration-300">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-emerald-50/50">
            <div>
                <h3 class="font-black text-emerald-900 flex items-center gap-2 text-xl">
                    <i class="fas fa-history"></i> ประวัติการรักษาอย่างละเอียด
                </h3>
                <p id="history_patient_name" class="text-emerald-600 font-bold text-sm"></p>
            </div>
            <button onclick="closeHistoryModal()" class="text-gray-400 hover:text-gray-600 bg-white w-10 h-10 rounded-full shadow-sm flex items-center justify-center">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div id="history_list_container" class="p-6 overflow-y-auto bg-gray-50/50 space-y-4 flex-1 scrollbar-custom">
            <!-- Loader -->
            <div id="history_loader" class="flex flex-col items-center justify-center py-20 text-gray-400">
                <i class="fas fa-spinner fa-spin text-3xl mb-4"></i>
                <p>กำลังโหลดข้อมูล...</p>
            </div>
            <div id="history_content"></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const modal = document.getElementById('editModal');
    const editForm = document.getElementById('editForm');
    const historyModal = document.getElementById('historyModal');

    // 1. ระบบค้นหา
    document.getElementById('patientSearch').addEventListener('keyup', function() {
        let value = this.value.toLowerCase();
        let rows = document.querySelectorAll('#patientTable tr');
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(value) ? '' : 'none';
        });
    });

    // 2. จัดการ Modal แก้ไข
    function editPatient(patient) {
    // 1. นำข้อมูลจาก Object 'patient' มาใส่ใน Input แต่ละตัว
    document.getElementById('edit_employee_id').value = patient.employee_id;
    document.getElementById('edit_first_name').value = patient.first_name;
    document.getElementById('edit_last_name').value = patient.last_name || '';
    
    // แยกฟิลด์แผนกและบริษัท
    document.getElementById('edit_department').value = patient.department || '';
    document.getElementById('edit_company').value = patient.company || ''; 
    
    document.getElementById('edit_chronic_disease').value = patient.chronic_disease || '';
    document.getElementById('edit_drug_allergy').value = patient.drug_allergy || '';
    
    // 2. ปรับเปลี่ยน Action ของ Form ให้ชี้ไปยัง ID ที่ถูกต้อง
    editForm.action = `/patients/${patient.id}`;
    
    // 3. แสดง Modal
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // 3. ระบบลบ
    function deletePatient(id, name) {
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: `คุณกำลังจะลบข้อมูลของ ${name} และประวัติทั้งหมด!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'ใช่, ลบเลย!',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/patients/${id}`;
                form.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(form);
                form.submit();
            }
        })
    }

    // แก้ไข URL ในการ Fetch ให้ตรงกับ Route ที่เราตั้งใหม่
function viewFullHistory(patientId, fullName) {
    const loader = document.getElementById('history_loader');
    const content = document.getElementById('history_content');
    const nameDisplay = document.getElementById('history_patient_name');

    nameDisplay.innerText = `พนักงาน: ${fullName}`;
    loader.classList.remove('hidden');
    content.innerHTML = ''; 
    
    historyModal.classList.remove('hidden');
    historyModal.classList.add('flex');

    // เรียก API (ปรับ URL ให้ตรงกับ web.php)
    fetch(`/api/patients/${patientId}/history`)
        .then(res => res.json())
        .then(data => {
    loader.classList.add('hidden');
    
    // เนื่องจากเราส่ง ['success' => true, 'dispensing_records' => ...] 
    // เราต้องดึงข้อมูลจาก data.dispensing_records
    const records = data.dispensing_records || []; 
    
    if (records.length === 0) {
        content.innerHTML = `<p class="text-center py-10 text-gray-400">ไม่พบประวัติการรักษา</p>`;
    } else {
        renderHistoryList(records);
    }
})
        .catch(err => {
            console.error('Fetch Error:', err);
            loader.classList.add('hidden');
            content.innerHTML = `<p class="text-center text-red-500 py-10">เกิดข้อผิดพลาดในการดึงข้อมูล</p>`;
        });
}

// เพิ่มฟังก์ชันสำหรับสร้าง HTML List
function renderHistoryList(records) {
    const container = document.getElementById('history_content');
    let html = `<div class="grid grid-cols-1 md:grid-cols-2 gap-4">`;

    records.forEach(rec => {
        const date = rec.created_at ? new Date(rec.created_at).toLocaleDateString('th-TH', {
            day: 'numeric', month: 'short', year: '2-digit', hour: '2-digit', minute: '2-digit'
        }) : '-';

        const typeClass = rec.illness_type === 'ในงาน' 
            ? 'bg-red-100 text-red-600 border-red-200' 
            : 'bg-blue-100 text-blue-600 border-blue-200';

        const medName = rec.medicine ? rec.medicine.name : (rec.medicine_name || 'ไม่ระบุยา');

        html += `
        <div class="bg-white p-5 rounded-[2rem] border border-gray-100 shadow-sm hover:shadow-md transition-all">
            <div class="flex justify-between items-start mb-3">
                <div class="flex flex-col gap-1">
                    <span class="text-[10px] font-bold text-gray-400">${date} น.</span>
                    <span class="${typeClass} text-[9px] px-2 py-0.5 rounded-full font-bold border w-fit">
                        ${rec.illness_type || 'ทั่วไป'}
                    </span>
                </div>
                <div class="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full border border-emerald-100 text-[11px] font-black">
                    x ${rec.amount || 0}
                </div>
            </div>
            <div class="mb-4">
                <h4 class="text-md font-black text-gray-800">${medName}</h4>
                <p class="text-[12px] text-gray-500 mt-1 italic">📌 ${rec.diagnosis || '-'}</p>
            </div>
            <div class="grid grid-cols-5 border-t border-gray-50 pt-3 text-center">
                <div><p class="text-[8px] text-gray-400 font-bold">T</p><p class="text-xs font-black text-red-500">${rec.temp || rec.v_temp || '-'}</p></div>
                <div><p class="text-[8px] text-gray-400 font-bold">BP</p><p class="text-xs font-black text-blue-600">${rec.bp || rec.v_bp || '-'}</p></div>
                <div><p class="text-[8px] text-gray-400 font-bold">PR</p><p class="text-xs font-black text-emerald-500">${rec.pr || rec.v_pr || '-'}</p></div>
                <div><p class="text-[8px] text-gray-400 font-bold">RR</p><p class="text-xs font-black text-orange-500">${rec.rr || rec.v_rr || '-'}</p></div>
                <div><p class="text-[8px] text-gray-400 font-bold">O2</p><p class="text-xs font-black text-purple-600">${rec.o2_sat || rec.v_o2 || '-'}</p></div>
            </div>
        </div>`;
    });

    container.innerHTML = html + `</div>`;
}
    function closeHistoryModal() {
        historyModal.classList.add('hidden');
        historyModal.classList.remove('flex');
    }

    // ปิด Modal เมื่อคลิกพื้นหลัง
    window.onclick = function(event) {
        if (event.target == modal) closeModal();
        if (event.target == historyModal) closeHistoryModal();
    }
</script>

<style>
    .scrollbar-custom::-webkit-scrollbar { width: 6px; }
    .scrollbar-custom::-webkit-scrollbar-track { background: #f9fafb; }
    .scrollbar-custom::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
    .animate-in { animation: fadeIn 0.3s ease-out; }
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
</style>
@endsection