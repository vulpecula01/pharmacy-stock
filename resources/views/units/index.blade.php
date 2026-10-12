@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto mt-10 p-6 bg-white shadow-lg rounded-2xl">
    <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
        <i class="fas fa-tag text-blue-600"></i> จัดการหน่วยนับยา
    </h2>

    {{-- ฟอร์มเพิ่มหน่วยนับ --}}
    <form action="{{ route('units.store') }}" method="POST" class="flex gap-2 mb-6">
        @csrf
        <input type="text" name="name" class="flex-1 border border-gray-300 p-2 rounded-lg outline-none focus:ring-2 focus:ring-blue-400" placeholder="เช่น เม็ด, ขวด, กล่อง..." required>
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 transition-all">
            + เพิ่ม
        </button>
    </form>

    {{-- รายการหน่วยนับที่มี --}}
    <div class="border rounded-xl overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="p-3 text-sm font-bold text-gray-600">ชื่อหน่วยนับ</th>
                    <th class="p-3 text-sm font-bold text-gray-600 text-center">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($units as $unit)
                <tr class="border-b last:border-none hover:bg-gray-50">
                    <td class="p-3 text-gray-700 font-medium">{{ $unit->name }}</td>
                    <td class="p-3 text-center">
                        <form action="{{ route('units.destroy', $unit->id) }}" method="POST" onsubmit="return confirm('ยืนยันการลบ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection