<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\User;
use App\Models\VerificationApplication;
use App\Support\ShareMealState;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminVerificationController extends Controller
{
    public function adminVerification(): View
    {
        return view('pages.admin.verification', $this->dashboardData('admin', 'Verifikasi Mitra & Lembaga Sosial', 'Sistem approval & verifikasi admin') + [
            'applications' => ShareMealState::get('applications'),
            'activeTab' => request('tab', 'pending'),
        ]);
    }

    public function adminApproveApplication(int $applicationId): RedirectResponse
    {
        $app = VerificationApplication::find($applicationId);
        $user = User::find($applicationId);
        $orgName = $user ? ($user->organization_name ?? $user->name) : ($app ? ($app->user?->organization_name ?? $app->user?->name) : 'Aplikasi #' . $applicationId);
        ShareMealState::approveApplication($applicationId);

        if ($user) {
            $user->notify(new \App\Notifications\VerificationApprovedNotification());
        }

        AdminLog::create([
            'admin_id' => Auth::id() ?? User::where('role', 'admin')->value('id'),
            'action' => 'verify_approve',
            'target_id' => $applicationId,
            'details' => 'Menyetujui verifikasi berkas akun: ' . $orgName,
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Aplikasi disetujui.');
    }

    public function adminRejectApplication(Request $request, int $applicationId): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required']]);
        $app = VerificationApplication::find($applicationId);
        $user = User::find($applicationId);
        $orgName = $user ? ($user->organization_name ?? $user->name) : ($app ? ($app->user?->organization_name ?? $app->user?->name) : 'Aplikasi #' . $applicationId);
        ShareMealState::rejectApplication($applicationId, $data['reason']);

        if ($user) {
            $user->notify(new \App\Notifications\VerificationRejectedNotification($data['reason']));
        }

        AdminLog::create([
            'admin_id' => Auth::id() ?? User::where('role', 'admin')->value('id'),
            'action' => 'verify_reject',
            'target_id' => $applicationId,
            'details' => 'Menolak verifikasi berkas akun: ' . $orgName . ' dengan alasan: ' . $data['reason'],
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Aplikasi ditolak.');
    }
}
