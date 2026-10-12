<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>พิมพ์บาร์โค้ด - {{ $medicine->name }}</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
    <style>
        body { font-family: 'Sarabun', sans-serif; display: flex; justify-content: center; padding-top: 50px; }
        .barcode-container { border: 1px dashed #ccc; padding: 20px; text-align: center; width: 300px; }
        .medicine-name { font-weight: bold; font-size: 18px; margin-bottom: 5px; }
        .company { font-size: 12px; color: #666; margin-bottom: 10px; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="position: fixed; top: 10px; left: 10px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #2563eb; color: white; border: none; border-radius: 5px; cursor: pointer;">
            🖨️ กดเพื่อพิมพ์ (Print)
        </button>
    </div>

    <div class="barcode-container">
        <div class="medicine-name">{{ $medicine->name }}</div>
        <div class="company">{{ $medicine->company_name }}</div>
        <svg id="barcode"></svg>
    </div>

    <script>
        JsBarcode("#barcode", "{{ $medicine->barcode }}", {
            format: "CODE128",
            width: 2,
            height: 60,
            displayValue: true
        });
    </script>
</body>
</html>