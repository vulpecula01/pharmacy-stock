<script src="https://cdn.tailwindcss.com"></script>

<div class="max-w-2xl mx-auto mt-10 p-6 bg-white shadow-lg rounded-lg">
    <h2 class="text-2xl font-bold mb-6">ระบบเบิกจ่ายยา (Master -> Sub-Stock)</h2>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 mb-4 rounded">{{ session('success') }}</div>
    @endif

    <form action="{{ route('transfers.store') }}" method="POST">
        @csrf
        <div class="mb-4">
            <label class="block">เลือกยา (หรือสแกนบาร์โค้ด)</label>
            <select name="medicine_id" class="w-full border p-2 rounded">
                @foreach($medicines as $item)
                    <option value="{{ $item->id }}">{{ $item->name }} (คงเหลือ: {{ $item->stock_master }})</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-4">
            <div>
                <label class="block">ไปที่คลัง</label>
                <select name="to_location" class="w-full border p-2 rounded">
                    <option value="stock_a">คลัง A</option>
                    <option value="stock_b">คลัง B</option>
                    <option value="stock_c">คลัง C</option>
                </select>
            </div>
            <div>
                <label class="block">จำนวนที่เบิก</label>
                <input type="number" name="quantity" class="w-full border p-2 rounded" min="1" required>
            </div>
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded font-bold hover:bg-blue-700">
            ยืนยันการเบิกจ่าย
        </button>
    </form>
</div>