<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DonationCancelledNotification extends Notification
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

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Donasi Dibatalkan oleh Mitra',
            'message' => "Maaf, donasi '{$this->donationTitle}' ({$this->quantity}) yang telah Anda klaim dibatalkan oleh Mitra '{$this->mitraName}'.",
            'type' => 'error',
            'icon' => 'x-circle',
            'action_url' => route('profile.edit')
        ];
    }
}
