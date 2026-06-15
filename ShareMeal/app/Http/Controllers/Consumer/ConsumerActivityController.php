<?php

namespace App\Http\Controllers\Consumer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use App\Models\ProblemReport;
use App\Notifications\NewProblemReportNotification;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ConsumerActivityController extends Controller
{
    public function submitReview(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'rating' => ['required', 'numeric', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $userId = Auth::id() ?? User::where('role', 'consumer')->value('id') ?? 1;
        $order = Order::where('id', $data['order_id'])
            ->where('customer_id', $userId)
            ->firstOrFail();

        if ($order->reviewRelation) {
            return back()->with('error', 'Anda sudah memberikan ulasan untuk pesanan ini.');
        }

        Review::create([
            'order_id' => $order->id,
            'customer_id' => $userId,
            'mitra_id' => $order->mitra_id,
            'rating' => $data['rating'],
            'comment' => $data['comment'],
        ]);

        return back()->with('success', 'Terima kasih atas ulasan Anda!');
    }

    public function submitProblemReport(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'issue_type' => ['required', 'string', 'in:expired,bad_quality,mismatch,other'],
            'description' => ['required', 'string', 'max:2000'],
            'evidence_image' => ['nullable', 'image', 'max:2048'],
        ]);

        $userId = Auth::id() ?? User::where('role', 'consumer')->value('id') ?? 1;
        $order = Order::where('id', $data['order_id'])
            ->where('customer_id', $userId)
            ->firstOrFail();

        $evidencePath = null;
        if ($request->hasFile('evidence_image')) {
            $evidencePath = $request->file('evidence_image')->store('reports', 'public');
        }

        $report = ProblemReport::create([
            'reporter_id' => $userId,
            'mitra_id' => $order->mitra_id,
            'order_id' => $order->id,
            'issue_type' => $data['issue_type'],
            'description' => $data['description'],
            'evidence_image' => $evidencePath,
            'status' => 'pending',
        ]);

        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new NewProblemReportNotification($report));
        }

        return back()->with('success', 'Laporan masalah berhasil dikirim. Admin akan segera meninjau laporan Anda.');
    }

    public function updateReview(Request $request, Review $review): RedirectResponse
    {
        $userId = Auth::id() ?? User::where('role', 'consumer')->value('id') ?? 1;

        if ($review->customer_id !== $userId) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah ulasan ini.');
        }

        if ($review->created_at->addMinutes(2)->isPast()) {
            abort(403, 'Ulasan tidak dapat diubah setelah 2 menit.');
        }

        $data = $request->validate([
            'rating' => ['required', 'numeric', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $review->update($data);

        return back()->with('success', 'Ulasan Anda berhasil diperbarui.');
    }

    public function deleteReview(Review $review): RedirectResponse
    {
        $userId = Auth::id() ?? User::where('role', 'consumer')->value('id') ?? 1;

        if ($review->customer_id !== $userId) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus ulasan ini.');
        }

        if ($review->created_at->addMinutes(2)->isPast()) {
            abort(403, 'Ulasan tidak dapat dihapus setelah 2 menit.');
        }

        $review->delete();

        return back()->with('success', 'Ulasan Anda telah dihapus.');
    }
}
