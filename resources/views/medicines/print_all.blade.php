<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>พิมพ์บาร์โค้ดยาทั้งหมด</title>
    <style>
        /* จัดการหน้าจอปกติ (Screen) */
        body { font-family: 'Helvetica', sans-serif; background: #f4f7f6; padding: 20px; }
        .no-print-area { background: #fff; padding: 15px; border-bottom: 1px solid #ddd; position: sticky; top: 0; z-index: 100; display: flex; justify-content: space-between; align-items: center; }
        
        /* 🔥 เลือกจำนวนแถว (Grid Columns) ตรงนี้ 🔥 */
        .barcode-grid {
            display: grid;
            gap: 15px;
            padding: 20px;
        }
        
        /* เลือกจำนวนแถวสำหรับหน้าจอ (Screen) */
        @media screen and (min-width: 768px) {
            .barcode-grid { grid-template-columns: repeat(3, 1fr); } /* แสดง 3 แถวบนหน้าจอ */
        }
        @media screen and (min-width: 1024px) {
            .barcode-grid { grid-template-columns: repeat(4, 1fr); } /* แสดง 4 แถวบนหน้าจอ */
        }
        
        .barcode-item {
            background: #fff; border: 1px solid #ddd; padding: 10px; text-align: center;
            border-radius: 8px; display: flex; flex-direction: column; align-items: center; justify-content: center;
        }

        /* ตั้งค่าสำหรับการพิมพ์ (Print) */
        @media print {
            body { background: white; padding: 0; margin: 0; }
            .no-print-area { display: none !important; } /* ซ่อนเมนู */
            
            .barcode-grid {
                display: grid !important;
                gap: 10px !important;
                padding: 10mm !important; /* เว้นขอบหน้ากระดาษ */
                
                /* 🔥 🔥 🔥 เลือกจำนวนแถว (Grid Columns) ตรงนี้ 🔥 🔥 🔥 */
                grid-template-columns: repeat(3, 1fr) !important; /* ปรับเป็น repeat(3, 1fr) หรือ repeat(4, 1fr) */
            }

            .barcode-item {
                page-break-inside: avoid; /* ห้ามตัดดวงบาร์โค้ดระหว่างหน้า */
                border: 1px solid #eee !important;
                padding: 5px !important;
            }

            @page {
                size: A4;
                margin: 1cm;
            }
        }
    </style>
</head>
<body>

    {{-- ส่วนปุ่มควบคุม (ซ่อนเวลาพิมพ์) --}}
    <div class="no-print-area border-b border-gray-100 shadow-sm rounded-xl mb-4">
        <h1 class="text-xl font-bold text-gray-800">รายการบาร์โค้ดยาทั้งหมด ({{ $medicines->count() }})</h1>
        <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold">
            🖨️ พิมพ์ทั้งหมด
        </button>
    </div>

    {{-- 🔥 ตารางบาร์โค้ด --}}
    <div class="barcode-grid">
        @foreach($medicines as $medicine)
            <div class="barcode-item">
                {{-- 1. ชื่อยา --}}
                <span class="text-[12px] font-bold truncate w-full mb-2 text-center">{{ $medicine->name }}</span>
                
                {{-- 2. ตัวบาร์โค้ด (ใช้ API แทน Class DNS1D) --}}
                <div class="mb-2">
                    <img src="https://bwipjs-api.metafloor.com/?bcid=code128&text={{ urlencode($medicine->barcode) }}&scale=2&rotate=N&includetext=false" 
                         alt="{{ $medicine->barcode }}" 
                         style="height: 45px; width: auto;">
                </div>
                
                {{-- 3. เลขบาร์โค้ดและชื่อบริษัท --}}
                <span class="text-[10px] font-mono tracking-widest">{{ $medicine->barcode }}</span>
               
            </div>
        @endforeach
    </div>

</body>
</html>