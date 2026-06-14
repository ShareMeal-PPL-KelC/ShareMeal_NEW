<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DonationPreparedNotification extends Notification
{
    use Queueable;

    protected $storeName;
    protected $donationTitle;
    protected $quantity;

    public function __construct(string $storeName, string $donationTitle, string $quantity)
    {
        $this->storeName = $storeName;
        $this->donationTitle = $donationTitle;
        $this->quantity = $quantity;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Donasi Siap Diambil',
            'message' => "Klaim donasi '{$this->donationTitle}' ({$this->quantity}) sudah disiapkan oleh {$this->storeName} dan siap untuk Anda ambil.",
            'type' => 'success',
            'icon' => 'check-circle',
            'action_url' => '/lembaga/donations',
        ];
    }
}
