<?php

namespace App\Filament\Resources\TransferResource\Pages;

use App\Filament\Resources\TransferResource;
use App\Models\Medicine;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTransfer extends CreateRecord
{
    protected static string $resource = TransferResource::class;

    /**
     * ขั้นตอนก่อนบันทึก: ตรวจสอบว่ายาในคลังใหญ่พอให้เบิกหรือไม่
     */
    protected function beforeCreate(): void
    {
        $data = $this->data; // ดึงข้อมูลจากฟอร์มที่กรอกเข้ามา
        $medicine = Medicine::find($data['medicine_id']);
        $sourceField = $data['from_location']; // เช่น 'stock_master'
        $requestedAmount = $data['quantity'];

        // ตรวจสอบว่ายอดคงเหลือในคลังต้นทางพอกับที่ระบุหรือไม่
        if ($medicine->$sourceField < $requestedAmount) {
            Notification::make()
                ->title('ยอดคงเหลือไม่พอ')
                ->body("ยาในคลังต้นทางมีเพียง {$medicine->$sourceField} ชิ้น ไม่สามารถเบิก {$requestedAmount} ชิ้นได้")
                ->danger()
                ->send();

            $this->halt(); // หยุดการบันทึกข้อมูลลง Database ทันที
        }
    }

    /**
     * ขั้นตอนหลังบันทึก: ทำการตัดสต็อกจริง
     */
    protected function afterCreate(): void
    {
        $record = $this->record; // ข้อมูล Transfer ที่เพิ่งบันทึกสำเร็จ
        $medicine = $record->medicine;

        if ($medicine) {
            // ตัดยอดจากคลังต้นทาง และเพิ่มยอดในคลังปลายทาง
            $medicine->decrement($record->from_location, $record->quantity);
            $medicine->increment($record->to_location, $record->quantity);

            Notification::make()
                ->title('เบิกจ่ายสำเร็จ')
                ->success()
                ->send();
        }
    }

    // หลังจากบันทึกเสร็จ ให้เด้งกลับไปหน้า List (รายการประวัติการเบิก)
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    public static function getPages(): array
{
    return [
        'index' => Pages\ListTransfers::route('/'),
        'create' => Pages\CreateTransfer::route('/create'),
        'edit' => Pages\EditTransfer::route('/{record}/edit'),
    ];
}
}