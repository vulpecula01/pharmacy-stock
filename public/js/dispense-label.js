/**
 * dispense-label.js
 * -----------------------------------------------------------------------
 * โค้ดพิมพ์ฉลากยาให้คนไข้ (แบบสองภาษา ไทย/อังกฤษ ตามชื่อคนไข้) แยกออกมาเป็นไฟล์กลาง
 * เพื่อให้ทั้งหน้า "จ่ายยา" (pharmacy/index.blade.php) และหน้า "ประวัติรับยาคนไข้"
 * (patients/show.blade.php) เรียกใช้ฟังก์ชันเดียวกันได้ ไม่ต้องคัดลอกโค้ด/CSS ซ้ำ
 * (กันปัญหาแก้ที่หนึ่งแล้วอีกหน้าลืมแก้ตาม เพราะโค้ดส่วนนี้ถูกปรับแก้บ่อยมาก)
 *
 * หน้าที่เรียกใช้ไฟล์นี้ต้องตั้งค่า window.PHARMACY_LABEL_CONFIG ก่อน load ไฟล์นี้ เช่น:
 *   window.PHARMACY_LABEL_CONFIG = {
 *       hospitalName: '...', hospitalNameEn: '...',
 *       logoDataUri: 'data:image/png;base64,...' หรือ null,
 *       widthMm: 100, heightMm: 70
 *   };
 */

// ตรวจว่าข้อความมีตัวอักษรไทยอยู่หรือไม่ ใช้แยกว่าพนักงาน/คนไข้คนนี้ชื่อเป็นไทยหรืออังกฤษ
// (พนักงานต่างชาติบางคนถูกกรอกชื่ออังกฤษไว้ในช่อง "ไทย" โดยตรง เพราะไม่มีชื่อไทย)
function isThaiText(str) {
    return !!(str && /[฀-๿]/.test(str));
}

function formatDateThai(dateInput, includeTime = false) {
    if (!dateInput) return '-';
    const d = new Date(dateInput);
    if (isNaN(d.getTime())) return dateInput;
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    let result = `${day}/${month}/${year}`;
    if (includeTime) {
        const time = String(d.getHours()).padStart(2, '0') + ":" + String(d.getMinutes()).padStart(2, '0');
        result += ` ${time}`;
    }
    return result;
}

// กันข้อความที่ผู้ใช้พิมพ์ไปทำลาย HTML/Template literal ตอน render (เช่น </textarea>, `, ${ )
function escapeForHtml(str) {
    return (str || '').toString()
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

// แปลหน่วยนับยา (ไทย) เป็นภาษาอังกฤษ สำหรับแสดงบนฉลากเมื่อพิมพ์เป็นภาษาอังกฤษ
// หากไม่พบหน่วยในตาราง จะแสดงหน่วยเดิม (ภาษาไทย) แทน ไม่ทำให้ข้อมูลหาย
const UNIT_NAME_EN_MAP = {
    'เม็ด': 'tablet(s)', 'ซอง': 'sachet(s)', 'อัน': 'piece(s)', 'ขวด': 'bottle(s)',
    'หลอด': 'tube(s)', 'ตลับ': 'case(s)', 'แผ่น': 'sheet(s)', 'แกลลอน': 'gallon(s)',
    'แผง': 'strip(s)', 'ม้วน': 'roll(s)', 'ห่อ': 'pack(s)', 'กล่อง': 'box(es)',
    'ชิ้น': 'piece(s)', 'ชุด': 'set(s)', 'คู่': 'pair(s)',
    'bot': 'bottle(s)', 'set': 'set(s)', 'Amp': 'ampoule(s)', 'kg': 'kg'
};
function toEnglishUnit(unitTh) {
    if (!unitTh) return '';
    return UNIT_NAME_EN_MAP[unitTh.trim()] || unitTh;
}

// --- พิมพ์ฉลากยาให้คนไข้ แบบภาษาเดียว (ไทยหรืออังกฤษ ตามชื่อคนไข้) เต็มพื้นที่สติกเกอร์ ---
// dispenseDateOverride: ใช้ตอน "พิมพ์ฉลากซ้ำ" จากประวัติคนไข้ เพื่อให้วันที่บนฉลากตรงกับวันที่จ่ายยาจริง (ไม่ใช่วันที่กดพิมพ์ซ้ำ)
function printDispenseLabels(items, patientName, dispenseDateOverride = null) {
    const cfg = window.PHARMACY_LABEL_CONFIG || {};
    const hospitalName = cfg.hospitalName || '';
    const hospitalNameEn = cfg.hospitalNameEn || '';
    const hospitalLogoDataUri = cfg.logoDataUri || null;
    const widthMm = cfg.widthMm || 100;
    const heightMm = cfg.heightMm || 50;
    const dispenseDateThai = formatDateThai(dispenseDateOverride || new Date());
    const useThai = isThaiText(patientName);
    function pickLang(thaiVal, enVal) {
        if (useThai) return thaiVal || enVal || '';
        return enVal || thaiVal || '';
    }

    const pages = items.map(item => {
        const qtyThText = (item.qty !== undefined && item.qty !== null && item.qty !== '')
            ? `${item.qty} ${item.unit_name || ''}`.trim() : '';
        const qtyEnText = (item.qty !== undefined && item.qty !== null && item.qty !== '')
            ? `${item.qty} ${toEnglishUnit(item.unit_name)}`.trim() : '';
        const qtyText = pickLang(qtyThText, qtyEnText);
        const usageText = pickLang(item.usage_instruction, item.usage_instruction_en);
        const indicationText = pickLang(item.indication, item.indication_en);
        const sideEffectText = pickLang(item.side_effect, item.side_effect_en);
        return `
        <div class="label-page">
            <div class="label-header">
                ${hospitalLogoDataUri ? `<img src="${hospitalLogoDataUri}" class="label-logo" alt="logo">` : ''}
                <div class="label-header-text">
                    <div class="label-header-th">${escapeForHtml(hospitalName)}</div>
                    ${hospitalNameEn ? `<div class="label-header-en">${escapeForHtml(hospitalNameEn)}</div>` : ''}
                </div>
            </div>
            <div class="label-body">
                <div class="label-patient-row">${useThai ? 'ผู้ป่วย' : 'Patient'}: ${escapeForHtml(patientName) || '-'}</div>
                <div class="label-medicine-row">
                    <span class="label-medicine">${escapeForHtml(item.name)}</span>
                    ${qtyText ? `<span class="label-qty">
                        <span class="label-qty-th">${escapeForHtml(qtyText)}</span>
                    </span>` : ''}
                </div>
                ${usageText ? `<div class="label-usage">${escapeForHtml(usageText)}</div>` : ''}
                ${indicationText ? `<div class="label-indication">${useThai ? 'สรรพคุณ' : 'Indication'}: ${escapeForHtml(indicationText)}</div>` : ''}
                ${sideEffectText ? `<div class="label-side-effect">&#9888; ${useThai ? 'ผลข้างเคียง' : 'Side effects'}: ${escapeForHtml(sideEffectText)}</div>` : ''}
            </div>
            <div class="label-footer">${useThai ? 'จ่ายยาวันที่' : 'Dispensed on'}: ${dispenseDateThai}</div>
        </div>
    `;
    }).join('');

    const printWin = window.open('', '_blank', 'width=500,height=450');
    if (!printWin) {
        if (window.Swal) {
            Swal.fire('เปิดหน้าต่างพิมพ์ไม่สำเร็จ', 'กรุณาอนุญาต Pop-up ของเว็บไซต์นี้ในเบราว์เซอร์ แล้วลองพิมพ์ฉลากอีกครั้ง', 'warning');
        } else {
            alert('เปิดหน้าต่างพิมพ์ไม่สำเร็จ กรุณาอนุญาต Pop-up ของเว็บไซต์นี้ในเบราว์เซอร์');
        }
        return;
    }
    printWin.document.open();
    printWin.document.write(`
        <!DOCTYPE html>
        <html lang="th">
        <head>
            <meta charset="UTF-8">
            <title>พิมพ์ฉลากยา</title>
            <style>
                @page { size: ${widthMm}mm ${heightMm}mm; margin: 0; }
                * { box-sizing: border-box; }
                html, body {
                    width: 100%; height: 100%; margin: 0; padding: 0;
                    font-family: 'Sarabun', 'TH Sarabun New', Tahoma, sans-serif;
                }
                /* ใช้ 100% แทนการล็อกขนาดเป็น mm ตรงๆ เพื่อให้เนื้อหาขยายเต็มพื้นที่กระดาษจริง
                   เผื่อกรณีไดรเวอร์เครื่องพิมพ์/ตัวเลือกกระดาษของเครื่อง ทำให้ขนาดหน้าที่พิมพ์จริงคลาดเคลื่อนไปจาก ${widthMm}x${heightMm}mm เล็กน้อย */
                .label-page {
                    width: 100%; height: 100%;
                    display: flex; flex-direction: column;
                    border: 1.5pt solid #000;
                    page-break-after: always; page-break-inside: avoid;
                }
                .label-header {
                    display: flex; align-items: center; justify-content: center; gap: 2mm;
                    background: #fff; border-bottom: 1.5pt solid #000;
                    padding: 1.3mm 3mm;
                }
                .label-logo { height: 8.5mm; width: 8.5mm; object-fit: contain; flex-shrink: 0; }
                .label-header-text { text-align: left; }
                .label-header-th { font-weight: 800; font-size: 9.5pt; line-height: 1.15; color: #000; }
                .label-header-en { font-weight: 500; font-size: 7pt; line-height: 1.1; color: #444; margin-top: 0.3mm; }
                /* ขนาดตัวอักษร/ระยะห่างด้านล่างถูกจำกัดไว้ไม่ให้เกินความสูงจริงของสติกเกอร์
                   เพราะถ้าเนื้อหารวมสูงเกินขนาดกระดาษจริง เครื่องพิมพ์/ไดรเวอร์จะตัดพิมพ์ล้นไปอีกแผ่น (ไม่ใช่แค่ถูกซ่อน)
                   จึงใช้ justify-content: flex-start (ไม่ centre) เพื่อให้ถ้าเนื้อหายาวผิดปกติ จะโดนตัดเฉพาะส่วนท้ายเท่านั้น
                   ไม่ใส่ overflow: hidden เพื่อให้เห็นได้ทันทีถ้าเนื้อหายาวเกิน จะได้ปรับแก้ ไม่ใช่หายไปเงียบๆ */
                .label-body {
                    flex: 1; padding: 1.8mm 5mm;
                    display: flex; flex-direction: column; justify-content: flex-start; gap: 1.6mm;
                }
                .label-patient-row {
                    font-size: 9pt; font-weight: 600;
                    border-bottom: 0.75pt dashed #999; padding-bottom: 1mm;
                }
                .label-medicine-row {
                    display: flex; justify-content: space-between; align-items: center; gap: 2mm;
                }
                .label-medicine { font-weight: 800; font-size: 15pt; }
                .label-qty {
                    display: flex; flex-direction: column; align-items: center; white-space: nowrap;
                    background: #eef4ff; border: 1pt solid #4a7fd6; color: #274b8f;
                    padding: 0.8mm 2.5mm; border-radius: 3mm; line-height: 1.15;
                }
                .label-qty-th { font-weight: 700; font-size: 10pt; }
                .label-usage { font-size: 10.5pt; font-weight: 600; white-space: pre-wrap; line-height: 1.2; }
                .label-indication { font-size: 9pt; font-style: italic; color: #333; white-space: pre-wrap; line-height: 1.15; }
                .label-side-effect {
                    font-size: 8.5pt; font-weight: 700; color: #a30000; white-space: pre-wrap; line-height: 1.15;
                    background: #fff3f3; border: 1pt solid #f0b4b4; border-radius: 2mm;
                    padding: 1mm 2.5mm;
                }
                .label-footer {
                    border-top: 0.75pt solid #000; text-align: right;
                    font-size: 7.5pt; color: #333; padding: 1.2mm 5mm;
                }
            </style>
        </head>
        <body>${pages}</body>
        </html>
    `);
    printWin.document.close();
    // เผื่อกรณี onload ไม่ทำงานเพราะเนื้อหาถูกเขียนผ่าน document.write หลังเปิดหน้าต่างเปล่า
    setTimeout(() => { try { printWin.focus(); printWin.print(); } catch (e) {} }, 300);
}
