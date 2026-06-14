<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\User;

class NewVerificationApplicationNotification extends Notification
{
    use Queueable;

    protected $registeredUser;

    public function __construct(User $registeredUser)
    {
        $this->registeredUser = $registeredUser;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        $roleLabel = match($this->registeredUser->role) {
            'mitra' => 'Mitra Toko',
            'lembaga' => 'Lembaga Sosial',
            default => ucfirst($this->registeredUser->role)
        };

        $orgName = $this->registeredUser->organization_name ?? $this->registeredUser->name;

        return [
            'title' => 'Pendaftaran Akun Baru',
            'message' => "Akun {$roleLabel} Baru: '{$orgName}' ({$this->registeredUser->name}) telah mendaftar dan memerlukan verifikasi berkas.",
            'type' => 'warning',
            'user_id' => $this->registeredUser->id,
            'icon' => 'shield',
            'action_url' => route('admin.verification')
        ];
    }
}
