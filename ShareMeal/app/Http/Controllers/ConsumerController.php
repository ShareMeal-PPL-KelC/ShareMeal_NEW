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

    public function favorites()
    {
        $stores = User::where('role', 'mitra')->with(['profile', 'products' => function($q) {
            $q->where('status', 'flash-sale')->where('stock', '>', 0);
        }])->get();

        return view('consumer.favorites', compact('stores'));
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

        $mitra = User::find($request->mitra_id);
        if ($mitra) {
            $mitra->notify(new \App\Notifications\IncomingOrderNotification($order));
        }

        return redirect()->route('consumer.history')->with('success', 'Reservasi berhasil! Kode pengambilan Anda: ' . $order->pickup_code);
    }

    public function education()
    {
        $articles = $this->getDummyArticles();

        $categories = ["Semua", "Tips", "Artikel Edukasi", "Panduan Praktis"];

        $stats = (object) [
            'readCount' => 12,
            'level' => "Eco Warrior",
            'points' => 450,
        ];

        return view('consumer.education', compact('articles', 'categories', 'stats'));
    }

    public function showArticle($id)
    {
        $article = $this->getDummyArticles()->firstWhere('id', (int)$id);

        if (!$article) {
            abort(404);
        }

        $relatedArticles = $this->getDummyArticles()->where('id', '!=', (int)$id)->take(2);

        return view('consumer.article', compact('article', 'relatedArticles'));
    }

    protected function getDummyArticles()
    {
        return collect([
            [
                'id' => 1,
                'title' => "Cara Cerdas Menyimpan Makanan agar Tahan Lama",
                'category' => "Tips",
                'readTime' => "5 min read",
                'date' => "2026-03-25",
                'author' => "System",
                'image' => "https://images.unsplash.com/photo-1737363625103-de62618722e8?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                'content' => "Ketahui cara yang benar menyimpan sayur, daging, dan produk dairy agar tidak cepat busuk dan terbuang sia-sia. Menyimpan makanan dengan cara yang benar tidak hanya membantu Anda menghemat uang, tetapi juga secara signifikan mengurangi jumlah sampah makanan yang dihasilkan oleh rumah tangga Anda. Misalnya, tahukah Anda bahwa kentang sebaiknya disimpan di tempat yang gelap dan sejuk, tetapi tidak di dalam lemari es? Lemari es dapat mengubah pati kentang menjadi gula, yang dapat mempengaruhi rasa dan teksturnya saat dimasak.",
            ],
            [
                'id' => 2,
                'title' => "Dampak Sampah Makanan Terhadap Perubahan Iklim",
                'category' => "Artikel Edukasi",
                'readTime' => "8 min read",
                'date' => "2026-03-20",
                'author' => "System",
                'image' => "https://images.unsplash.com/photo-1611284446314-60a58ac0deb9?w=500&h=300&fit=crop",
                'content' => "Tahukah Anda bahwa sampah makanan berkontribusi sebesar 8% terhadap total emisi gas rumah kaca global? Mari kita bahas lebih lanjut tentang bagaimana membuang makanan berarti kita juga membuang semua sumber daya yang digunakan untuk memproduksinya—termasuk air, tanah, energi, tenaga kerja, dan modal. Ketika sampah makanan menumpuk di tempat pembuangan akhir, ia membusuk dan melepaskan metana, gas rumah kaca yang jauh lebih kuat daripada karbon dioksida.",
            ],
            [
                'id' => 3,
                'title' => "Mengolah Sisa Bahan Makanan Menjadi Kompos",
                'category' => "Panduan Praktis",
                'readTime' => "6 min read",
                'date' => "2026-03-15",
                'author' => "System",
                'image' => "https://images.unsplash.com/photo-1492496913980-501348b61469?q=80&w=987&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D",
                'content' => "Panduan langkah demi langkah membuat kompos sendiri di rumah menggunakan sisa-sisa sayuran dan buah. Membuat kompos adalah cara yang luar biasa untuk mendaur ulang nutrisi kembali ke tanah dan mengurangi ketergantungan kita pada pupuk kimia. Anda tidak memerlukan halaman yang luas untuk memulai; pengomposan dalam ruangan atau sistem bokashi bisa menjadi solusi bagi mereka yang tinggal di apartemen atau rumah dengan lahan terbatas.",
            ],
        ])->map(fn($i) => (object)$i);
    }
}
