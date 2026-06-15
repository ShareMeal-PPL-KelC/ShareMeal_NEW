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
 * PBI-37: Pilih Jam Pengantaran
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
class Pbi37PilihJamPengantaranTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function setupTestData(): array
    {
        $mitraEmail = 'mitra37_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $consumerEmail = 'consumer37_' . time() . '_' . rand(1000, 9999) . '@example.com';
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
            'name' => 'Roti Manis',
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
     * TC-PBI37-001 (Positif)
     * Konsumen berhasil memilih waktu pengantaran saat checkout dan menyelesaikan pemesanan.
     */
    public function test_consumer_can_select_delivery_time_slot_and_complete_order(): void
    {
        [$consumerEmail, $password, $product] = $this->setupTestData();

        $this->browse(function (Browser $browser) use ($consumerEmail, $password, $product) {
            $this->login($browser, $consumerEmail, $password);

            // Mengunjungi halaman 'halaman terkait'
            $browser->visit('/consumer/checkout?product_id=' . $product->id);
            $this->disableReveal($browser);

            // Menunggu teks '' muncul di layar (batas waktu standar detik)
            $browser->waitForText('Menyelesaikan Pemesanan')
                    // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
                    ->pause(1000);

            // Select receiving method "Kirim ke Lokasi" (delivery)
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("
                let radio = document.querySelector('input[name=\"receiving_method_radio\"][value=\"delivery\"]');
                if (radio) {
                    radio.click();
                    radio.dispatchEvent(new Event('change', { bubbles: true }));
                } else {
                    throw new Error('Delivery radio not found');
                }
            ");
            // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(1000);

            // Select delivery slot time
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("
                let slotBtn = document.querySelector('button[\\\\@click*=\"deliveryTimeSlot\"]:not([disabled])');
                if (slotBtn) {
                    slotBtn.click();
                } else {
                    throw new Error('Delivery slot button not found');
                }
            ");
            // Menjeda eksekusi selama 500 milidetik agar proses render/transisi halaman selesai
            $browser->pause(500);

            // Scroll down and Click Confirm & Pay
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("
                window.scrollTo(0, document.body.scrollHeight);
                let confirmBtn = document.querySelector('button[\\\\@click*=\"handleConfirmPayment\"]');
                if (confirmBtn) {
                    confirmBtn.click();
                } else {
                    throw new Error('Confirm button not found');
                }
            ");

            // Tunggu hingga pemesanan selesai
            // Menunggu teks 'Pemesanan Berhasil' muncul di layar (batas waktu 15 detik)
            $browser->waitForText('Pemesanan Berhasil', 15)
                    // Memastikan teks 'LUNAS' terlihat pada halaman browser
                    ->assertSee('LUNAS')
                    // Memastikan teks 'KIRIM KE LOKASI' terlihat pada halaman browser
                    ->assertSee('KIRIM KE LOKASI');
        });
    }

    /**
     * TC-PBI37-002 (Negatif)
     * Konsumen gagal menyelesaikan pemesanan jika memilih pengantaran tetapi tidak memilih slot waktu.
     */
    public function test_consumer_cannot_complete_order_without_selecting_delivery_time_slot(): void
    {
        [$consumerEmail, $password, $product] = $this->setupTestData();

        $this->browse(function (Browser $browser) use ($consumerEmail, $password, $product) {
            $this->login($browser, $consumerEmail, $password);

            // Mengunjungi halaman 'halaman terkait'
            $browser->visit('/consumer/checkout?product_id=' . $product->id);
            $this->disableReveal($browser);

            // Menunggu teks '' muncul di layar (batas waktu standar detik)
            $browser->waitForText('Menyelesaikan Pemesanan')
                    // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
                    ->pause(1000);

            // Mock window.alert untuk memverifikasi pesan kesalahan dan mencegah pemblokiran dialog
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("
                window.alert_msg = null;
                window.alert = function(msg) { window.alert_msg = msg; };
            ");

            // Select receiving method "Kirim ke Lokasi" (delivery)
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("
                let radio = document.querySelector('input[name=\"receiving_method_radio\"][value=\"delivery\"]');
                if (radio) {
                    radio.click();
                    radio.dispatchEvent(new Event('change', { bubbles: true }));
                } else {
                    throw new Error('Delivery radio not found');
                }
            ");
            // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(1000);

            // Scroll down and Click Confirm & Pay (without choosing slot)
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("
                window.scrollTo(0, document.body.scrollHeight);
                let confirmBtn = document.querySelector('button[\\\\@click*=\"handleConfirmPayment\"]');
                if (confirmBtn) {
                    confirmBtn.click();
                } else {
                    throw new Error('Confirm button not found');
                }
            ");
            // Menjeda eksekusi selama 500 milidetik agar proses render/transisi halaman selesai
            $browser->pause(500);

            // Ambil pesan alert dari window
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $alertMsg = $browser->script("return window.alert_msg;")[0];
            $this->assertEquals('Silakan pilih waktu pengantaran terlebih dahulu.', $alertMsg);

            // Pastikan proses pembayaran tidak dilanjutkan
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $isProcessing = $browser->script("return window.Alpine.\$data(document.querySelector('[x-data=\"checkoutPage\"]')).isProcessing;")[0];
            $this->assertFalse($isProcessing, 'Pembayaran tidak boleh diproses jika slot waktu belum dipilih');
        });
    }
}
