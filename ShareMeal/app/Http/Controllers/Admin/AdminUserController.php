<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\User;
use App\Support\ShareMealState;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function adminUsers(Request $request): View
    {
        $search = (string) $request->query('search', '');
        $type = (string) $request->query('type', 'all');
        $status = (string) $request->query('status', 'all');

        $query = User::query();

        if ($search !== '') {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($type !== 'all') {
            $query->where('role', $type);
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $users = $query->orderBy('id')->get()->map(fn (User $user) => ShareMealState::transformUser($user))->all();

        $stats = [
            'totalUsers' => User::count(),
            'totalKonsumen' => User::where('role', 'consumer')->count(),
            'totalMitra' => User::where('role', 'mitra')->count(),
            'totalLembaga' => User::where('role', 'lembaga')->count(),
            'totalAktif' => User::where('status', 'active')->count(),
            'totalWarning' => User::where('status', 'warned')->orWhere('warnings_count', '>', 0)->count(),
            'totalBlocked' => User::where('status', 'blocked')->count(),
        ];

        return view('pages.admin.users', $this->dashboardData('admin', 'Manajemen Data User', 'Kelola akun & moderasi pelanggaran') + [
            'users' => $users,
            'stats' => $stats,
            'search' => $search,
            'type' => $type,
            'status' => $status,
        ]);
    }

    public function adminWarnUser(Request $request, int $userId): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required']]);
        $user = User::find($userId);
        $name = $user ? $user->displayName : 'User #' . $userId;
        ShareMealState::warnUser($userId, $data['reason']);

        AdminLog::create([
            'admin_id' => Auth::id() ?? User::where('role', 'admin')->value('id'),
            'action' => 'warn_user',
            'target_id' => $userId,
            'details' => 'Mengirim peringatan resmi kepada ' . $name . '. Alasan: ' . $data['reason'],
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Peringatan diberikan kepada user.');
    }

    public function adminBlockUser(Request $request, int $userId): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required']]);
        $user = User::find($userId);
        $name = $user ? $user->displayName : 'User #' . $userId;
        ShareMealState::blockUser($userId, $data['reason']);

        AdminLog::create([
            'admin_id' => Auth::id() ?? User::where('role', 'admin')->value('id'),
            'action' => 'block_user',
            'target_id' => $userId,
            'details' => 'Memblokir akun ' . $name . '. Alasan: ' . $data['reason'],
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'User diblokir.');
    }

    public function adminUnblockUser(int $userId): RedirectResponse
    {
        $user = User::find($userId);
        $name = $user ? $user->displayName : 'User #' . $userId;
        ShareMealState::unblockUser($userId);

        AdminLog::create([
            'admin_id' => Auth::id() ?? User::where('role', 'admin')->value('id'),
            'action' => 'unblock_user',
            'target_id' => $userId,
            'details' => 'Membuka blokir akun ' . $name,
            'ip_address' => request()->ip(),
        ]);

        return back()->with('success', 'Blokir user dibuka.');
    }
}
