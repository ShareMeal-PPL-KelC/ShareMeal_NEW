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
        $mitraName = $this->report->mitra->name ?? 'Mitra';

        if ($this->resolutionType === 'dismissed') {
            return [
                'title' => 'Laporan Masalah Ditindaklanjuti',
                'message' => "Laporan Anda mengenai '{$issueLabel}' terhadap Mitra '{$mitraName}' telah diabaikan/ditutup oleh admin.",
                'type' => 'info',
                'icon' => 'eye-off',
                'status' => 'dismissed',
                'report_id' => $this->report->id,
                'action_url' => route('profile.edit')
            ];
        }

        $moderationAction = $this->resolutionType === 'warned' ? 'sanksi peringatan resmi' : 'memblokir akun Mitra';

        return [
            'title' => 'Laporan Masalah Ditindaklanjuti',
            'message' => "Terima kasih atas laporan Anda. Pengaduan mengenai '{$issueLabel}' terhadap Mitra '{$mitraName}' telah ditindaklanjuti oleh tim admin dengan tindakan: {$moderationAction}.",
            'type' => 'success',
            'icon' => 'shield',
            'status' => 'resolved',
            'report_id' => $this->report->id,
            'action_url' => route('profile.edit')
        ];
    }
}
