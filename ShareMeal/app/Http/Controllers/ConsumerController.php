<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Str;

class ConsumerController extends Controller
{
    public function index()
    {
        $userId = Auth::id() ?? User::where('role', 'consumer')->value('id') ?? 1;
        
        $stats = (object) [
            'savedMeals' => OrderItem::whereHas('order', function($q) use ($userId) {
                $q->where('customer_id', $userId)->where('status', 'completed');
            })->sum('quantity'),
            'moneySaved' => 0, // Placeholder
            'co2Reduced' => 6.5,
            'favoriteStores' => 8,
        ];

        $flashSales = Product::with('user.profile')
            ->where('status', 'flash-sale')
            ->where('stock', '>', 0)
            ->latest()
            ->take(3)
            ->get();

        $favoriteStores = User::where('role', 'mitra')
            ->with('profile')
            ->take(3)
            ->get();

        return view('consumer.dashboard', compact('stats', 'flashSales', 'favoriteStores'));
    }

    public function search(Request $request)
    {
        $filters = collect([
            ['id' => "halal", 'label' => "Halal", 'icon' => "🕌"],
            ['id' => "bakery", 'label' => "Bakery", 'icon' => "🍞"],
            ['id' => "healthy", 'label' => "Healthy", 'icon' => "🥗"],
            ['id' => "indonesian", 'label' => "Indonesian", 'icon' => "🍜"],
        ])->map(fn($i) => (object)$i);

        $storesQuery = User::where('role', 'mitra')->with(['profile', 'products' => function($q) {
            $q->where('status', 'flash-sale')->where('stock', '>', 0);
        }]);

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

    public function checkout(Request $request)
    {
        $product = Product::with('user.profile')->findOrFail($request->product_id);
        
        $booking = (object) [
            'id' => "BK-" . strtoupper(Str::random(6)),
            'storeName' => $product->user->name,
            'dealItem' => $product->name,
            'quantity' => $request->quantity ?? 1,
            'price' => $product->discount_price > 0 ? $product->discount_price : $product->price,
            'status' => 'pending',
            'pickupTime' => "18:00 - 20:00", // Default logic or get from store profile
            'distance' => "0.5 km",
            'address' => $product->user->profile->address ?? 'Alamat tidak tersedia',
            'product_id' => $product->id,
            'mitra_id' => $product->user_id,
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
        ]);

        $order = Order::create([
            'customer_id' => Auth::id() ?? User::where('role', 'consumer')->value('id') ?? 1,
            'mitra_id' => $request->mitra_id,
            'total_amount' => $request->price * $request->quantity,
            'status' => 'pending',
            'pickup_code' => 'PICK-' . strtoupper(Str::random(4)),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'price' => $request->price,
        ]);

        return redirect()->route('consumer.history')->with('success', 'Reservasi berhasil! Kode pengambilan Anda: ' . $order->pickup_code);
    }

    public function education()
    {
        $articles = collect([
            [
                'id' => 1,
                'title' => "Cara Cerdas Menyimpan Makanan agar Tahan Lama",
                'category' => "Tips",
                'readTime' => "5 min read",
                'date' => "2026-03-25",
                'author' => "System",
                'image' => "https://images.unsplash.com/photo-1588610547076-26154e177b94?w=500&h=300&fit=crop",
                'content' => "Ketahui cara yang benar menyimpan sayur, daging, dan produk dairy agar tidak cepat busuk dan terbuang sia-sia.",
            ],
            [
                'id' => 2,
                'title' => "Dampak Sampah Makanan Terhadap Perubahan Iklim",
                'category' => "Artikel Edukasi",
                'readTime' => "8 min read",
                'date' => "2026-03-20",
                'author' => "System",
                'image' => "https://images.unsplash.com/photo-1611284446314-60a58ac0deb9?w=500&h=300&fit=crop",
                'content' => "Tahukah Anda bahwa sampah makanan berkontribusi sebesar 8% terhadap total emisi gas rumah kaca global? Mari kita bahas lebih lanjut.",
            ],
            [
                'id' => 3,
                'title' => "Mengolah Sisa Bahan Makanan Menjadi Kompos",
                'category' => "Panduan Praktis",
                'readTime' => "6 min read",
                'date' => "2026-03-15",
                'author' => "System",
                'image' => "https://images.unsplash.com/photo-1595273151817-299fbd0e7048?w=500&h=300&fit=crop",
                'content' => "Panduan langkah demi langkah membuat kompos sendiri di rumah menggunakan sisa-sisa sayuran dan buah.",
            ],
        ])->map(fn($i) => (object)$i);

        $categories = ["Semua", "Tips", "Artikel Edukasi", "Panduan Praktis"];
        
        $stats = (object) [
            'readCount' => 12,
            'level' => "Eco Warrior",
            'points' => 450,
        ];

        return view('consumer.education', compact('articles', 'categories', 'stats'));
    }
}
