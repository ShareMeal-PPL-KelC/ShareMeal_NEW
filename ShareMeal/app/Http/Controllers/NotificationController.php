<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function markNotificationsRead(): RedirectResponse
    {
        if (Auth::check()) {
            Auth::user()->unreadNotifications->markAsRead();
        }
        return back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
    }

    public function markSingleNotificationRead(string $id): RedirectResponse
    {
        if (Auth::check()) {
            Auth::user()->notifications()->findOrFail($id)->markAsRead();
        }
        return back();
    }

    public function allNotifications(): View|RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login untuk mengakses notifikasi.');
        }

        $notifications = $user->notifications()->paginate(15);
        $role = $user->role ?? 'consumer';

        return view('pages.notifications', $this->dashboardData($role, 'Semua Notifikasi', 'Pantau semua aktivitas dan pemberitahuan Anda') + [
            'notificationsList' => $notifications,
        ]);
    }
}
