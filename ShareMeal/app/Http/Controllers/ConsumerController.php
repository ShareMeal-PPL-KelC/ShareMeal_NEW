<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Services\AutoDonationService;
use Illuminate\Support\Str;

class ConsumerController extends Controller
{
    public function index()
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

        $flashSales = Product::with(['user' => function($q) {
                $q->withAvg('reviewsAsMitra', 'rating')->with('profile');
            }])
            ->where('status', 'flash-sale')
            ->where('stock', '>', 0)
            ->where('expires_at', '>', now())
            ->latest()
            ->take(3)
            ->get();

        $favoriteStores = User::where('role', 'mitra')
            ->with('profile')
            ->withAvg('reviewsAsMitra', 'rating')
            ->get();

        // PBI #45: Add critical alert for active orders
        $criticalAlerts = [];
        $shippingOrdersCount = $orders->where('status', 'shipping')->count();
        if ($shippingOrdersCount > 0) {
            $criticalAlerts[] = [
                'type' => 'info',
                'message' => "Hore! Ada $shippingOrdersCount pesanan yang sedang dalam perjalanan ke tempat Anda.",
                'link' => route('consumer.history'),
                'link_text' => 'Pantau Lokasi'
            ];
        }
        session()->flash('critical_alerts', $criticalAlerts);

        return view('consumer.dashboard', compact('stats', 'flashSales', 'favoriteStores'));
    }

    public function search(Request $request)
    {
        app(AutoDonationService::class)->processProducts();

        $filters = collect([
            ['id' => "halal", 'label' => "Halal", 'icon' => "🕌"],
            ['id' => "bakery", 'label' => "Bakery", 'icon' => "🍞"],
            ['id' => "healthy", 'label' => "Healthy", 'icon' => "🥗"],
            ['id' => "indonesian", 'label' => "Indonesian", 'icon' => "🍜"],
        ])->map(fn($i) => (object)$i);

        $storesQuery = User::where('role', 'mitra')
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

    public function history()
    {
        $userId = Auth::id() ?? User::where('role', 'consumer')->value('id') ?? 1;
        $transactions = Order::with(['items.product', 'mitra.profile', 'reviewRelation'])
            ->where('customer_id', $userId)
            ->latest()
            ->get();

        return view('consumer.history', compact('transactions'));
    }

    public function favorites()
    {
        app(AutoDonationService::class)->processProducts();

        $stores = User::where('role', 'mitra')->with(['profile', 'products' => function($q) {
            $q->where('status', 'flash-sale')
                ->where('stock', '>', 0)
                ->where('expires_at', '>', now());
        }])->get();

        return view('consumer.favorites', compact('stores'));
    }

    public function checkout(Request $request)
    {
        try {
            app(AutoDonationService::class)->processProducts();
        } catch (\Exception $e) {
            \Log::error('AutoDonationService error in checkout: ' . $e->getMessage());
        }

        $product = Product::with('user.profile')->findOrFail($request->product_id);

        if (!$product->user) {
            return redirect()->route('consumer.search')->withErrors(['product_id' => 'Data mitra tidak ditemukan untuk produk ini.']);
        }

        if (!in_array($product->status, ['normal', 'flash-sale'], true) || $product->stock <= 0 || ($product->expires_at && $product->expires_at->isPast())) {
            return redirect()->route('consumer.search')->withErrors(['product_id' => 'Produk sudah kedaluwarsa atau tidak tersedia.']);
        }

        $pickupStart = $product->pickup_start_time ?? '18:00';
        $pickupEnd = $product->pickup_end_time ?? '20:00';

        $slotLimit = $product->user->profile?->delivery_slot_limit ?? 10;
        
        // Count orders per slot for today
        $orderCounts = Order::where('mitra_id', $product->user_id)
            ->whereDate('created_at', now()->toDateString())
            ->whereNotNull('delivery_time_slot')
            ->groupBy('delivery_time_slot')
            ->select('delivery_time_slot', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->pluck('count', 'delivery_time_slot');

        // Generate 1-hour slots within window
        $slots = [];
        try {
            $startStr = !empty($pickupStart) ? $pickupStart : '18:00';
            $endStr = !empty($pickupEnd) ? $pickupEnd : '20:00';
            
            $start = \Carbon\Carbon::parse($startStr);
            $end = \Carbon\Carbon::parse($endStr);
            
            if ($end->lt($start)) {
                $end->addDay();
            }
            
            $maxSlots = 24;
            while ($start->lt($end) && $maxSlots > 0) {
                $slotStart = $start->format('H:i');
                $start->addHours(1);
                if ($start->gt($end)) break;
                $slotEnd = $start->format('H:i');
                $slotLabel = "$slotStart - $slotEnd";
                
                $currentCount = $orderCounts[$slotLabel] ?? 0;
                $isFull = $currentCount >= $slotLimit;
                
                $slots[] = (object) [
                    'label' => $slotLabel,
                    'is_full' => $isFull,
                    'remaining' => max(0, $slotLimit - $currentCount)
                ];
                $maxSlots--;
            }
        } catch (\Exception $e) {
            \Log::error('Error generating slots: ' . $e->getMessage());
        }

        $booking = (object) [
            'id' => "BK-" . strtoupper(Str::random(6)),
            'storeName' => $product->user->displayName,
            'dealItem' => $product->name,
            'quantity' => $request->quantity ?? 1,
            'price' => $product->discount_price > 0 ? $product->discount_price : $product->price,
            'status' => 'pending',
            'pickupTime' => $product->pickupTime,
            'pickupStart' => $pickupStart,
            'pickupEnd' => $pickupEnd,
            'distance' => "0.5 km",
            'address' => $product->user->profile?->business_address ?? $product->user->profile?->address ?? 'Alamat tidak tersedia',
            'product_id' => $product->id,
            'mitra_id' => $product->user_id,
            'canDelivery' => (bool) ($product->user->profile?->can_delivery ?? false),
            'deliveryFee' => (int) ($product->user->profile?->delivery_fee ?? 0),
            'deliverySlots' => $slots,
        ];

        $paymentMethods = collect([
            ['id' => "qris", 'name' => "QRIS", 'icon' => "qr-code", 'description' => "Scan QR untuk bayar"],
            ['id' => "gopay", 'name' => "GoPay", 'icon' => "wallet", 'description' => "E-wallet GoPay"],
            ['id' => "ovo", 'name' => "OVO", 'icon' => "wallet", 'description' => "E-wallet OVO"],
            ['id' => "dana", 'name' => "DANA", 'icon' => "smartphone", 'description' => "E-wallet DANA"],
        ])->map(fn($i) => (object)$i);

        return view('consumer.checkout', compact('booking', 'paymentMethods'));
    }

    public function storeOrder(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'mitra_id' => 'required|exists:users,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric',
            'receiving_method' => 'required|in:pickup,delivery',
            'delivery_time_slot' => 'required_if:receiving_method,delivery|string|nullable',
            'payment_method' => 'nullable|string|in:qris,gopay,ovo,dana',
        ]);

        $product = Product::findOrFail($request->product_id);
        $mitra = User::with('profile')->findOrFail($request->mitra_id);

        app(AutoDonationService::class)->processProducts($product->user_id);
        $product->refresh();
        
        if (!in_array($product->status, ['normal', 'flash-sale'], true) || $product->expires_at->isPast()) {
            return back()->withErrors(['product_id' => 'Produk sudah kedaluwarsa atau tidak tersedia.'])->withInput();
        }

        if ($product->stock < $request->quantity) {
            return back()->withErrors(['quantity' => 'Stok produk tidak mencukupi.'])->withInput();
        }

        $receivingMethod = $request->receiving_method;
        $deliveryFee = 0;
        $deliveryTimeSlot = $request->delivery_time_slot;

        if ($receivingMethod === 'delivery') {
            if (!$mitra->profile || !$mitra->profile->can_delivery) {
                return back()->withErrors(['receiving_method' => 'Mitra ini tidak menyediakan jasa pengiriman.'])->withInput();
            }
            $deliveryFee = $mitra->profile->delivery_fee;

            // PBI #38: Delivery Slot Limits
            if ($deliveryTimeSlot) {
                $slotLimit = $mitra->profile->delivery_slot_limit ?? 10;
                $currentSlotCount = Order::where('mitra_id', $request->mitra_id)
                    ->whereDate('created_at', now()->toDateString())
                    ->where('delivery_time_slot', $deliveryTimeSlot)
                    ->count();

                if ($currentSlotCount >= $slotLimit) {
                    return back()->withErrors(['delivery_time_slot' => 'Slot waktu pengantaran ini sudah penuh. Silakan pilih waktu lain.'])->withInput();
                }
            }
        }

        $order = Order::create([
            'customer_id' => Auth::id() ?? User::where('role', 'consumer')->value('id') ?? 1,
            'mitra_id' => $request->mitra_id,
            'total_amount' => ($request->price * $request->quantity) + $deliveryFee,
            'status' => 'pending',
            'pickup_code' => 'PICK-' . strtoupper(Str::random(4)),
            'pickup_start_time' => $product->pickup_start_time,
            'pickup_end_time' => $product->pickup_end_time,
            'receiving_method' => $receivingMethod,
            'delivery_fee' => $deliveryFee,
            'delivery_time_slot' => $deliveryTimeSlot,
            'payment_method' => $request->payment_method ?? 'qris',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'price' => $request->price,
        ]);

        $product->decrement('stock', $request->quantity);

        $mitra = User::find($request->mitra_id);
        if ($mitra) {
            $mitra->notify(new \App\Notifications\IncomingOrderNotification($order));
        }

        if (request()->wantsJson() || request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'order_number' => $order->orderId,
                'pickup_code' => $order->pickup_code,
                'redirect_url' => route('consumer.history'),
            ]);
        }

        return redirect()->route('consumer.history')->with('success', 'Reservasi berhasil! Kode pengambilan Anda: ' . $order->pickup_code);
    }

    public function submitReview(Request $request)
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

        // Prevent multiple reviews for the same order
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

    public function submitProblemReport(Request $request)
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

        $report = \App\Models\ProblemReport::create([
            'reporter_id' => $userId,
            'mitra_id' => $order->mitra_id,
            'order_id' => $order->id,
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

        return back()->with('success', 'Laporan masalah berhasil dikirim. Admin akan segera meninjau laporan Anda.');
    }

    /**
     * PBI #32: Update Existing Review
     * Dikerjakan oleh: Muh Irfan Ubaidillah
     */
    public function updateReview(Request $request, Review $review)
    {
        $userId = Auth::id() ?? User::where('role', 'consumer')->value('id') ?? 1;

        if ($review->customer_id !== $userId) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah ulasan ini.');
        }

        $data = $request->validate([
            'rating' => ['required', 'numeric', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $review->update($data);

        return back()->with('success', 'Ulasan Anda berhasil diperbarui.');
    }

    /**
     * PBI #32: Delete Review
     * Dikerjakan oleh: Muh Irfan Ubaidillah
     */
    public function deleteReview(Review $review)
    {
        $userId = Auth::id() ?? User::where('role', 'consumer')->value('id') ?? 1;

        if ($review->customer_id !== $userId) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus ulasan ini.');
        }

        $review->delete();

        return back()->with('success', 'Ulasan Anda telah dihapus.');
    }

    public function education()
    {
        // Ambil artikel published dari DB, jadikan plain array untuk Alpine.js
        $articles = collect(\App\Support\ShareMealState::get('articles'))
            ->filter(fn($a) => strtolower($a['status']) === 'published')
            ->values()
            ->map(fn($a) => [
                'id'       => $a['id'],
                'title'    => $a['title'],
                'category' => $a['category'],
                'readTime' => $a['read_time'] ?? '4 min read',
                'date'     => $a['date'],
                'author'   => $a['author'],
                'image'    => $a['image'] ?? '',
                'content'  => $a['content'],
            ])
            ->values();

        $categories = array_values(array_unique(
            array_merge(['Semua'], $articles->pluck('category')->toArray())
        ));

        $stats = (object) [
            'readCount' => 12,
            'level'     => 'Eco Warrior',
            'points'    => 450,
        ];

        return view('consumer.education', compact('articles', 'categories', 'stats'));
    }

    public function showArticle($id)
    {
        $allArticles = collect(\App\Support\ShareMealState::get('articles'))
            ->filter(fn($a) => strtolower($a['status']) === 'published');

        $raw = $allArticles->firstWhere('id', (int) $id);

        if (!$raw) {
            abort(404);
        }

        $article = (object) [
            'id'       => $raw['id'],
            'title'    => $raw['title'],
            'category' => $raw['category'],
            'readTime' => $raw['read_time'] ?? '4 min read',
            'date'     => $raw['date'],
            'author'   => $raw['author'],
            'image'    => $raw['image'],
            'content'  => $raw['content'],
        ];

        $relatedArticles = $allArticles
            ->filter(fn($a) => $a['id'] !== (int) $id)
            ->take(2)
            ->map(fn($a) => (object) $a);

        return view('consumer.article', compact('article', 'relatedArticles'));
    }

}
