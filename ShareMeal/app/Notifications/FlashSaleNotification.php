<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FlashSaleNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected $storeName,
        protected $itemName,
        protected $discountPrice
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Flash Sale di Resto Favoritmu!',
            'message' => "{$this->storeName} baru saja memulai flash sale untuk {$this->itemName} seharga Rp " . number_format($this->discountPrice, 0, ',', '.'),
            'store_name' => $this->storeName,
            'item_name' => $this->itemName,
            'icon' => '🔥'
        ];
    }
}
