<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderDelayedNotification extends Notification
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
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        if ($this->order->receiving_method === 'delivery') {
            $message = 'Pesanan Anda sedang diproses, namun kemungkinan akan terlambat datang karena mitra sedang banyak pesanan.';
        } else {
            $message = 'Penyiapan pesanan Anda memerlukan waktu sedikit lebih lama karena antrean di toko sedang padat. Mohon kesediaannya untuk menunggu beberapa saat.';
        }

        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->orderId,
            'status' => 'delayed',
            'message' => $message,
            'title' => 'Pesanan Terlambat (Delay)',
        ];
    }
}
