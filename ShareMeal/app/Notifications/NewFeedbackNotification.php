<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Feedback;

class NewFeedbackNotification extends Notification
{
    use Queueable;

    protected $feedback;

    public function __construct(Feedback $feedback)
    {
        $this->feedback = $feedback;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        $userName = $this->feedback->user ? $this->feedback->user->name : 'Pengguna';
        $userRole = $this->feedback->user ? match($this->feedback->user->role) {
            'mitra' => 'Mitra',
            'lembaga' => 'Lembaga',
            'consumer' => 'Konsumen',
            default => ucfirst($this->feedback->user->role)
        } : 'Pengguna';

        $categoryLabel = match($this->feedback->category) {
            'fitur' => 'Saran Fitur',
            'bug' => 'Laporan Bug',
            'ui_ux' => 'Tampilan / UI/UX',
            'other' => 'Lain-lain',
            default => ucfirst($this->feedback->category)
        };

        return [
            'title' => 'Feedback Pengguna',
            'message' => "{$userName} ({$userRole}) mengirimkan feedback [{$categoryLabel}]: '{$this->feedback->subject}'",
            'type' => 'info',
            'feedback_id' => $this->feedback->id,
            'icon' => 'message-square',
            'action_url' => route('admin.feedbacks.index')
        ];
    }
}
