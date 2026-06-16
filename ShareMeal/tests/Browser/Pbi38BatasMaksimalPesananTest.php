<?php

namespace Tests\Browser;

use App\Models\UserProfile;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Support\Facades\Hash;

/**
 * PBI-38: Batas Maksimal Pesanan
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
class Pbi38BatasMaksimalPesananTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function setupTestData(int $slotLimit = 5, bool $fillSlot = false): array
    {
        $mitraEmail = 'mitra38_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $consumerEmail = 'consumer38_' . time() . '_' . rand(1000, 9999) . '@example.com';
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
            'delivery_slot_limit' => $slotLimit,
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

        if ($fillSlot) {
            // Calculate first generated slot label based on the start time
            $startToday = \Carbon\Carbon::parse($pickupStart);
            $slotStart = $startToday->format('H:i');
            $startToday->addHours(1);
            $slotEnd = $startToday->format('H:i');
            $slotLabel = "$slotStart - $slotEnd";

            // Create orders to fully occupy the slot
            for ($i = 0; $i < $slotLimit; $i++) {
                Order::create([
                    'customer_id' => $consumer->id,
                    'mitra_id' => $mitra->id,
                    'total_amount' => 15000,
                    'status' => 'pending',
                    'pickup_code' => 'TEST-38-' . $i,
                    'receiving_method' => 'delivery',
                    'delivery_time_slot' => $slotLabel,
                    'created_at' => now(),
                ]);
            }
        }

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
     * TC-PBI38-001 (Positif)
     * Konsumen dapat melihat dan memilih slot waktu pengantaran yang masih tersedia.
     */
    public function test_konsumen_dapat_memilih_slot_waktu_yang_tersedia(): void
    {
        [$consumerEmail, $password, $product] = $this->setupTestData(5, false);

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

            // Check that the slot is available and clickable
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

            // Verify that the slot has 'TERSEDIA' label and is selected (not disabled)
            // Memastikan teks 'TERSEDIA' terlihat pada halaman browser
            $browser->assertSee('TERSEDIA')
                    // Memastikan teks '(PENUH)' TIDAK muncul pada halaman browser
                    ->assertDontSee('(PENUH)');
        });
    }

    /**
     * TC-PBI38-002 (Negatif)
     * Slot waktu pengantaran yang sudah penuh (mencapai batas maksimal pesanan per jam)
     * ditampilkan sebagai dinonaktifkan (disabled) dan tidak bisa dipilih.
     */
    public function test_checkout_menonaktifkan_slot_waktu_yang_sudah_penuh(): void
    {
        [$consumerEmail, $password, $product] = $this->setupTestData(1, true);

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

            // Verify that the full slot displays "(PENUH)" and has a disabled attribute
            // Memastikan teks '(PENUH)' terlihat pada halaman browser
            $browser->assertSee('(PENUH)')
                    ->assertPresent('button[disabled]');
        });
    }
}
