<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DonationAvailableNotification extends Notification
{
    use Queueable;

    protected $mitraName;
    protected $donationTitle;
    protected $quantity;

    public function __construct(string $mitraName, string $donationTitle, string $quantity)
    {
        $this->mitraName = $mitraName;
        $this->donationTitle = $donationTitle;
        $this->quantity = $quantity;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Donasi Baru Tersedia!',
            'message' => "{$this->mitraName} baru saja mendonasikan {$this->quantity} {$this->donationTitle}. Segera klaim sebelum diambil lembaga lain!",
            'mitra_name' => $this->mitraName,
            'donation_title' => $this->donationTitle,
            'icon' => '❤️',
            'status' => 'info'
        ];
    }
}
