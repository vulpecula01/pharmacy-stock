<?php

namespace App\Providers;

use Native\Laravel\Facades\Window;
use Native\Laravel\Contracts\ProvidesPhpIni;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
{
    \Native\Laravel\Facades\Window::open()
        ->maximized()      // บังคับขยายหน้าต่างให้เต็มจอ (เห็น Taskbar)
        ->showDevTools(false) 
        ->rememberState(false) // *** สำคัญ: ปิดการจำสถานะเก่าชั่วคราว เพื่อล้างค่าเดิมออก ***
        ->title('Pharmacy Stock Management');
}

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
        ];
    }
}
