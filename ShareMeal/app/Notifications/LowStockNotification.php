<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Product;

class LowStockNotification extends Notification
{
    use Queueable;

    protected $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Stok Makanan Menipis',
            'message' => "Stok untuk produk '{$this->product->name}' tersisa {$this->product->stock} porsi. Segera perbarui atau kelola stok Anda.",
            'type' => 'warning',
            'product_id' => $this->product->id,
            'icon' => 'alert-triangle',
            'action_url' => route('mitra.inventory')
        ];
    }
}
