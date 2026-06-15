<?php

namespace App\Http\Controllers\Consumer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\AutoDonationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class ConsumerDashboardController extends Controller
{
    public function index(): View
    {
        app(AutoDonationService::class)->processProducts();

        $userId = Auth::id() ?? User::where('role', 'consumer')->value('id') ?? 1;
        $orders = Order::with('items')
            ->where('customer_id', $userId)
            ->get();

        $stats = (object) [
            'savedMeals' => OrderItem::whereHas('order', function($q) use ($userId) {
                $q->where('customer_id', $userId)->where('status', 'completed');
            })->sum('quantity'),
            'moneySaved' => $orders->sum('savedAmount'),
            'co2Reduced' => 6.5,
            'favoriteStores' => 8,
        ];

        $flashSales = Product::whereHas('user', function($q) {
                $q->where('is_verified', true);
            })
            ->with(['user' => function($q) {
                $q->withAvg('reviewsAsMitra', 'rating')->with('profile');
            }])
            ->where('status', 'flash-sale')
            ->where('stock', '>', 0)
            ->where('expires_at', '>', now())
            ->latest()
            ->take(3)
            ->get();

        $favoriteStores = User::where('role', 'mitra')
            ->where('is_verified', true)
            ->with('profile')
            ->withAvg('reviewsAsMitra', 'rating')
            ->get();

        $criticalAlerts = [];
        $shippingOrdersCount = $orders->where('status', 'shipping')->count();
        if ($shippingOrdersCount > 0) {
            $criticalAlerts[] = [
                'type' => 'info',
                'title' => 'Status Pengiriman',
                'message' => "Hore! Ada $shippingOrdersCount pesanan yang sedang dalam perjalanan ke tempat Anda.",
                'link' => route('consumer.orders.active'),
                'link_text' => 'Pantau Lokasi'
            ];
        }
        session()->flash('critical_alerts', $criticalAlerts);

        return view('consumer.dashboard', compact('stats', 'flashSales', 'favoriteStores'));
    }

    public function search(Request $request): View
    {
        app(AutoDonationService::class)->processProducts();

        $filters = collect([
            ['id' => "halal", 'label' => "Halal", 'icon' => "🕌"],
            ['id' => "bakery", 'label' => "Bakery", 'icon' => "🍞"],
            ['id' => "healthy", 'label' => "Healthy", 'icon' => "🥗"],
            ['id' => "indonesian", 'label' => "Indonesian", 'icon' => "🍜"],
        ])->map(fn($i) => (object)$i);

        $storesQuery = User::where('role', 'mitra')
            ->where('is_verified', true)
            ->with(['profile', 'products' => function($q) {
                $q->whereIn('status', ['flash-sale', 'normal'])
                    ->where('stock', '>', 0)
                    ->where('expires_at', '>', now());
            }])
            ->withAvg('reviewsAsMitra', 'rating');

        if ($request->has('q')) {
            $storesQuery->where('name', 'like', '%' . $request->q . '%');
        }

        $stores = $storesQuery->get();

        return view('consumer.search', compact('filters', 'stores'));
    }

    public function history(): View
    {
        $userId = Auth::id() ?? User::where('role', 'consumer')->value('id') ?? 1;
        $transactions = Order::with(['items.product', 'mitra.profile', 'reviewRelation'])
            ->where('customer_id', $userId)
            ->where(function($query) {
                $query->where('status', 'cancelled')
                      ->orWhere(function($q) {
                          $q->where('status', 'completed')
                            ->where('confirmed_by_consumer', true);
                      });
            })
            ->latest('updated_at')
            ->get();

        return view('consumer.history', compact('transactions'));
    }

    public function activeOrders(): View
    {
        Order::checkAndApplyDelays();
        $userId = Auth::id() ?? User::where('role', 'consumer')->value('id') ?? 1;
        $activeOrders = Order::with(['items.product', 'mitra.profile'])
            ->where('customer_id', $userId)
            ->where(function($query) {
                $query->whereIn('status', ['pending', 'processing', 'ready', 'shipping'])
                      ->orWhere(function($q) {
                          $q->where('status', 'completed')
                            ->where('confirmed_by_consumer', false);
                      });
            })
            ->latest()
            ->get();

        return view('consumer.active_orders', compact('activeOrders'));
    }

    public function confirmComplete(int $orderId): JsonResponse|RedirectResponse
    {
        $userId = Auth::id() ?? User::where('role', 'consumer')->value('id') ?? 1;
        $order = Order::where('customer_id', $userId)->findOrFail($orderId);

        $order->update(['confirmed_by_consumer' => true]);

        if (request()->wantsJson() || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pesanan telah dikonfirmasi selesai.'
            ]);
        }

        return back()->with('success', 'Pesanan telah dikonfirmasi selesai.');
    }

    public function favorites(): View
    {
        app(AutoDonationService::class)->processProducts();

        $stores = User::where('role', 'mitra')->with(['profile', 'products' => function($q) {
            $q->where('status', 'flash-sale')
                ->where('stock', '>', 0)
                ->where('expires_at', '>', now());
        }])->get();

        return view('consumer.favorites', compact('stores'));
    }
}
