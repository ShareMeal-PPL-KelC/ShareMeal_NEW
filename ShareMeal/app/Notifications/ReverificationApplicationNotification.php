<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\User;

class ReverificationApplicationNotification extends Notification
{
    use Queueable;

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        $roleLabel = match($this->user->role) {
            'mitra' => 'Mitra Toko',
            'lembaga' => 'Lembaga Sosial',
            default => ucfirst($this->user->role)
        };

        $orgName = $this->user->organization_name ?? $this->user->name;

        return [
            'title' => 'Pengajuan Ulang Verifikasi',
            'message' => "Akun {$roleLabel}: '{$orgName}' ({$this->user->name}) telah mengunggah ulang berkas dokumen dan memerlukan verifikasi.",
            'type' => 'warning',
            'user_id' => $this->user->id,
            'icon' => 'shield-alert',
            'action_url' => route('admin.verification')
        ];
    }
}
