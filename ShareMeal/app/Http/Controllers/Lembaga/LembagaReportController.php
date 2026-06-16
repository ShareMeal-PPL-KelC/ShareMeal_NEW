<?php

namespace App\Http\Controllers\Lembaga;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\ProblemReport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LembagaReportController extends Controller
{
    public function lembagaSubmitProblemReport(Request $request)
    {
        $data = $request->validate([
            'donation_id' => ['required', 'exists:donations,id'],
            'issue_type' => ['required', 'string', 'in:expired,bad_quality,mismatch,other'],
            'description' => ['required', 'string', 'max:2000'],
            'evidence_image' => ['nullable', 'image', 'max:2048'],
        ]);

        $userId = Auth::id() ?? Session::get('sharemeal.current_user_id');
        $donation = Donation::where('id', $data['donation_id'])
            ->where('lembaga_id', $userId)
            ->firstOrFail();

        $evidencePath = null;
        if ($request->hasFile('evidence_image')) {
            $evidencePath = $request->file('evidence_image')->store('reports', 'public');
        }

        $report = ProblemReport::create([
            'reporter_id' => $userId,
            'mitra_id' => $donation->mitra_id,
            'donation_id' => $donation->id,
            'issue_type' => $data['issue_type'],
            'description' => $data['description'],
            'evidence_image' => $evidencePath,
            'status' => 'pending',
        ]);

        // Notify Admins
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\NewProblemReportNotification($report));
        }

        return back()->with('success', 'Laporan masalah donasi berhasil dikirim.');
    }
}
