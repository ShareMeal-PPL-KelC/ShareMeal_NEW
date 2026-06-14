<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Product;

class AutoDonatedNotification extends Notification
{
    use Queueable;

    protected $product;
    protected $quantity;

    public function __construct(Product $product, int $quantity)
    {
        $this->product = $product;
        $this->quantity = $quantity;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Produk Dialihkan ke Donasi',
            'message' => "Produk '{$this->product->name}' Anda yang tersisa {$this->quantity} porsi telah dialihkan menjadi donasi sosial otomatis karena mendekati batas kedaluwarsa.",
            'type' => 'info',
            'icon' => 'heart',
            'action_url' => route('mitra.donations')
        ];
    }
}
