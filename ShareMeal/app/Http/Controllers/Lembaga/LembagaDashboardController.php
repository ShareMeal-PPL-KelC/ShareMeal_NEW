<?php

namespace App\Http\Controllers\Lembaga;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\User;
use App\Support\ShareMealState;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class LembagaDashboardController extends Controller
{
    public function lembagaDashboard(): View
    {
        $userId = Session::get('sharemeal.current_user_id') ?? Auth::id();
        $userObj = User::query()->find($userId);

        $donationsQuery = Donation::query()
            ->where(function ($q) {
                $q->where('status', 'pending')
                  ->where(function ($q2) {
                      $q2->whereNull('expires_at')
                         ->orWhere('expires_at', '>', now());
                  });
            })
            ->orWhere('lembaga_id', $userId);

        $donations = ShareMealState::getDonationsQuery($donationsQuery);

        // PBI #45: Add critical alert for active claimed/prepared donations
        $criticalAlerts = [];
        $activeClaimedCount = collect($donations)->where('status', 'claimed')->count();
        $activePreparedCount = collect($donations)->where('status', 'prepared')->count();
        if ($activePreparedCount > 0) {
            $criticalAlerts[] = [
                'type' => 'warning',
                'title' => 'Donasi Siap Diambil',
                'message' => "Ada $activePreparedCount donasi yang sudah SIAP DIAMBIL. Segera lakukan penjemputan.",
                'link' => route('lembaga.donations', ['tab' => 'prepared']),
                'link_text' => 'Lihat Donasi'
            ];
        } elseif ($activeClaimedCount > 0) {
            $criticalAlerts[] = [
                'type' => 'info',
                'title' => 'Status Klaim Donasi',
                'message' => "Ada $activeClaimedCount donasi yang sudah Anda klaim dan menunggu penjemputan.",
                'link' => route('lembaga.donations', ['tab' => 'claimed']),
                'link_text' => 'Lihat Jadwal'
            ];
        }
        session()->flash('critical_alerts', $criticalAlerts);

        return view('pages.lembaga.dashboard', $this->dashboardData('lembaga', 'Dashboard Lembaga Sosial', 'Kelola penerimaan donasi makanan') + [
            'stats' => (object) [
                'totalDonations' => Donation::where('lembaga_id', $userId)->where('status', 'completed')->count(),
                'activeDonations' => Donation::where('lembaga_id', $userId)->whereIn('status', ['claimed', 'prepared'])->count(),
                'beneficiaries' => $userObj?->profile?->beneficiaries_count ?? 120,
                'thisMonth' => Donation::where('lembaga_id', $userId)->where('status', 'completed')->where('delivered_at', '>=', now()->startOfMonth())->count()
            ],
            'donations' => $donations,
            'availableDonations' => collect($donations)->where('status', 'available')->all(),
            'recentDonations' => collect($donations)->whereIn('status', ['claimed', 'prepared', 'completed'])->sortByDesc('claimed_at')->take(5)->all(),
            'userObj' => $userObj,
        ]);
    }
}
