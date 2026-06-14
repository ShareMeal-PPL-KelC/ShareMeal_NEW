<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\ProblemReport;

class ProblemReportResolvedNotification extends Notification
{
    use Queueable;

    protected $report;
    protected $resolutionType;

    public function __construct(ProblemReport $report, string $resolutionType)
    {
        $this->report = $report;
        $this->resolutionType = $resolutionType;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        $issueLabel = $this->report->issue_label ?? 'Laporan Masalah';

        if ($this->resolutionType === 'dismissed') {
            return [
                'title' => 'Laporan Masalah Ditutup',
                'message' => "Laporan Anda mengenai '{$issueLabel}' telah ditinjau oleh tim kami dan dinyatakan selesai/ditutup.",
                'type' => 'info',
                'icon' => 'eye-off',
                'action_url' => route('profile.edit')
            ];
        }

        return [
            'title' => 'Laporan Masalah Ditindaklanjuti',
            'message' => "Terima kasih atas laporan Anda. Pengaduan mengenai '{$issueLabel}' telah ditindaklanjuti oleh tim admin dengan pemberian sanksi/moderasi.",
            'type' => 'success',
            'icon' => 'shield',
            'action_url' => route('profile.edit')
        ];
    }
}
