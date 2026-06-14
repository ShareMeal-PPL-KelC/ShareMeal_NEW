<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DonationHandedOverNotification extends Notification
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
            'title' => 'Donasi Telah Diserahkan',
            'message' => "Donasi '{$this->donationTitle}' ({$this->quantity}) telah diserahkan oleh Mitra '{$this->mitraName}' dan selesai.",
            'type' => 'success',
            'icon' => 'check-circle',
            'action_url' => route('profile.edit')
        ];
    }
}
