<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewMenuAvailableNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected $storeName,
        protected $itemName,
        protected $price
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Menu Baru Ditambahkan!',
            'message' => "{$this->storeName} baru saja menambahkan menu baru: {$this->itemName} seharga Rp " . number_format($this->price, 0, ',', '.'),
            'store_name' => $this->storeName,
            'item_name' => $this->itemName,
            'icon' => 'utensils',
            'status' => 'success',
            'action_url' => route('consumer.search', ['q' => $this->storeName])
        ];
    }
}
