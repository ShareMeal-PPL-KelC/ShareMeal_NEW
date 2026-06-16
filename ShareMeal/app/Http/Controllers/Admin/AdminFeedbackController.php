<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\AdminLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminFeedbackController extends Controller
{
    /**
     * Halaman daftar feedback untuk admin
     */
    public function adminIndex(Request $request): View
    {
        $query = Feedback::with('user');

        // Filter Kategori
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Filter Role User
        if ($request->filled('role')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('role', $request->input('role'));
            });
        }

        // Filter Rating
        if ($request->filled('rating')) {
            $query->where('rating', $request->input('rating'));
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Pencarian (Subjek / Deskripsi / Nama User)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($qu) use ($search) {
                      $qu->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $feedbacks = $query->latest()->paginate(10)->withQueryString();

        return view('pages.admin.feedbacks', compact('feedbacks'));
    }

    /**
     * Hapus feedback oleh admin
     */
    public function adminDelete(Feedback $feedback): RedirectResponse
    {
        // Hapus screenshot files dari storage jika ada
        if ($feedback->screenshots) {
            foreach ($feedback->screenshots as $path) {
                Storage::disk('public')->delete($path);
            }
        }

        AdminLog::create([
            'admin_id' => Auth::id() ?? User::where('role', 'admin')->value('id'),
            'action' => 'feedback_delete',
            'target_id' => $feedback->id,
            'details' => 'Menghapus feedback "' . $feedback->subject . '" dari ' . ($feedback->user->name ?? 'Unknown') . '.',
            'ip_address' => request()->ip(),
        ]);

        $feedback->delete();

        return back()->with('success', 'Feedback berhasil dihapus dari sistem.');
    }

    /**
     * Toggle status feedback oleh admin (pending / resolved)
     */
    public function adminToggleStatus(Feedback $feedback): RedirectResponse
    {
        $newStatus = $feedback->status === 'resolved' ? 'pending' : 'resolved';
        $feedback->update(['status' => $newStatus]);

        AdminLog::create([
            'admin_id' => Auth::id() ?? User::where('role', 'admin')->value('id'),
            'action' => 'feedback_update',
            'target_id' => $feedback->id,
            'details' => 'Mengubah status feedback "' . $feedback->subject . '" menjadi ' . ($newStatus === 'resolved' ? 'Selesai' : 'Belum Selesai') . '.',
            'ip_address' => request()->ip(),
        ]);

        $message = $newStatus === 'resolved' 
            ? 'Feedback berhasil ditandai sebagai SELESAI / DI-ACC.' 
            : 'Feedback berhasil dikembalikan ke status BELUM SELESAI.';

        return back()->with('success', $message);
    }
}
