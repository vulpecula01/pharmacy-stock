@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto mt-10 p-6 bg-white shadow-xl rounded-2xl">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">จัดการหมวดหมู่ยา</h2>
            <p class="text-sm text-gray-500">จัดการประเภทของยาและเวชภัณฑ์ในระบบ</p>
        </div>
        <button onclick="openAddModal()" 
            class="bg-green-600 text-white px-5 py-2.5 rounded-xl hover:bg-green-700 shadow-lg shadow-green-100 transition-all flex items-center gap-2">
            <i class="fas fa-plus"></i> เพิ่มหมวดหมู่ใหม่
        </button>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-100">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 text-gray-600 uppercase text-[11px] font-bold tracking-wider">
                    <th class="p-4 border-b">ชื่อหมวดหมู่</th>
                    <th class="p-4 border-b text-center">สถานะ</th>
                    <th class="p-4 border-b text-right">จัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($categories as $cat)
                <tr class="hover:bg-blue-50/50 transition-all">
                    <td class="p-4 font-bold text-gray-700">{{ $cat->name }}</td>
                    <td class="p-4 text-center">
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase {{ $cat->is_active ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                            {{ $cat->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                        </span>
                    </td>
                    <td class="p-4 text-right space-x-1">
                        <button onclick="openEditModal({{ $cat->id }}, '{{ $cat->name }}', {{ $cat->is_active }})" 
                            class="p-2 text-blue-500 hover:bg-blue-100 rounded-lg transition-colors">
                            <i class="fas fa-edit"></i>
                        </button>

                        <form action="{{ route('categories.destroy', $cat->id) }}" method="POST" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" onclick="return confirm('ยืนยันการลบหมวดหมู่ {{ $cat->name }}?')" 
                                class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div id="category-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center animate-in fade-in duration-200">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full mx-4 overflow-hidden transform transition-all">
        <div class="p-6 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
            <h3 id="modal-title" class="text-lg font-black text-gray-800">จัดการหมวดหมู่</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="category-form" method="POST" class="p-6 space-y-4">
            @csrf
            <div id="method-field"></div> <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">ชื่อหมวดหมู่</label>
                <input type="text" name="name" id="cat_name_input" 
                    class="w-full border-2 border-gray-100 p-4 rounded-2xl focus:border-blue-500 outline-none transition-all" 
                    placeholder="เช่น ยาเม็ด, ยาน้ำ" required>
            </div>

            <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-2xl">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" id="cat_active_checkbox" value="1" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                </label>
                <span class="text-sm font-bold text-gray-700">เปิดใช้งานสถานะ</span>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeModal()" 
                    class="flex-1 py-4 border-2 border-gray-100 rounded-2xl font-bold text-gray-500 hover:bg-gray-50 transition-all">
                    ยกเลิก
                </button>
                <button type="submit" 
                    class="flex-1 bg-blue-600 text-white py-4 rounded-2xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all">
                    บันทึกข้อมูล
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const modal = document.getElementById('category-modal');
    const form = document.getElementById('category-form');
    const title = document.getElementById('modal-title');
    const nameInput = document.getElementById('cat_name_input');
    const activeCheckbox = document.getElementById('cat_active_checkbox');
    const methodField = document.getElementById('method-field');

    function openAddModal() {
        title.innerText = 'เพิ่มหมวดหมู่ใหม่';
        form.action = "{{ route('categories.store') }}";
        methodField.innerHTML = ''; // เป็นการสร้างใหม่ ไม่ต้องใช้ PUT
        nameInput.value = '';
        activeCheckbox.checked = true;
        modal.classList.remove('hidden');
    }

    function openEditModal(id, name, isActive) {
        title.innerText = 'แก้ไขหมวดหมู่';
        // เปลี่ยน URL เป็น route update
        form.action = `/categories/${id}`;
        methodField.innerHTML = '@method("PUT")'; // ใส่ Method PUT สำหรับ Laravel Update
        nameInput.value = name;
        activeCheckbox.checked = isActive == 1;
        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
    }

    // ปิด Modal เมื่อคลิกพื้นหลัง
    window.onclick = function(event) {
        if (event.target == modal) closeModal();
    }
</script>
@endsection