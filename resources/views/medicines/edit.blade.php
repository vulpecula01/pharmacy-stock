<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลยา</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Sarabun', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen p-4 md:p-8">

    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-50 bg-blue-600 text-white">
                <h2 class="text-2xl font-bold">แก้ไขข้อมูลยา</h2>
                <p class="text-blue-100">แก้ไขรายละเอียดของ {{ $medicine->name }}</p>
            </div>

            <form action="{{ route('medicines.update', $medicine->id) }}" method="POST" class="p-8 space-y-6">
                @csrf
                @method('PUT') <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">ชื่อยา</label>
                    <input type="text" name="name" value="{{ old('name', $medicine->name) }}" 
                        class="w-full border-2 border-gray-200 p-3 rounded-xl focus:border-blue-500 outline-none transition-all" required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">บาร์โค้ด / รหัสยา</label>
                    <input type="text" name="barcode" value="{{ old('barcode', $medicine->barcode) }}" 
                        class="w-full border-2 border-gray-200 p-3 rounded-xl focus:border-blue-500 outline-none transition-all font-mono" required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">บริษัทผู้จำหน่าย</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $medicine->company_name) }}" 
                        class="w-full border-2 border-gray-200 p-3 rounded-xl focus:border-blue-500 outline-none transition-all" required>
                </div>

                <div>
                    <label class="block text-sm font-bold text-red-600 mb-2">วันหมดอายุ (Expire Date)</label>
                    <input type="date" name="expire_date" 
                        value="{{ old('expire_date', $medicine->expire_date ? $medicine->expire_date->format('Y-m-d') : '') }}" 
                        class="w-full border-2 border-red-50/50 border-gray-200 p-3 rounded-xl focus:border-red-500 outline-none transition-all">
                </div>

                <div class="flex gap-4 pt-4">
                    <a href="{{ route('transfers.index') }}" 
                        class="flex-1 text-center py-3 border-2 border-gray-200 rounded-xl font-bold text-gray-500 hover:bg-gray-50 transition-all">
                        ยกเลิก
                    </a>
                    <button type="submit" 
                        class="flex-1 bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-100 transition-all">
                        บันทึกการแก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>