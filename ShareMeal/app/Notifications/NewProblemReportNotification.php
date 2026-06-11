<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\ProblemReport;

class NewProblemReportNotification extends Notification
{
    use Queueable;

    protected $report;

    /**
     * Create a new notification instance.
     */
    public function __construct(ProblemReport $report)
    {
        $this->report = $report;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $reporterName = $this->report->reporter ? $this->report->reporter->name : 'Pengguna';
        $issueText = '';
        switch ($this->report->issue_type) {
            case 'expired':
                $issueText = 'Makanan Kedaluwarsa';
                break;
            case 'bad_quality':
                $issueText = 'Kualitas Buruk / Basi';
                break;
            case 'mismatch':
                $issueText = 'Tidak Sesuai Deskripsi';
                break;
            default:
                $issueText = 'Masalah Lainnya';
                break;
        }

        return [
            'title' => 'Laporan Masalah Baru',
            'message' => "Laporan baru dari {$reporterName} mengenai '{$issueText}'. Silakan tinjau laporan ini segera.",
            'type' => 'error',
            'report_id' => $this->report->id,
        ];
    }
}
