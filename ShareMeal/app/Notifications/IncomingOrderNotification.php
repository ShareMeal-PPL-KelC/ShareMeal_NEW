<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class IncomingOrderNotification extends Notification
{
    use Queueable;

    protected $order;

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
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'title' => 'Pesanan Baru Masuk!',
            'message' => 'Anda menerima pesanan baru dari ' . ($this->order->customer->name ?? 'Pelanggan') . ' sejumlah Rp ' . number_format($this->order->total_amount, 0, ',', '.'),
            'status' => 'info', // will map to blue info icon in dashboard
        ];
    }
}
