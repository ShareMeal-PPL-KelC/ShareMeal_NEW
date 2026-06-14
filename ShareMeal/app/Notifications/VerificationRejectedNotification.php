<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VerificationRejectedNotification extends Notification
{
    use Queueable;

    protected $reason;

    public function __construct(string $reason)
    {
        $this->reason = $reason;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        $roleLabel = match($notifiable->role) {
            'mitra' => 'Mitra Toko',
            'lembaga' => 'Lembaga Sosial',
            default => ucfirst($notifiable->role)
        };

        return [
            'title' => 'Verifikasi Akun Ditolak',
            'message' => "Maaf, pengajuan verifikasi berkas {$roleLabel} Anda ditolak oleh admin dengan alasan: '{$this->reason}'. Silakan perbarui dokumen Anda.",
            'type' => 'error',
            'icon' => 'x-circle',
            'action_url' => route('profile.edit')
        ];
    }
}
