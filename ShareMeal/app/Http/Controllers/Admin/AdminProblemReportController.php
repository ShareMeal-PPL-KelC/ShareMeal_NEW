<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\ProblemReport;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminProblemReportController extends Controller
{
    public function adminProblemReports(): View
    {
        $reports = ProblemReport::with(['reporter', 'mitra', 'order', 'donation'])
            ->latest()
            ->paginate(15);

        return view('pages.admin.problem_reports', $this->dashboardData('admin', 'Laporan Masalah', 'Moderasi dan tindak lanjut laporan makanan bermasalah') + [
            'reports' => $reports,
        ]);
    }

    public function adminDismissReport(int $reportId): RedirectResponse
    {
        $report = ProblemReport::findOrFail($reportId);
        $report->update(['status' => 'dismissed']);

        if ($report->reporter) {
            $report->reporter->notify(new \App\Notifications\ProblemReportResolvedNotification($report, 'dismissed'));
        }

        AdminLog::create([
            'admin_id' => Auth::id() ?? User::where('role', 'admin')->value('id'),
            'action' => 'report_dismiss',
            'target_id' => $reportId,
            'details' => 'Mengabaikan laporan masalah #' . $reportId . ' (' . $report->issue_label . ')',
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Laporan telah diabaikan.');
    }

    public function adminWarnMitraReport(Request $request, int $reportId): RedirectResponse
    {
        $report = ProblemReport::findOrFail($reportId);
        $mitra = $report->mitra;
        $reason = $request->input('reason') ?: ($report->issue_label . ': ' . $report->description);

        if ($mitra) {
            $mitra->increment('warnings_count');
            $mitra->update([
                'status' => 'warned',
                'last_warning_at' => now(),
                'warning_reason' => $reason,
            ]);

            $mitra->notify(new \App\Notifications\SystemWarningNotification(
                'Peringatan Akun',
                'Akun Anda mendapatkan peringatan resmi. Alasan: ' . $reason
            ));
        }

        $report->update(['status' => 'resolved', 'admin_note' => 'Diberikan peringatan kepada mitra. Alasan: ' . $reason]);

        if ($report->reporter) {
            $report->reporter->notify(new \App\Notifications\ProblemReportResolvedNotification($report, 'warned'));
        }

        AdminLog::create([
            'admin_id' => Auth::id() ?? User::where('role', 'admin')->value('id'),
            'action' => 'report_warn',
            'target_id' => $reportId,
            'details' => 'Menindaklanjuti laporan #' . $reportId . ' dengan memberi peringatan ke Mitra ' . ($mitra ? $mitra->displayName : 'Tidak Diketahui') . '. Alasan: ' . $reason,
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Peringatan telah dikirimkan kepada mitra.');
    }

    public function adminBlockMitraReport(Request $request, int $reportId): RedirectResponse
    {
        $report = ProblemReport::findOrFail($reportId);
        $mitra = $report->mitra;
        $reason = $request->input('reason') ?: ('Pelanggaran berat/berulang berdasarkan laporan: ' . $report->issue_label);

        if ($mitra) {
            $mitra->update([
                'status' => 'blocked',
                'blocked_at' => now(),
                'block_reason' => $reason,
            ]);

            $mitra->notify(new \App\Notifications\SystemWarningNotification(
                'Akun Diblokir',
                'Akun Anda telah dinonaktifkan permanen oleh Admin. Alasan: ' . $reason
            ));
        }

        $report->update(['status' => 'resolved', 'admin_note' => 'Mitra telah diblokir secara permanen. Alasan: ' . $reason]);

        if ($report->reporter) {
            $report->reporter->notify(new \App\Notifications\ProblemReportResolvedNotification($report, 'blocked'));
        }

        AdminLog::create([
            'admin_id' => Auth::id() ?? User::where('role', 'admin')->value('id'),
            'action' => 'report_block',
            'target_id' => $reportId,
            'details' => 'Menindaklanjuti laporan #' . $reportId . ' dengan memblokir Mitra ' . ($mitra ? $mitra->displayName : 'Tidak Diketahui') . '. Alasan: ' . $reason,
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Mitra telah diblokir.');
    }
}
