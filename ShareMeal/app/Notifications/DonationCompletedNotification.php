<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DonationCompletedNotification extends Notification
{
    use Queueable;

    protected $lembagaName;
    protected $donationTitle;
    protected $quantity;

    public function __construct(string $lembagaName, string $donationTitle, string $quantity)
    {
        $this->lembagaName = $lembagaName;
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
            'title' => 'Donasi Selesai Diambil',
            'message' => "Donasi '{$this->donationTitle}' ({$this->quantity}) dikonfirmasi telah selesai diambil oleh {$this->lembagaName}.",
            'type' => 'success',
            'icon' => 'check-circle',
            'action_url' => '/mitra/donations',
        ];
    }
}
