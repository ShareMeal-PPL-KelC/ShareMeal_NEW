<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Notifications\PickupDeadlineReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendPickupDeadlineReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sharemeal:send-pickup-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send pickup deadline reminders to customers 55 minutes before expiration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orders = Order::whereIn('status', ['pending', 'ready'])
            ->where('receiving_method', 'pickup')
            ->get();

        $count = 0;
        foreach ($orders as $order) {
            $expiresAt = $order->expires_at;
            if (!$expiresAt) {
                continue;
            }

            // Hitung selisih menit antara sekarang dan waktu batas ambil
            $diffInMinutes = now()->diffInMinutes($expiresAt, false);

            // Jika batas waktu ambil tersisa antara 51 sampai 55 menit (5 menit setelah pemesanan 1 jam)
            if ($diffInMinutes >= 51 && $diffInMinutes <= 55) {
                // Pastikan belum pernah dikirim notifikasi untuk order ini
                $alreadyNotified = DB::table('notifications')
                    ->where('notifiable_id', $order->customer_id)
                    ->where('data', 'like', '%"order_id":' . $order->id . '%')
                    ->where('type', PickupDeadlineReminderNotification::class)
                    ->exists();

                if (!$alreadyNotified && $order->customer) {
                    $order->customer->notify(new PickupDeadlineReminderNotification($order));
                    $count++;
                }
            }
        }

        $this->info("Berhasil mengirim $count notifikasi peringatan batas ambil.");
    }
}
