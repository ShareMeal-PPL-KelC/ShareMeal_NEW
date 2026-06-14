<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProductExpiredNotification extends Notification
{
    use Queueable;

    protected $product;

    /**
     * Create a new notification instance.
     */
    public function __construct(Product $product)
    {
        $this->product = $product;
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
        return [
            'title' => 'Produk Kedaluwarsa',
            'message' => "Produk '{$this->product->name}' telah kedaluwarsa dan otomatis diturunkan dari etalase.",
            'type' => 'error',
            'status' => 'expired',
            'product_id' => $this->product->id,
            'icon' => 'x-circle',
            'action_url' => route('mitra.inventory')
        ];
    }
}
