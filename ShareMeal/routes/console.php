<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Jalankan auto-donasi rutin untuk memindahkan item yang kedaluwarsa < 2 jam
Schedule::command('sharemeal:auto-donate')->everyMinute()->withoutOverlapping();

// Kirim peringatan batas waktu pengambilan pesanan konsumen 30 menit sebelum berakhir
Schedule::command('sharemeal:send-pickup-reminders')->everyMinute()->withoutOverlapping();

// Cek pesanan yang mengalami keterlambatan secara otomatis setiap menit
Schedule::command('sharemeal:check-order-delays')->everyMinute()->withoutOverlapping();
