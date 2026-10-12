<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการคลังยา</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Sarabun', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen p-4 md:p-8">

    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Dashboard คลังยา</h1>
                <p class="text-gray-500">จัดการข้อมูลสต็อกและการเบิกจ่ายยา</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('receives.create') }}" class="bg-green-600 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-green-700 shadow-lg shadow-green-100 transition-all">
                    + รับยาเข้าคลังใหญ่
                </a>
                <a href="{{ route('transfers.create') }}" class="bg-blue-600 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-100 transition-all">
                    + ทำเรื่องเบิกจ่าย
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-50 bg-gray-50/50">
                <h2 class="text-xl font-bold text-gray-700">ยอดคงเหลือทุกโกดัง</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-sm uppercase">
                            <th class="p-4">ข้อมูลยา / บริษัท</th>
                            <th class="p-4 text-center">คลังใหญ่ (Master)</th>
                            <th class="p-4 text-center">คลัง A</th>
                            <th class="p-4 text-center">คลัง B</th>
                            <th class="p-4 text-center">คลัง C</th>
                            <th class="p-4 text-center">วันหมดอายุ</th>
                            <th class="p-4 text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($medicines as $medicine)
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="p-4">
                                <div class="font-bold text-gray-800">{{ $medicine->name }}</div>
                                <div class="text-xs text-gray-400">Barcode: {{ $medicine->barcode }}</div>
                                <div class="text-xs text-blue-500 font-medium">บริษัท: {{ $medicine->company_name ?? 'ไม่ระบุ' }}</div>
                            </td>
                            <td class="p-4 text-center font-bold text-blue-600 bg-blue-50/50">{{ number_format($medicine->stock_master) }}</td>
                            <td class="p-4 text-center">{{ number_format($medicine->stock_a) }}</td>
                            <td class="p-4 text-center">{{ number_format($medicine->stock_b) }}</td>
                            <td class="p-4 text-center">{{ number_format($medicine->stock_c) }}</td>
                            <td class="p-4 text-center">
                                @if($medicine->expire_date)
                                    <span class="px-2 py-1 rounded text-xs {{ $medicine->expire_date->isPast() ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $medicine->expire_date->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-gray-300">-</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
    <div class="flex flex-col items-center gap-1">
        <div class="flex gap-3">
            <a href="{{ route('medicines.edit', $medicine->id) }}" class="text-blue-500 hover:text-blue-700 font-bold text-sm">แก้ไข</a>
            <form action="{{ route('medicines.destroy', $medicine->id) }}" method="POST" onsubmit="return confirm('คุณต้องการลบยาตัวนี้ใช่หรือไม่?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-400 hover:text-red-600 font-bold text-sm">ลบ</button>
            </form>
        </div>
        <form action="{{ route('medicines.clear', $medicine->id) }}" method="POST" onsubmit="return confirm('ต้องการล้างยอดสต็อกทั้งหมดให้เป็น 0 ใช่หรือไม่?')">
            @csrf
            <button type="submit" class="text-xs text-orange-500 hover:text-orange-700 border border-orange-200 px-2 py-0.5 rounded-md hover:bg-orange-50 mt-1 transition-all">
                ล้างสต็อก
            </button>
        </form>
    </div>
</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-50 bg-gray-50/50">
                <h2 class="text-xl font-bold text-gray-700">ประวัติการเบิกจ่าย (10 รายการล่าสุด)</h2>
            </div>
            <div class="p-2">
                @foreach($recentTransfers as $log)
                <div class="flex items-center justify-between p-4 border-b last:border-0 hover:bg-gray-50 rounded-xl transition">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-bold text-gray-800">{{ $log->medicine->name }}</div>
                            <div class="text-xs text-gray-500">{{ $log->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold text-blue-600">+ {{ $log->quantity }} ชิ้น</div>
                        <div class="text-xs text-gray-400">ไปที่: {{ strtoupper(str_replace('stock_', 'คลัง ', $log->to_location)) }}</div>
                    </div>
                </div>
                @endforeach
                @if($recentTransfers->isEmpty())
                    <p class="p-8 text-center text-gray-400 italic">ยังไม่มีประวัติการเบิกจ่าย</p>
                @endif
            </div>
        </div>
    </div>

</body>
</html>