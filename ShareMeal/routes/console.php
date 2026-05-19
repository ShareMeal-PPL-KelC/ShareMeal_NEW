<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Jalankan auto-donasi rutin untuk memindahkan item yang kedaluwarsa < 2 jam
Schedule::command('sharemeal:auto-donate')->everyMinute()->withoutOverlapping();
