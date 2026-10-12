<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รับยาเข้าคลังใหญ่</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50 min-h-screen">

<div class="max-w-2xl mx-auto mt-10 p-8 bg-white shadow-xl rounded-2xl border border-gray-100">
    <div class="flex items-center gap-3 mb-8">
        <div class="bg-green-100 p-3 rounded-xl text-green-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
        </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-800">รับยาเข้าคลังใหญ่</h2>
            <p class="text-sm text-gray-500">สแกนบาร์โค้ดเพื่อเพิ่มยาใหม่หรือเพิ่มสต็อกเดิม</p>
        </div>
    </div>

    <form action="{{ route('receives.store') }}" method="POST" class="space-y-6" id="receive-form">
        @csrf
        
        <div class="relative">
            <label class="block text-sm font-bold text-gray-700 mb-2">สแกนบาร์โค้ด / รหัสยา</label>
            <input type="text" name="barcode" id="barcode_input" 
                class="w-full border-2 border-gray-200 p-4 rounded-xl focus:border-green-500 focus:ring-4 focus:ring-green-100 outline-none transition-all text-lg font-mono" 
                placeholder="คลิกที่นี่แล้วสแกน..." autofocus required>
            <div id="loading-spinner" class="absolute right-4 top-11 hidden">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-green-600"></div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">ชื่อยา</label>
            <input type="text" name="name" id="name_input"
                class="w-full border-2 border-gray-200 p-4 rounded-xl focus:border-green-500 outline-none transition-all" 
                placeholder="ชื่อยาจะปรากฏเมื่อสแกน หรือพิมพ์กรณีเพิ่มใหม่" required>
            <span id="status-msg" class="text-xs mt-1 block"></span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">บริษัทผู้จำหน่าย (เช่น AAA)</label>
                <input type="text" name="company_name" id="company_input"
                    class="w-full border-2 border-gray-200 p-4 rounded-xl focus:border-green-500 outline-none transition-all" 
                    placeholder="ระบุบริษัท" required>
            </div>
            <div>
                <label class="block text-sm font-bold text-red-600 mb-2">วันหมดอายุ (Expire)</label>
                <input type="date" name="expire_date" id="expire_input"
                    class="w-full border-2 border-red-100 p-4 rounded-xl focus:border-red-500 outline-none transition-all" required>
            </div>
        </div>

        <div>
            <label class="block text-sm font-bold text-gray-700 mb-2">จำนวนที่รับเข้าคลังใหญ่ (Master Stock)</label>
            <input type="number" name="quantity" 
                class="w-full border-2 border-gray-200 p-4 rounded-xl focus:border-green-500 outline-none transition-all text-2xl font-bold text-center" 
                min="1" value="1" required>
        </div>

        <div class="flex gap-4 pt-4">
            <a href="{{ route('transfers.index') }}" 
                class="flex-1 text-center py-4 border-2 border-gray-200 rounded-2xl font-bold text-gray-500 hover:bg-gray-50 transition-all">
                ยกเลิก
            </a>
            <button type="submit" 
                class="flex-1 bg-green-600 text-white py-4 rounded-2xl font-bold hover:bg-green-700 shadow-lg shadow-green-200 transform hover:-translate-y-1 transition-all">
                ยืนยันการรับเข้า
            </button>
        </div>
    </form>
</div>

<script>
    const barcodeInput = document.getElementById('barcode_input');
    const nameInput = document.getElementById('name_input');
    const companyInput = document.getElementById('company_input');
    const expireInput = document.getElementById('expire_input');
    const spinner = document.getElementById('loading-spinner');
    const statusMsg = document.getElementById('status-msg');

    // ฟังก์ชันดึงข้อมูลยาเมื่อสแกนบาร์โค้ด
    barcodeInput.addEventListener('change', function() {
        const barcode = this.value;
        if (!barcode) return;

        // แสดงตัวโหลด
        spinner.classList.remove('hidden');
        statusMsg.innerText = 'กำลังตรวจสอบข้อมูล...';
        statusMsg.className = 'text-xs mt-1 block text-blue-500';

        fetch(`/api/check-medicine/${barcode}`)
            .then(response => response.json())
            .then(data => {
                spinner.classList.add('hidden');
                
                if (data.exists) {
                    // กรณีมียาเดิม: ดึงข้อมูลมาใส่ให้
                    nameInput.value = data.name;
                    companyInput.value = data.company_name || '';
                    expireInput.value = data.expire_date || '';
                    
                    statusMsg.innerText = 'พบข้อมูลยาเดิมในระบบ';
                    statusMsg.className = 'text-xs mt-1 block text-green-600 font-bold';
                    
                    // เลื่อนโฟกัสไปที่ช่องจำนวนทันทีเพื่อความเร็ว
                    document.querySelector('input[name="quantity"]').focus();
                } else {
                    // กรณีเป็นยาใหม่: ล้างค่าเพื่อให้กรอกใหม่
                    nameInput.value = '';
                    nameInput.placeholder = 'ไม่พบข้อมูล! กรุณาระบุชื่อยาใหม่';
                    
                    statusMsg.innerText = 'ไม่พบข้อมูลบาร์โค้ดนี้ (ยาใหม่)';
                    statusMsg.className = 'text-xs mt-1 block text-orange-500 font-bold';
                    
                    nameInput.focus();
                }
            })
            .catch(error => {
                spinner.classList.add('hidden');
                console.error('Error:', error);
            });
    });

    // ป้องกันการกด Enter แล้ว Submit ฟอร์มทันที (ให้ JavaScript ทำงานก่อน)
    barcodeInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            this.blur(); // บังคับให้เกิด Event 'change'
        }
    });
</script>

</body>
</html>