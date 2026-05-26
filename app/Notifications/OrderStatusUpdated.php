<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderStatusUpdated extends Notification
{
    use Queueable;

    protected $order;
    protected $status;

    /**
     * Create a new notification instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->status = $order->status;
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
        $statusMessages = [
            'pending' => 'Pesanan Anda sedang diproses.',
            'ready' => 'Pesanan Anda siap diambil!',
            'completed' => 'Pesanan Anda telah selesai diambil. Terima kasih!',
            'cancelled' => 'Mohon maaf, pesanan Anda telah dibatalkan.',
        ];

        $message = $statusMessages[$this->status] ?? "Status pesanan Anda telah berubah menjadi {$this->status}.";

        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->orderId,
            'status' => $this->status,
            'message' => $message,
            'title' => 'Update Status Pesanan',
        ];
    }
}
