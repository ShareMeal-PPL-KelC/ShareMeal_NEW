<?php

namespace App\Http\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Product;
use App\Models\User;
use App\Services\AutoDonationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class MitraInventoryController extends Controller
{
    public function mitraInventory(): View
    {
        $userId = Auth::id() ?? User::where('role', 'mitra')->value('id');
        app(AutoDonationService::class)->processProducts($userId);

        $user = Auth::user()?->load('profile');
        $profile = $user?->profile;
        $openingHours = $profile?->opening_hours ?? '08:00 - 20:00';
        $parts = explode(' - ', $openingHours);
        $shopOpen = count($parts) === 2 ? trim($parts[0]) : '08:00';
        $shopClose = count($parts) === 2 ? trim($parts[1]) : '20:00';

        try {
            $startCarbon = Carbon::createFromFormat('H:i', $shopOpen);
            $defaultPickupStart = $startCarbon->addHour()->format('H:i');
        } catch (\Exception $e) {
            $defaultPickupStart = '09:00';
        }
        $defaultPickupEnd = $shopClose;

        $products = Product::where('user_id', $userId)
            ->get()
            ->map(function (Product $product) {
                $expiresAt = $product->expires_at?->copy()->timezone(config('app.timezone'));

                $product->expires_at_input = $expiresAt?->format('Y-m-d\TH:i');
                $product->expires_at_display = $expiresAt?->format('d/m/Y H:i');
                $product->pickup_start_time_input = $product->pickup_start_time ? substr((string) $product->pickup_start_time, 0, 5) : '';
                $product->pickup_end_time_input = $product->pickup_end_time ? substr((string) $product->pickup_end_time, 0, 5) : '';

                return $product;
            });

        return view('pages.mitra.inventory', compact('products', 'defaultPickupStart', 'defaultPickupEnd'));
    }

    public function mitraInventoryStore(Request $request): RedirectResponse
    {
        if (!Auth::user()?->is_verified) {
            return back()->with('error', 'Akun Anda belum terverifikasi. Anda tidak dapat menambahkan produk ke inventaris.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'price' => ['required', 'integer', 'min:0'],
            'discount_price' => ['nullable', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'expires_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'pickup_start_time' => ['required', 'date_format:H:i'],
            'pickup_end_time' => ['required', 'date_format:H:i', 'after:pickup_start_time'],
            'status' => ['required', 'string', 'in:normal,flash-sale,donation,expired'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ], [
            'pickup_start_time.required' => 'Jam mulai pengambilan wajib diisi.',
            'pickup_end_time.required' => 'Jam akhir pengambilan wajib diisi.',
            'pickup_end_time.after' => 'Jam akhir pengambilan harus lebih akhir dari jam mulai.',
            'image.max' => 'Ukuran gambar tidak boleh melebihi 2MB.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif.',
        ]);

        $user = Auth::user()?->load('profile');
        $profile = $user->profile;

        $openingHours = $profile?->business_opening_hours ?? $profile?->opening_hours;
        if ($openingHours && str_contains($openingHours, ' - ')) {
            [$opStart, $opEnd] = explode(' - ', $openingHours, 2);

            if ($data['pickup_start_time'] < $opStart || $data['pickup_start_time'] > $opEnd) {
                return back()->withErrors(['pickup_start_time' => "Jam mulai pengambilan harus di dalam jam operasional ($openingHours)."])->withInput();
            }
            if ($data['pickup_end_time'] > $opEnd) {
                return back()->withErrors(['pickup_end_time' => "Jam akhir pengambilan harus di dalam jam operasional ($openingHours)."])->withInput();
            }
        }

        $expiresAt = $this->parseLocalDateTime($data['expires_at']);

        $discountPrice = $data['discount_price'] ?? 0;
        if ($data['status'] === 'flash-sale' && $discountPrice <= 0) {
            $discountPrice = floor($data['price'] * 0.7);
        }

        $product = Product::create([
            'user_id' => Auth::id() ?? User::where('role', 'mitra')->first()?->id,
            'name' => $data['name'],
            'category' => $data['category'],
            'price' => $data['price'],
            'discount_price' => $discountPrice,
            'stock' => $data['stock'],
            'expires_at' => $expiresAt,
            'pickup_start_time' => $data['pickup_start_time'],
            'pickup_end_time' => $data['pickup_end_time'],
            'status' => $data['status'],
            'image' => $request->hasFile('image') ? $request->file('image')->store('products', 'public') : 'https://images.unsplash.com/photo-1666114170628-b34b0dcc21aa?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxiYWtlcnklMjBicmVhZCUyMHBhc3RyeSUyMHNob3B8ZW58MXx8fHwxNzc0OTc0Mzg5fDA&ixlib=rb-4.1.0&q=80&w=1080',
        ]);

        $mitra = User::find($product->user_id);
        if ($mitra) {
            if ($product->status === 'flash-sale') {
                $consumers = User::where('role', 'consumer')->get();
                if ($consumers->count() > 0) {
                    Notification::send($consumers, new \App\Notifications\FlashSaleNotification($mitra->name, $product->name, $product->discount_price));
                }
            } elseif ($product->status === 'normal') {
                $consumers = User::where('role', 'consumer')->get();
                if ($consumers->count() > 0) {
                    Notification::send($consumers, new \App\Notifications\NewMenuAvailableNotification($mitra->name, $product->name, $product->price));
                }
            } elseif ($product->status === 'donation') {
                $donation = Donation::create([
                    'mitra_id' => $product->user_id,
                    'title' => $product->name,
                    'quantity' => $product->stock,
                    'unit' => 'pcs',
                    'expires_at' => $product->expires_at,
                    'pickup_start_time' => $product->pickup_start_time,
                    'pickup_end_time' => $product->pickup_end_time,
                    'description' => 'Didonasikan langsung melalui inventaris produk.',
                    'status' => 'pending',
                    'image' => $product->getRawOriginal('image'),
                ]);

                $lembagas = User::where('role', 'lembaga')->get();
                if ($lembagas->count() > 0) {
                    Notification::send($lembagas, new \App\Notifications\DonationAvailableNotification($mitra->name, $donation->title, $donation->quantity . ' ' . $donation->unit));
                }
            }
        }

        return back()->with('success', 'Produk berhasil ditambahkan.');
    }

    public function mitraInventoryUpdate(Request $request, int $productId): RedirectResponse
    {
        $userId = Auth::id() ?? User::where('role', 'mitra')->value('id');
        $product = Product::where('user_id', $userId)->findOrFail($productId);

        if ($product->status === 'expired' || $product->expires_at->isPast() || $product->stock <= 0) {
            return back()->with('error', 'Produk sudah habis atau kedaluwarsa dan tidak dapat diubah.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'price' => ['required', 'integer', 'min:0'],
            'discount_price' => ['nullable', 'integer', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'expires_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'pickup_start_time' => ['required', 'date_format:H:i'],
            'pickup_end_time' => ['required', 'date_format:H:i', 'after:pickup_start_time'],
            'status' => ['required', 'string', 'in:normal,flash-sale,donation,expired'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ], [
            'pickup_start_time.required' => 'Jam mulai pengambilan wajib diisi.',
            'pickup_end_time.required' => 'Jam akhir pengambilan wajib diisi.',
            'pickup_end_time.after' => 'Jam akhir pengambilan harus lebih akhir dari jam mulai.',
            'image.max' => 'Ukuran gambar tidak boleh melebihi 2MB.',
            'image.image' => 'File harus berupa gambar.',
            'image.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif.',
        ]);

        $user = Auth::user()?->load('profile');
        $profile = $user->profile;

        $openingHours = $profile?->business_opening_hours ?? $profile?->opening_hours;
        if ($openingHours && str_contains($openingHours, ' - ')) {
            [$opStart, $opEnd] = explode(' - ', $openingHours, 2);

            if ($data['pickup_start_time'] < $opStart || $data['pickup_start_time'] > $opEnd) {
                return back()->withErrors(['pickup_start_time' => "Jam mulai pengambilan harus di dalam jam operasional ($openingHours)."])->withInput();
            }
            if ($data['pickup_end_time'] > $opEnd) {
                return back()->withErrors(['pickup_end_time' => "Jam akhir pengambilan harus di dalam jam operasional ($openingHours)."])->withInput();
            }
        }

        $wasNotFlashSale = $product->getOriginal('status') !== 'flash-sale';
        $wasNotNormal = $product->getOriginal('status') !== 'normal';
        $wasNotDonation = $product->getOriginal('status') !== 'donation';

        $expiresAt = $this->parseLocalDateTime($data['expires_at']);

        $discountPrice = $data['discount_price'] ?? 0;
        if ($data['status'] === 'flash-sale' && $discountPrice <= 0) {
            $discountPrice = floor($data['price'] * 0.7);
        }

        $product->update([
            'name' => $data['name'],
            'category' => $data['category'],
            'price' => $data['price'],
            'discount_price' => $discountPrice,
            'stock' => $data['stock'],
            'expires_at' => $expiresAt,
            'pickup_start_time' => $data['pickup_start_time'],
            'pickup_end_time' => $data['pickup_end_time'],
            'status' => $data['status'],
        ]);

        if ($request->hasFile('image')) {
            $product->update(['image' => $request->file('image')->store('products', 'public')]);
        }

        $mitra = User::find($product->user_id);
        if ($mitra) {
            if ($product->status === 'flash-sale' && $wasNotFlashSale) {
                $consumers = User::where('role', 'consumer')->get();
                if ($consumers->count() > 0) {
                    Notification::send($consumers, new \App\Notifications\FlashSaleNotification($mitra->name, $product->name, $product->discount_price));
                }
            } elseif ($product->status === 'normal' && $wasNotNormal) {
                $consumers = User::where('role', 'consumer')->get();
                if ($consumers->count() > 0) {
                    Notification::send($consumers, new \App\Notifications\NewMenuAvailableNotification($mitra->name, $product->name, $product->price));
                }
            } elseif ($product->status === 'donation' && $wasNotDonation) {
                $donation = Donation::create([
                    'mitra_id' => $product->user_id,
                    'title' => $product->name,
                    'quantity' => $product->stock,
                    'unit' => 'pcs',
                    'expires_at' => $product->expires_at,
                    'pickup_start_time' => $product->pickup_start_time,
                    'pickup_end_time' => $product->pickup_end_time,
                    'description' => 'Didonasikan langsung melalui perubahan status inventaris.',
                    'status' => 'pending',
                    'image' => $product->getRawOriginal('image'),
                ]);

                $lembagas = User::where('role', 'lembaga')->get();
                if ($lembagas->count() > 0) {
                    Notification::send($lembagas, new \App\Notifications\DonationAvailableNotification($mitra->name, $donation->title, $donation->quantity . ' ' . $donation->unit));
                }
            }
        }

        return back()->with('success', 'Informasi produk berhasil diperbarui.');
    }

    public function mitraInventoryFlashSale(int $productId): RedirectResponse
    {
        $userId = Auth::id() ?? User::where('role', 'mitra')->value('id');
        app(AutoDonationService::class)->processProducts($userId);

        $product = Product::where('user_id', $userId)->findOrFail($productId);

        if ($product->status === 'expired' || $product->expires_at->isPast() || $product->stock <= 0) {
            return back()->with('error', 'Produk sudah habis atau kedaluwarsa.');
        }

        $product->update([
            'status' => 'flash-sale',
            'discount_price' => floor($product->price * 0.7),
        ]);

        $mitra = User::find($product->user_id);
        if ($mitra) {
            $consumers = User::where('role', 'consumer')->get();
            if ($consumers->count() > 0) {
                Notification::send($consumers, new \App\Notifications\FlashSaleNotification($mitra->name, $product->name, $product->discount_price));
            }
        }

        return back()->with('success', 'Flash sale diaktifkan.');
    }

    public function mitraInventoryToggleDonation(int $productId): RedirectResponse
    {
        $userId = Auth::id() ?? User::where('role', 'mitra')->value('id');
        $product = Product::where('user_id', $userId)->findOrFail($productId);

        if ($product->status === 'expired' || $product->expires_at->isPast() || $product->stock <= 0) {
            return back()->with('error', 'Produk sudah habis atau kedaluwarsa.');
        }

        $product->update([
            'donatable' => !$product->donatable,
        ]);

        $status = $product->donatable ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', 'Donasi otomatis untuk "' . $product->name . '" berhasil ' . $status . '.');
    }

    public function mitraInventoryDelete(int $productId): RedirectResponse
    {
        $userId = Auth::id() ?? User::where('role', 'mitra')->value('id');
        $product = Product::where('user_id', $userId)->findOrFail($productId);
        $product->delete();

        return back()->with('success', 'Produk dihapus.');
    }
}
