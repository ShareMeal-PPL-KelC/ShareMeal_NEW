<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VerificationApprovedNotification extends Notification
{
    use Queueable;

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

        $orgName = $notifiable->organization_name ?? $notifiable->name;

        return [
            'title' => 'Akun Anda Telah Diverifikasi!',
            'message' => "Selamat! Berkas pendaftaran {$roleLabel} '{$orgName}' telah disetujui dan diverifikasi oleh admin. Sekarang Anda dapat menggunakan seluruh fitur ShareMeal.",
            'type' => 'success',
            'icon' => 'check-circle',
            'action_url' => route('profile.edit')
        ];
    }
}
