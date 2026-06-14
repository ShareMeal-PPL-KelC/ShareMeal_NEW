<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PickupDeadlineReminderNotification extends Notification
{
    use Queueable;

    public $order;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $pickupEndTime = $this->order->pickup_end_time ? substr($this->order->pickup_end_time, 0, 5) : 'waktu batas';
        
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->orderId,
            'status' => 'warning',
            'message' => 'Peringatan: Batas waktu pengambilan pesanan ' . $this->order->orderId . ' Anda tinggal 55 menit lagi (sebelum pukul ' . $pickupEndTime . '). Harap segera mengambil pesanan Anda di mitra.',
            'title' => 'Batas Waktu Pengambilan Hampir Habis',
        ];
    }
}
