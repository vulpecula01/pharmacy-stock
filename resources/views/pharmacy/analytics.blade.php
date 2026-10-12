@extends('layouts.app')

@section('title', 'วิเคราะห์แนวโน้มคลังยา (AI Analytics)')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="max-w-[1700px] mx-auto min-h-screen p-4 bg-gray-50 flex flex-col gap-5">
    
    {{-- ส่วนหัวการ์ดควบคุม --}}
    <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-brain text-purple-600"></i> ระบบวิเคราะห์แนวโน้มและการสำรองยาอัจฉริยะ (AI Demand Forecast)
            </h2>
            <p class="text-xs text-gray-500 mt-0.5">พยากรณ์ความต้องการใช้ยาใน 30 วันข้างหน้าด้วยระบบ Time Series Forecasting เพื่อการบริหารคลังที่มีประสิทธิภาพ</p>
        </div>
        <button onclick="runAiProcessing()" class="bg-purple-600 hover:bg-purple-700 text-white font-bold px-4 py-2 rounded-xl text-sm transition-all flex items-center gap-2 shadow-md active:scale-95">
            <i class="fas fa-sync-alt" id="sync-icon"></i> สั่งให้ AI ประมวลผลและอัปเดตข้อมูลคลัง
        </button>
    </div>

    {{-- โซนแจ้งเตือนวิกฤต: สต็อกปัจจุบัน < ยอดที่ AI คาดว่าจะใช้ --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
        
        {{-- ฝั่งซ้าย: ตารางสถานะและข้อเสนอแนะจัดซื้อ --}}
        <div class="lg:col-span-7 bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-col gap-3">
            <h3 class="text-base font-bold text-gray-800 flex items-center gap-2"><i class="fas fa-clipboard-list text-blue-600"></i> ตารางเปรียบเทียบสต็อกจริง และ ยอดพยากรณ์ความต้องการ (30 วันข้างหน้า)</h3>
            
            <div class="overflow-x-auto rounded-lg border border-gray-100">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-600 font-bold border-b border-gray-100">
                        <tr>
                            <th class="p-3">ชื่อยา / เวชภัณฑ์</th>
                            <th class="p-3 text-center">คลังหลักปัจจุบัน</th>
                            <th class="p-3 text-center">AI พยากรณ์ (30 วันข้างหน้า)</th>
                            <th class="p-3 text-center">สถานะสำรองคลัง</th>
                            <th class="p-3 text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium">
                        @foreach($medicines as $med)
                            @php
                                $predicted = round($med->total_predicted_demand ?? 0, 1);
                                $isShortage = $med->stock_master < $predicted;
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="p-3 font-bold text-gray-800">{{ $med->name }}</td>
                                <td class="p-3 text-center font-mono font-bold text-lg text-slate-700">{{ $med->stock_master }}</td>
                                <td class="p-3 text-center font-mono font-bold text-lg text-purple-600 bg-purple-50/40">{{ $predicted }}</td>
                                <td class="p-3 text-center">
                                    @if($isShortage)
                                        <span class="px-2.5 py-1 rounded-full bg-red-100 text-red-600 text-xs font-bold border border-red-200 block w-fit mx-auto animate-pulse">⚠️ แนะนำสั่งซื้อเพิ่ม</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-600 text-xs font-bold border border-emerald-200 block w-fit mx-auto">✅ สต็อกเพียงพอ</span>
                                    @endif
                                </td>
                                <td class="p-3 text-center">
                                    <button onclick="loadDrugChart({{ $med->id }}, '{{ $med->name }}')" class="bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white px-2.5 py-1.5 rounded-lg text-xs font-bold transition-all">
                                        <i class="fas fa-chart-line mr-1"></i>ดูแนวโน้ม
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ฝั่งขวา: กราฟเส้นแสดงแนวโน้มอนาคตเปรียบเทียบอดีตรายตัว --}}
        <div class="lg:col-span-5 flex flex-col gap-4">
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 min-h-[400px] flex flex-col">
                <h3 id="chart-title" class="text-base font-bold text-gray-800 flex items-center gap-2 mb-4">
                    <i class="fas fa-chart-area text-purple-600"></i> กราฟพยากรณ์ความต้องการรายวันจาก AI
                </h3>
                
                <div class="flex-1 flex items-center justify-center relative bg-slate-50 border border-dashed border-gray-200 rounded-xl p-2">
                    <div id="chart-placeholder" class="text-center text-gray-400 italic text-sm">
                        <i class="fas fa-chart-line text-3xl text-gray-300 mb-2 block"></i>
                        กรดเลือกปุ่ม "ดูแนวโน้ม" ยาจากตารางด้านซ้าย เพื่อพลอตกราฟพยากรณ์เจาะลึก
                    </div>
                    <canvas id="drugTrendChart" class="hidden w-full h-full"></canvas>
                </div>
            </div>

            {{-- ชาร์ตวงกลม 5 กลุ่มอาการยอดฮิต --}}
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                <h3 class="text-base font-bold text-gray-800 mb-3"><i class="fas fa-user-md text-emerald-500"></i> สัดส่วนกลุ่มอาการที่มารับบริการห้องพยาบาล</h3>
                <div class="grid grid-cols-2 gap-4 items-center">
                    <div class="h-40">
                        <canvas id="symptomPieChart"></canvas>
                    </div>
                    <div class="text-xs space-y-1.5 text-gray-600">
                        @foreach($symptomTrends as $index => $st)
                            <div class="flex justify-between items-center p-1.5 rounded bg-gray-50 font-medium">
                                <span class="truncate pr-1">📌 {{ $st->symptom_groups }}</span>
                                <span class="font-bold text-blue-600 shrink-0 font-mono">{{ $st->count }} ครั้ง</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    let trendChartInstance = null;

    // ฟังก์ชันยิง API ดึงข้อมูลมาพล็อตชาร์ตรายตัว
    function loadDrugChart(id, name) {
        document.getElementById('chart-placeholder').classList.add('hidden');
        const canvas = document.getElementById('drugTrendChart');
        canvas.classList.remove('hidden');

        document.getElementById('chart-title').innerHTML = `<i class="fas fa-chart-line text-blue-600"></i> แนวโน้มความต้องการ: <span class="text-purple-600 font-extrabold font-sans">${name}</span>`;

        fetch(`/pharmacy/analytics/drug-trend/${id}`)
            .then(res => res.json())
            .then(data => {
                const labels = [];
                const historyData = [];
                const predictData = [];

                // รวมข้อมูลประวัติย้อนหลัง
                data.history.forEach(item => {
                    labels.push(formatDateSimple(item.date));
                    historyData.push(item.total);
                    predictData.push(null); // วันเก่าไม่มีผลทำนายอนาคต
                });

                // รวมข้อมูลพยากรณ์อนาคต
                data.predictions.forEach(item => {
                    labels.push(formatDateSimple(item.date));
                    historyData.push(null); // วันอนาคตไม่มีข้อมูลประวัติเก่า
                    predictData.push(item.total);
                });

                if (trendChartInstance) trendChartInstance.destroy(); // ล้างชาร์ตเก่าออกก่อนพล็อตใหม่

                trendChartInstance = new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'ยอดใช้จริงในอดีต (30 วันที่ผ่านมา)',
                                data: historyData,
                                borderColor: '#3b82f6',
                                backgroundColor: '#93c5fd',
                                borderDash: [],
                                tension: 0.2
                            },
                            {
                                label: 'แนวโน้มความต้องการที่ AI ทำนาย (30 วันข้างหน้า)',
                                data: predictData,
                                borderColor: '#a855f7',
                                backgroundColor: '#e9d5ff',
                                borderDash: [5, 5], // ทำเป็นเส้นประเพื่อบ่งบอกว่าเป็นอนาคต
                                tension: 0.2
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'top', labels: { font: { weight: 'bold', size: 11 } } } },
                        scales: { y: { beginAtZero: true, title: { display: true, text: 'จำนวน (หน่วยเม็ด/ขวด/ชิ้น)' } } }
                    }
                });
            });
    }

    function formatDateSimple(dateStr) {
        const d = new Date(dateStr);
        return d.toLocaleDateString('th-TH', { day: 'numeric', month: 'short' });
    }

    // ฟังก์ชันสั่งเรียกให้ Python ทำงานเพื่อคำนวณซ้ำ
    function runAiProcessing() {
        const icon = document.getElementById('sync-icon');
        icon.classList.add('fa-spin');
        
        Swal.fire({
            title: 'กำลังคำนวณด้วย AI...',
            text: 'โมเดลกำลังประมวลผลข้อมูล Seasonality และแนวโน้มความต้องการในอนาคต กรุณารอสักครู่',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        fetch("{{ route('pharmacy.analytics.trigger-ai') }}", {
            method: "POST",
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(res => res.json())
        .then(data => {
            icon.classList.remove('fa-spin');
            if (data.success) {
                Swal.fire('ประมวลผลสำเร็จ', data.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถคำนวณข้อมูลได้', 'error');
            }
        }).catch(() => {
            icon.classList.remove('fa-spin');
            Swal.fire('เกิดข้อผิดพลาด', 'Network Error', 'error');
        });
    }

    // พล็อตชาร์ตวงกลมกลุ่มอาการเริ่มต้นตอนโหลดหน้า
    window.onload = () => {
        const pieCtx = document.getElementById('symptomPieChart').getContext('2d');
        const trends = @json($symptomTrends);
        
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: trends.map(t => t.symptom_groups),
                datasets: [{
                    data: trends.map(t => t.count),
                    backgroundColor: ['#60a5fa', '#34d399', '#f87171', '#fbbf24', '#c084fc']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } } // ปิด Legend เพราะเราทำตารางด้านข้างอธิบายแล้ว
            }
        });
    };
</script>
@endsection