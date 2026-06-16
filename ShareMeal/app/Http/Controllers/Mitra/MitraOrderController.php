<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MitraOrderController extends Controller
{
    public function mitraOrders(): View
    {
        Order::checkAndApplyDelays();
        $userId = Auth::id() ?? User::where('role', 'mitra')->value('id');
        $orders = Order::with(['customer.profile', 'items.product', 'reviewRelation'])
            ->where('mitra_id', $userId)
            ->latest()
            ->get();

        return view('pages.mitra.orders', compact('orders'));
    }

    public function mitraHistory(): View
    {
        $userId = Auth::id() ?? User::where('role', 'mitra')->value('id');

        $orders = Order::with(['customer.profile', 'items.product', 'reviewRelation'])
            ->where('mitra_id', $userId)
            ->whereIn('status', ['completed', 'cancelled'])
            ->latest('updated_at')
            ->get();

        $stats = (object) [
            'total_orders' => $orders->count(),
            'completed_orders' => $orders->where('status', 'completed')->count(),
            'cancelled_orders' => $orders->where('status', 'cancelled')->count(),
            'total_revenue' => $orders->where('status', 'completed')->sum('total_amount'),
        ];

        return view('pages.mitra.history', $this->dashboardData('mitra', 'Riwayat Transaksi', 'Manajemen histori transaksi penjualan') + [
            'orders' => $orders,
            'stats' => $stats,
        ]);
    }

    public function mitraReviews(): View
    {
        $userId = Auth::id() ?? User::where('role', 'mitra')->value('id');

        $allReviews = Review::where('mitra_id', $userId)->get();

        $stats = [
            'average' => round($allReviews->avg('rating') ?? 0, 1),
            'total' => $allReviews->count(),
            'counts' => [
                5 => $allReviews->where('rating', 5)->count(),
                4 => $allReviews->where('rating', 4)->count(),
                3 => $allReviews->where('rating', 3)->count(),
                2 => $allReviews->where('rating', 2)->count(),
                1 => $allReviews->where('rating', 1)->count(),
            ]
        ];

        $reviews = Review::with(['customer', 'order.items.product'])
            ->where('mitra_id', $userId)
            ->latest()
            ->paginate(10);

        return view('pages.mitra.reviews', compact('reviews', 'stats'));
    }

    public function updateOrderStatus(Request $request, int $orderId): JsonResponse|RedirectResponse
    {
        $userId = Auth::id() ?? User::where('role', 'mitra')->value('id');
        $order = Order::where('mitra_id', $userId)->findOrFail($orderId);

        $request->validate([
            'status' => ['required', 'in:pending,processing,ready,shipping,completed,cancelled'],
            'cancel_reason' => ['nullable', 'required_if:status,cancelled', 'string', 'max:500'],
        ]);

        $updateData = ['status' => $request->status];
        if ($request->status === 'cancelled') {
            $updateData['cancel_reason'] = $request->cancel_reason ?: 'Dibatalkan oleh mitra toko.';
        } elseif (in_array($request->status, ['processing', 'ready']) && $order->status === 'pending' && $order->receiving_method === 'pickup') {
            $updateData['pickup_start_time'] = now()->format('H:i:s');
            $updateData['pickup_end_time'] = now()->addHour()->format('H:i:s');
        }

        $order->update($updateData);

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'status' => $order->status,
                'completed_time' => $order->completedTime,
            ]);
        }

        return back()->with('success', 'Status pesanan berhasil diperbarui.');
    }

    public function delayOrder(Request $request, int $orderId): JsonResponse|RedirectResponse
    {
        $userId = Auth::id() ?? User::where('role', 'mitra')->value('id');
        $order = Order::where('mitra_id', $userId)->findOrFail($orderId);

        if ($order->status !== 'processing') {
            if ($request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan hanya dapat ditandai delay jika statusnya sedang diproses.',
                ], 422);
            }
            return back()->with('error', 'Pesanan hanya dapat ditandai delay jika statusnya sedang diproses.');
        }

        $order->update([
            'is_delayed' => true,
            'delayed_at' => now(),
        ]);

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'is_delayed' => true,
                'message' => 'Pesanan berhasil ditandai delay.',
            ]);
        }

        return back()->with('success', 'Pesanan berhasil ditandai delay.');
    }

    public function mitraOrdersConfirm(int $orderId): JsonResponse|RedirectResponse
    {
        $userId = Auth::id() ?? User::where('role', 'mitra')->value('id');
        $order = Order::where('mitra_id', $userId)->findOrFail($orderId);
        $order->update(['status' => 'completed']);

        if (request()->wantsJson() || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'completed_time' => $order->completedTime,
            ]);
        }
        return back()->with('success', 'Pesanan dikonfirmasi sebagai sudah diambil.');
    }
}
