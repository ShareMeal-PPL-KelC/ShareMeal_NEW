<?php

namespace Tests\Browser;

use App\Models\UserProfile;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Support\Facades\Hash;

class Pbi39SimulasiPembayaranBerhasilTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function setupTestData(): array
    {
        $mitraEmail = 'mitra39_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $consumerEmail = 'consumer39_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $password = 'password123';

        // Setup Mitra
        $mitra = User::factory()->create([
            'role' => 'mitra',
            'name' => 'Toko Roti Enak',
            'email' => $mitraEmail,
            'password' => Hash::make($password),
            'is_verified' => true,
        ]);
        
        UserProfile::create([
            'user_id' => $mitra->id,
            'business_name' => 'Toko Roti Enak',
            'business_address' => 'Jl. Padi No. 10',
            'can_delivery' => true,
            'delivery_fee' => 5000,
            'delivery_slot_limit' => 5,
        ]);

        $now = now();
        if ($now->hour >= 22) {
            $pickupStart = '23:58:00';
            $pickupEnd = '23:59:59';
        } else {
            $pickupStart = $now->copy()->addHour()->format('H:i:s');
            $pickupEnd = $now->copy()->addHours(3)->format('H:i:s');
        }

        // Setup Product
        $product = Product::factory()->create([
            'user_id' => $mitra->id,
            'name' => 'Roti Coklat',
            'category' => 'Bakery',
            'price' => 15000,
            'stock' => 10,
            'status' => 'normal',
            'pickup_start_time' => $pickupStart,
            'pickup_end_time' => $pickupEnd,
            'expires_at' => now()->addDay(),
        ]);

        // Setup Consumer
        $consumer = User::factory()->create([
            'role' => 'consumer',
            'email' => $consumerEmail,
            'password' => Hash::make($password),
        ]);
        
        UserProfile::create([
            'user_id' => $consumer->id,
            'phone' => '081234567890',
            'address' => 'Jl. Kebon Jeruk No. 25',
        ]);

        return [$consumerEmail, $password, $product];
    }

    private function disableReveal(Browser $browser): void
    {
        $browser->script("
            var style = document.createElement('style');
            style.innerHTML = '.reveal { opacity: 1 !important; transform: none !important; transition: none !important; transition-delay: 0s !important; }';
            document.head.appendChild(style);
        ");
    }

    private function login(Browser $browser, string $email, string $password): void
    {
        $browser->driver->manage()->deleteAllCookies();
        $browser->maximize()
            ->visit('/login')
            ->waitFor('select[name="user_type"]')
            ->select('user_type', 'consumer')
            ->type('email', $email)
            ->type('password', $password)
            ->click('button[type="submit"]')
            ->waitForLocation('/consumer', 15);
    }

    /**
     * TC-PBI39-001 (Positif)
     * Konsumen masuk, mencari produk, menambahkan ke keranjang, checkout, memilih Ambil Sendiri
     * dan pembayaran QRIS, melihat proses loading simulasi pembayaran QRIS, dan melihat struk sukses.
     */
    public function test_konsumen_dapat_menyelesaikan_pembayaran_dengan_simulasi(): void
    {
        [$consumerEmail, $password, $product] = $this->setupTestData();

        $this->browse(function (Browser $browser) use ($consumerEmail, $password, $product) {
            $this->login($browser, $consumerEmail, $password);

            // 1. Cari Makanan / Search Page
            $browser->visit('/consumer/search');
            $this->disableReveal($browser);
            $browser->waitForText('Roti Coklat', 15);

            // 2. Klik Pesan Sekarang / Add to Cart
            $browser->script("
                var targetBtn = null;
                for (var el of document.querySelectorAll('*')) {
                    if (el.textContent && el.textContent.trim() === 'Roti Coklat') {
                        let parent = el;
                        for (let i = 0; i < 10; i++) {
                            if (!parent) break;
                            let btn = parent.querySelector('button');
                            if (btn) {
                                targetBtn = btn;
                                break;
                            }
                            parent = parent.parentElement;
                        }
                        if (targetBtn) break;
                    }
                }
                if (targetBtn) {
                    targetBtn.click();
                } else {
                    throw new Error('Pesan Sekarang button for Roti Coklat not found');
                }
            ");

            // 3. Masuk ke halaman Cart
            $browser->waitForLocation('/consumer/cart', 15)
                    ->waitForText('Detail Makanan yang Direservasi', 15)
                    ->waitFor('a[href*="checkout"]', 15);
            
            // Scroll ke bawah di Cart
            $browser->script("window.scrollTo(0, document.body.scrollHeight);");
            $browser->pause(1000);

            // Klik Lanjutkan ke Checkout
            $browser->script("
                let checkoutBtn = document.querySelector('a[href*=\"checkout\"]');
                if (checkoutBtn) {
                    checkoutBtn.click();
                } else {
                    throw new Error('Checkout button not found');
                }
            ");

            // 4. Masuk ke Halaman Checkout
            $browser->waitForText('Menyelesaikan Pemesanan', 15);
            $this->disableReveal($browser);
            $browser->pause(1000);

            // Pilih metode pengambilan Ambil Sendiri (pickup) menggunakan JS click
            $browser->script("
                let radio = document.querySelector('input[name=\"receiving_method_radio\"][value=\"pickup\"]');
                if (radio) {
                    radio.click();
                    radio.dispatchEvent(new Event('change', { bubbles: true }));
                } else {
                    throw new Error('Ambil Sendiri radio not found');
                }
            ");
            $browser->pause(1000);

            // Pilih metode pembayaran QRIS menggunakan JS click
            $browser->script("
                let radio = document.querySelector('input[name=\"payment_method_radio\"][value=\"qris\"]');
                if (radio) {
                    radio.click();
                    radio.dispatchEvent(new Event('change', { bubbles: true }));
                } else {
                    throw new Error('QRIS radio not found');
                }
            ");
            $browser->pause(1000);
            
            // Scroll ke bawah di Checkout
            $browser->script("window.scrollTo(0, document.body.scrollHeight);");
            $browser->pause(1000);

            // Tekan tombol konfirmasi & bayar
            $browser->script("
                let confirmBtn = document.querySelector('button[x-on\\\\:click*=\"handleConfirmPayment\"], button[\\\\@click*=\"handleConfirmPayment\"]');
                if (confirmBtn) {
                    confirmBtn.click();
                } else {
                    throw new Error('Confirm button not found');
                }
            ");

            // Cek apakah loading screen QRIS muncul dan menampilkan pesan transisi
            $browser->waitForText('Menghasilkan kode QRIS unik...', 15)
                    
                    // Tunggu hingga simulasi selesai (sekitar 4 detik) dan struk digital muncul
                    ->waitForText('Pemesanan Berhasil', 15)
                    ->assertSee('LUNAS')
                    ->assertSee('AMBIL SENDIRI')
                    ->assertSee('QRIS');
        });
    }

    /**
     * TC-PBI39-002 (Negatif)
     * Transaksi gagal diselesaikan jika data keranjang belanja kosong saat konfirmasi pembayaran dilakukan.
     */
    public function test_transaksi_gagal_jika_keranjang_kosong(): void
    {
        [$consumerEmail, $password, $product] = $this->setupTestData();

        $this->browse(function (Browser $browser) use ($consumerEmail, $password, $product) {
            $this->login($browser, $consumerEmail, $password);

            // 1. Cari Makanan / Search Page
            $browser->visit('/consumer/search');
            $this->disableReveal($browser);
            $browser->waitForText('Roti Coklat', 15);

            // 2. Klik Pesan Sekarang / Add to Cart
            $browser->script("
                var targetBtn = null;
                for (var el of document.querySelectorAll('*')) {
                    if (el.textContent && el.textContent.trim() === 'Roti Coklat') {
                        let parent = el;
                        for (let i = 0; i < 10; i++) {
                            if (!parent) break;
                            let btn = parent.querySelector('button');
                            if (btn) {
                                targetBtn = btn;
                                break;
                            }
                            parent = parent.parentElement;
                        }
                        if (targetBtn) break;
                    }
                }
                if (targetBtn) {
                    targetBtn.click();
                } else {
                    throw new Error('Pesan Sekarang button for Roti Coklat not found');
                }
            ");

            // 3. Masuk ke halaman Cart
            $browser->waitForLocation('/consumer/cart', 15)
                    ->waitForText('Detail Makanan yang Direservasi', 15)
                    ->waitFor('a[href*="checkout"]', 15);
            
            // Scroll ke bawah di Cart
            $browser->script("window.scrollTo(0, document.body.scrollHeight);");
            $browser->pause(1000);

            // Klik Lanjutkan ke Checkout
            $browser->script("
                let checkoutBtn = document.querySelector('a[href*=\"checkout\"]');
                if (checkoutBtn) {
                    checkoutBtn.click();
                } else {
                    throw new Error('Checkout button not found');
                }
            ");

            // 4. Masuk ke Halaman Checkout
            $browser->waitForText('Menyelesaikan Pemesanan', 15);
            $this->disableReveal($browser);
            $browser->pause(1000);

            // Mock window.alert untuk memverifikasi pesan kesalahan
            $browser->script("
                window.alert_msg = null;
                window.alert = function(msg) { window.alert_msg = msg; };
            ");

            // Pilih metode pengambilan Ambil Sendiri (pickup) menggunakan JS click
            $browser->script("
                let radio = document.querySelector('input[name=\"receiving_method_radio\"][value=\"pickup\"]');
                if (radio) {
                    radio.click();
                    radio.dispatchEvent(new Event('change', { bubbles: true }));
                } else {
                    throw new Error('Ambil Sendiri radio not found');
                }
            ");
            $browser->pause(1000);

            // Pilih metode pembayaran QRIS menggunakan JS click
            $browser->script("
                let radio = document.querySelector('input[name=\"payment_method_radio\"][value=\"qris\"]');
                if (radio) {
                    radio.click();
                    radio.dispatchEvent(new Event('change', { bubbles: true }));
                } else {
                    throw new Error('QRIS radio not found');
                }
            ");
            $browser->pause(1000);

            // Kosongkan keranjang belanja di database secara langsung agar transaksi gagal saat disubmit!
            \App\Models\CartItem::truncate();

            // Hapus nama input 'product_id' agar controller tidak mere-create cart item dari POST request
            $browser->script("
                let prodInput = document.querySelector('input[name=\"product_id\"]');
                if (prodInput) {
                    prodInput.removeAttribute('name');
                }
            ");

            // Scroll ke bawah
            $browser->script("window.scrollTo(0, document.body.scrollHeight);");
            $browser->pause(1000);

            // Tekan tombol konfirmasi & bayar
            $browser->script("
                let confirmBtn = document.querySelector('button[x-on\\\\:click*=\"handleConfirmPayment\"], button[\\\\@click*=\"handleConfirmPayment\"]');
                if (confirmBtn) {
                    confirmBtn.click();
                } else {
                    throw new Error('Confirm button not found');
                }
            ");
            
            // Tunggu 5 detik untuk simulasi QRIS selesai, kemudian AJAX request dibuat, ditangkap, dan alert ditampilkan
            $browser->pause(6000);

            // Verifikasi alert pesan kesalahan dari server muncul
            $alertMsg = $browser->script("return window.alert_msg;")[0];
            $this->assertNotNull($alertMsg, "Alert message was not captured!");
            $this->assertStringContainsString('Keranjang kosong atau batas waktu reservasi telah habis', $alertMsg);
        });
    }
}
