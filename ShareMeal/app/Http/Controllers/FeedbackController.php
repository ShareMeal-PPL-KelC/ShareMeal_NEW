<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    /**
     * Tampilkan form feedback untuk user (Consumer, Mitra, Lembaga)
     */
    public function create(): View
    {
        return view('pages.feedback.create');
    }

    /**
     * Simpan feedback baru ke database
     */
    public function store(Request $request): RedirectResponse
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
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\NewFeedbackNotification($feedback));
        }

        return back()->with('success', 'Feedback Anda berhasil terkirim ke admin. Terima kasih atas masukan yang diberikan!');
    }
}
