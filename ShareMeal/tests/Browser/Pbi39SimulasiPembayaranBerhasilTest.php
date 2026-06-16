<?php

namespace Tests\Browser;

use App\Models\UserProfile;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Support\Facades\Hash;

/**
 * PBI-39: Simulasi Pembayaran Berhasil
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
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
        // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
        $browser->script("
            var style = document.createElement('style');
            style.innerHTML = '.reveal { opacity: 1 !important; transform: none !important; transition: none !important; transition-delay: 0s !important; }';
            document.head.appendChild(style);
        ");
    }

    private function login(Browser $browser, string $email, string $password): void
    {
        $browser->driver->manage()->deleteAllCookies();
        // Memaksimalkan ukuran jendela browser agar tampilan terlihat penuh
        $browser->maximize()
            // Mengunjungi halaman '/login'
            ->visit('/login')
            // Menunggu elemen 'elemen terkait' muncul di layar (batas waktu standar detik)
            ->waitFor('select[name="user_type"]')
            // Memilih opsi 'consumer' pada dropdown 'user_type'
            ->select('user_type', 'consumer')
            // Mengisi input field 'email'
            ->type('email', $email)
            // Mengisi input field 'password'
            ->type('password', $password)
            // Mengeklik elemen 'elemen terkait' di halaman
            ->click('button[type="submit"]')
            // Menunggu halaman berpindah ke rute '/consumer' (batas waktu 15 detik)
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
            // Mengunjungi halaman '/consumer/search'
            $browser->visit('/consumer/search');
            $this->disableReveal($browser);
            // Menunggu teks 'Roti Coklat' muncul di layar (batas waktu 15 detik)
            $browser->waitForText('Roti Coklat', 15);

            // 2. Klik Pesan Sekarang / Add to Cart
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
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
            // Menunggu halaman berpindah ke rute '/consumer/cart' (batas waktu 15 detik)
            $browser->waitForLocation('/consumer/cart', 15)
                    // Menunggu teks 'Detail Makanan yang Direservasi' muncul di layar (batas waktu 15 detik)
                    ->waitForText('Detail Makanan yang Direservasi', 15)
                    // Menunggu elemen 'elemen terkait' muncul di layar (batas waktu standar detik)
                    ->waitFor('a[href*="checkout"]', 15);
            
            // Scroll ke bawah di Cart
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("window.scrollTo(0, document.body.scrollHeight);");
            // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(1000);

            // Klik Lanjutkan ke Checkout
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("
                let checkoutBtn = document.querySelector('a[href*=\"checkout\"]');
                if (checkoutBtn) {
                    checkoutBtn.click();
                } else {
                    throw new Error('Checkout button not found');
                }
            ");

            // 4. Masuk ke Halaman Checkout
            // Menunggu teks 'Menyelesaikan Pemesanan' muncul di layar (batas waktu 15 detik)
            $browser->waitForText('Menyelesaikan Pemesanan', 15);
            $this->disableReveal($browser);
            // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(1000);

            // Pilih metode pengambilan Ambil Sendiri (pickup) menggunakan JS click
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("
                let radio = document.querySelector('input[name=\"receiving_method_radio\"][value=\"pickup\"]');
                if (radio) {
                    radio.click();
                    radio.dispatchEvent(new Event('change', { bubbles: true }));
                } else {
                    throw new Error('Ambil Sendiri radio not found');
                }
            ");
            // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(1000);

            // Pilih metode pembayaran QRIS menggunakan JS click
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("
                let radio = document.querySelector('input[name=\"payment_method_radio\"][value=\"qris\"]');
                if (radio) {
                    radio.click();
                    radio.dispatchEvent(new Event('change', { bubbles: true }));
                } else {
                    throw new Error('QRIS radio not found');
                }
            ");
            // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(1000);
            
            // Scroll ke bawah di Checkout
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("window.scrollTo(0, document.body.scrollHeight);");
            // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(1000);

            // Tekan tombol konfirmasi & bayar
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("
                let confirmBtn = document.querySelector('button[x-on\\\\:click*=\"handleConfirmPayment\"], button[\\\\@click*=\"handleConfirmPayment\"]');
                if (confirmBtn) {
                    confirmBtn.click();
                } else {
                    throw new Error('Confirm button not found');
                }
            ");

            // Cek apakah loading screen QRIS muncul dan menampilkan pesan transisi
            // Menunggu teks 'Menghasilkan kode QRIS unik...' muncul di layar (batas waktu 15 detik)
            $browser->waitForText('Menghasilkan kode QRIS unik...', 15)
                    
                    // Tunggu hingga simulasi selesai (sekitar 4 detik) dan struk digital muncul
                    // Menunggu teks 'Pemesanan Berhasil' muncul di layar (batas waktu 15 detik)
                    ->waitForText('Pemesanan Berhasil', 15)
                    // Memastikan teks 'LUNAS' terlihat pada halaman browser
                    ->assertSee('LUNAS')
                    // Memastikan teks 'AMBIL SENDIRI' terlihat pada halaman browser
                    ->assertSee('AMBIL SENDIRI')
                    // Memastikan teks 'QRIS' terlihat pada halaman browser
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
            // Mengunjungi halaman '/consumer/search'
            $browser->visit('/consumer/search');
            $this->disableReveal($browser);
            // Menunggu teks 'Roti Coklat' muncul di layar (batas waktu 15 detik)
            $browser->waitForText('Roti Coklat', 15);

            // 2. Klik Pesan Sekarang / Add to Cart
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
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
            // Menunggu halaman berpindah ke rute '/consumer/cart' (batas waktu 15 detik)
            $browser->waitForLocation('/consumer/cart', 15)
                    // Menunggu teks 'Detail Makanan yang Direservasi' muncul di layar (batas waktu 15 detik)
                    ->waitForText('Detail Makanan yang Direservasi', 15)
                    // Menunggu elemen 'elemen terkait' muncul di layar (batas waktu standar detik)
                    ->waitFor('a[href*="checkout"]', 15);
            
            // Scroll ke bawah di Cart
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("window.scrollTo(0, document.body.scrollHeight);");
            // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(1000);

            // Klik Lanjutkan ke Checkout
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("
                let checkoutBtn = document.querySelector('a[href*=\"checkout\"]');
                if (checkoutBtn) {
                    checkoutBtn.click();
                } else {
                    throw new Error('Checkout button not found');
                }
            ");

            // 4. Masuk ke Halaman Checkout
            // Menunggu teks 'Menyelesaikan Pemesanan' muncul di layar (batas waktu 15 detik)
            $browser->waitForText('Menyelesaikan Pemesanan', 15);
            $this->disableReveal($browser);
            // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(1000);

            // Mock window.alert untuk memverifikasi pesan kesalahan
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("
                window.alert_msg = null;
                window.alert = function(msg) { window.alert_msg = msg; };
            ");

            // Pilih metode pengambilan Ambil Sendiri (pickup) menggunakan JS click
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("
                let radio = document.querySelector('input[name=\"receiving_method_radio\"][value=\"pickup\"]');
                if (radio) {
                    radio.click();
                    radio.dispatchEvent(new Event('change', { bubbles: true }));
                } else {
                    throw new Error('Ambil Sendiri radio not found');
                }
            ");
            // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(1000);

            // Pilih metode pembayaran QRIS menggunakan JS click
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("
                let radio = document.querySelector('input[name=\"payment_method_radio\"][value=\"qris\"]');
                if (radio) {
                    radio.click();
                    radio.dispatchEvent(new Event('change', { bubbles: true }));
                } else {
                    throw new Error('QRIS radio not found');
                }
            ");
            // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(1000);

            // Kosongkan keranjang belanja di database secara langsung agar transaksi gagal saat disubmit!
            \App\Models\CartItem::truncate();

            // Hapus nama input 'product_id' agar controller tidak mere-create cart item dari POST request
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("
                let prodInput = document.querySelector('input[name=\"product_id\"]');
                if (prodInput) {
                    prodInput.removeAttribute('name');
                }
            ");

            // Scroll ke bawah
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("window.scrollTo(0, document.body.scrollHeight);");
            // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(1000);

            // Tekan tombol konfirmasi & bayar
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("
                let confirmBtn = document.querySelector('button[x-on\\\\:click*=\"handleConfirmPayment\"], button[\\\\@click*=\"handleConfirmPayment\"]');
                if (confirmBtn) {
                    confirmBtn.click();
                } else {
                    throw new Error('Confirm button not found');
                }
            ");
            
            // Tunggu 5 detik untuk simulasi QRIS selesai, kemudian AJAX request dibuat, ditangkap, dan alert ditampilkan
            // Menjeda eksekusi selama 6000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(6000);

            // Verifikasi alert pesan kesalahan dari server muncul
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $alertMsg = $browser->script("return window.alert_msg;")[0];
            $this->assertNotNull($alertMsg, "Alert message was not captured!");
            $this->assertStringContainsString('Keranjang kosong atau batas waktu reservasi telah habis', $alertMsg);
        });
    }
}
