<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * PBI-13: Waktu Layak Konsumsi
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
class Pbi13WaktuLayakKonsumsiTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * TC-PBI13-001 (Positif)
     * Konsumen dengan pesanan aktif yang produknya memiliki expires_at
     * dapat melihat "Layak Konsumsi s/d" di halaman pesanan aktif.
     */
    public function test_konsumen_dapat_melihat_batas_waktu_kelayakan_konsumsi_pada_pesanan_aktif(): void
    {
        // Setup mitra
        $mitra = User::factory()->create(['role' => 'mitra', 'name' => 'Toko Roti Makmur', 'is_verified' => true]);
        UserProfile::create([
            'user_id'                => $mitra->id,
            'phone'                  => '089876543210',
            'address'                => 'Jl. Sukabirus No. 45',
            'business_name'          => 'Toko Roti Makmur',
            'business_address'       => 'Jl. Sukabirus No. 45',
            'business_contact'       => '089876543210',
            'business_opening_hours' => '08:00 - 20:00',
            'is_verified'            => true,
        ]);

        // Produk dengan expires_at yang jelas
        $expiresAt = now()->addHours(6);
        $product = Product::create([
            'user_id'    => $mitra->id,
            'name'       => 'Susu Kurma Segar',
            'category'   => 'Healthy',
            'price'      => 15000,
            'stock'      => 10,
            'expires_at' => $expiresAt,
        ]);

        // Consumer
        $email    = 'consumer13_' . time() . '@example.com';
        $password = 'password123';
        $consumer = User::factory()->create([
            'role'        => 'consumer',
            'name'        => 'Budi Santoso',
            'email'       => $email,
            'password'    => Hash::make($password),
            'is_verified' => true,
        ]);
        UserProfile::create([
            'user_id'     => $consumer->id,
            'phone'       => '081234567890',
            'address'     => 'Kost Orange, Bandung',
            'is_verified' => true,
        ]);

        // Order
        $order = Order::create([
            'customer_id'       => $consumer->id,
            'mitra_id'          => $mitra->id,
            'total_amount'      => 15000,
            'status'            => 'processing',
            'pickup_code'       => 'TST13A',
            'receiving_method'  => 'pickup',
            'payment_method'    => 'GoPay',
            'pickup_start_time' => '14:00:00',
            'pickup_end_time'   => '20:00:00',
        ]);
        OrderItem::create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'quantity'   => 1,
            'price'      => 15000,
        ]);

        $this->browse(function (Browser $browser) use ($email, $password) {
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
                ->waitForLocation('/consumer', 15)
                // Mengunjungi halaman 'halaman terkait'
                ->visit(route('consumer.orders.active'))
                // Menunggu halaman berpindah ke rute '/consumer/orders/active' (batas waktu 15 detik)
                ->waitForLocation('/consumer/orders/active', 15)
                // Menjeda eksekusi selama 2000 milidetik agar proses render/transisi halaman selesai
                ->pause(2000)
                // Memastikan teks 'Layak Konsumsi s/d:' terlihat pada halaman browser
                ->assertSee('Layak Konsumsi s/d:');
        });
    }

    /**
     * TC-PBI13-002 (Negatif)
     * Konsumen tanpa pesanan aktif tidak melihat informasi kelayakan konsumsi.
     */
    public function test_konsumen_tanpa_pesanan_aktif_tidak_melihat_batas_waktu_kelayakan_konsumsi(): void
    {
        $email    = 'consumer13b_' . time() . '@example.com';
        $password = 'password123';

        $consumer = User::factory()->create([
            'role'        => 'consumer',
            'name'        => 'Siti Rahayu',
            'email'       => $email,
            'password'    => Hash::make($password),
            'is_verified' => true,
        ]);
        UserProfile::create([
            'user_id'     => $consumer->id,
            'phone'       => '082234567890',
            'address'     => 'Jl. Bunga No. 5, Bandung',
            'is_verified' => true,
        ]);

        $this->browse(function (Browser $browser) use ($email, $password) {
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
                ->waitForLocation('/consumer', 15)
                // Mengunjungi halaman 'halaman terkait'
                ->visit(route('consumer.orders.active'))
                // Menunggu halaman berpindah ke rute '/consumer/orders/active' (batas waktu 15 detik)
                ->waitForLocation('/consumer/orders/active', 15)
                // Menjeda eksekusi selama 2000 milidetik agar proses render/transisi halaman selesai
                ->pause(2000)
                // Memastikan teks 'Layak Konsumsi s/d:' TIDAK muncul pada halaman browser
                ->assertDontSee('Layak Konsumsi s/d:');
        });
    }
}