<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FeedbackController extends Controller
{
    /**
     * Tampilkan form feedback untuk user (Consumer, Mitra, Lembaga)
     */
    public function create()
    {
        return view('pages.feedback.create');
    }

    /**
     * Simpan feedback baru ke database
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'category' => ['required', 'string', 'in:fitur,bug,ui_ux,other'],
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'screenshots' => ['nullable', 'array'],
            'screenshots.*' => ['image', 'max:2048'], // MAX 2 MB each
        ], [
            'category.required' => 'Kategori wajib dipilih.',
            'category.in' => 'Kategori tidak valid.',
            'subject.required' => 'Subjek wajib diisi.',
            'subject.max' => 'Subjek maksimal 255 karakter.',
            'description.required' => 'Deskripsi wajib diisi.',
            'description.max' => 'Deskripsi maksimal 5000 karakter.',
            'rating.required' => 'Rating wajib diisi.',
            'rating.integer' => 'Rating harus berupa angka.',
            'rating.min' => 'Rating minimal 1 bintang.',
            'rating.max' => 'Rating maksimal 5 bintang.',
            'screenshots.array' => 'Format file lampiran tidak valid.',
            'screenshots.*.image' => 'File lampiran harus berupa gambar.',
            'screenshots.*.max' => 'Ukuran setiap gambar maksimal 2 MB.',
        ]);

        $screenshotPaths = [];
        if ($request->hasFile('screenshots')) {
            foreach ($request->file('screenshots') as $file) {
                $screenshotPaths[] = $file->store('feedbacks', 'public');
            }
        }

        $feedback = Feedback::create([
            'user_id' => Auth::id(),
            'category' => $data['category'],
            'subject' => $data['subject'],
            'description' => $data['description'],
            'rating' => $data['rating'],
            'screenshots' => $screenshotPaths,
        ]);

        // Notify Admins
        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\NewFeedbackNotification($feedback));
        }

        return back()->with('success', 'Feedback Anda berhasil terkirim ke admin. Terima kasih atas masukan yang diberikan!');
    }

    /**
     * Halaman daftar feedback untuk admin
     */
    public function adminIndex(Request $request)
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
    public function adminDelete(Feedback $feedback)
    {
        // Hapus screenshot files dari storage jika ada
        if ($feedback->screenshots) {
            foreach ($feedback->screenshots as $path) {
                Storage::disk('public')->delete($path);
            }
        }

        \App\Models\AdminLog::create([
            'admin_id' => Auth::id() ?? \App\Models\User::where('role', 'admin')->value('id'),
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
    public function adminToggleStatus(Feedback $feedback)
    {
        $newStatus = $feedback->status === 'resolved' ? 'pending' : 'resolved';
        $feedback->update(['status' => $newStatus]);

        \App\Models\AdminLog::create([
            'admin_id' => Auth::id() ?? \App\Models\User::where('role', 'admin')->value('id'),
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
