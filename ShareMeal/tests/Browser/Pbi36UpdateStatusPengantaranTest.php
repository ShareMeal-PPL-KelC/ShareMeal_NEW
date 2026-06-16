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
 * PBI-36: Update Status Pengantaran
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
class Pbi36UpdateStatusPengantaranTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test PBI-36: Update Status Pengantaran oleh Mitra.
     *
     * @return void
     */
    public function testUpdateStatusPengantaran()
    {
        $email = 'mitra_pos_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $password = 'password123';

        // 1. Create Mitra User
        $mitra = User::factory()->create([
            'role' => 'mitra',
            'email' => $email,
            'password' => Hash::make($password),
            'name' => 'Mitra PBI 36 Pos',
            'is_verified' => true,
        ]);

        // 2. Create Mitra UserProfile
        UserProfile::create([
            'user_id' => $mitra->id,
            'phone' => '089876543210',
            'address' => 'Jl. PGA No. 8, Bandung',
            'latitude' => -6.974028,
            'longitude' => 107.630528,
            'business_type' => 'Bakery',
            'business_name' => 'Mitra Roti 36 Pos',
            'business_address' => 'Jl. PGA No. 8, Bandung',
            'business_contact' => '089876543210',
            'business_opening_hours' => '08:00 - 20:00',
            'business_description' => 'Roti lezat',
            'is_verified' => true,
            'can_delivery' => true,
            'delivery_fee' => 5000,
            'delivery_slot_limit' => 10,
        ]);

        // 3. Create Consumer User
        $consumer = User::factory()->create([
            'role' => 'consumer',
            'is_verified' => true,
        ]);

        // 4. Create Product
        $product = Product::create([
            'user_id' => $mitra->id,
            'name' => 'Roti Manis Pos',
            'category' => 'Bakery',
            'price' => 15000,
            'stock' => 10,
            'image' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=500&h=300&fit=crop',
            'expires_at' => now()->addHours(8),
        ]);

        // 5. Create Pending Order (delivery)
        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 30000,
            'status' => 'pending',
            'pickup_code' => 'TESTPOS36',
            'receiving_method' => 'delivery',
            'delivery_fee' => 5000,
            'delivery_time_slot' => '14:00 - 15:00',
            'payment_method' => 'GoPay',
        ]);

        // 6. Create OrderItem
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 15000,
        ]);

        $this->browse(function (Browser $browser) use ($email, $password) {
            $browser->driver->manage()->deleteAllCookies();
            
            // Memaksimalkan ukuran jendela browser agar tampilan terlihat penuh
            $browser->maximize()
                    // Mengunjungi halaman '/login'
                    ->visit('/login')
                    // Menunggu elemen 'elemen terkait' muncul di layar (batas waktu standar detik)
                    ->waitFor('select[name="user_type"]')
                    // Memilih opsi 'mitra' pada dropdown 'user_type'
                    ->select('user_type', 'mitra')
                    // Mengisi input field 'email'
                    ->type('email', $email)
                    // Mengisi input field 'password'
                    ->type('password', $password)
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('button[type="submit"]')
                    // Menunggu halaman berpindah ke rute '/mitra' (batas waktu 15 detik)
                    ->waitForLocation('/mitra', 15)
                    // Mengunjungi halaman 'halaman terkait'
                    ->visit(route('mitra.orders'))
                    // Menunggu halaman berpindah ke rute '/mitra/orders' (batas waktu 15 detik)
                    ->waitForLocation('/mitra/orders', 15)
                    // Menjeda eksekusi selama 2000 milidetik agar proses render/transisi halaman selesai
                    ->pause(2000);
            
            // 1. Konfirmasi Pembayaran dan Proses Pesanan
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("
                let btn = document.querySelector('button[\\\\@click*=\"updateStatus(order.id, \\'processing\\')\"]');
                if(btn) btn.click();
                else throw new Error('Tombol Proses Pesanan tidak ditemukan');
            ");
            // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(1000);
            
            // Klik Ya, Lanjutkan
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("
                let confirmBtn = document.querySelector('button[\\\\@click*=\"executeConfirm()\"]');
                if(confirmBtn) confirmBtn.click();
                else throw new Error('Tombol Ya, Lanjutkan tidak ditemukan');
            ");
            // Menjeda eksekusi selama 2500 milidetik agar proses render/transisi halaman selesai
            $browser->pause(2500); // Tunggu animasi dan render ulang
            
            // 2. Pesanan Siap
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("
                let btnReady = document.querySelector('button[\\\\@click*=\"updateStatus(order.id, \\'ready\\')\"]');
                if(btnReady) btnReady.click();
                else throw new Error('Tombol Pesanan Siap tidak ditemukan');
            ");
            // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(1000);
            
            // Klik Ya, Lanjutkan
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("
                let confirmBtn = document.querySelector('button[\\\\@click*=\"executeConfirm()\"]');
                if(confirmBtn) confirmBtn.click();
                else throw new Error('Tombol Ya, Lanjutkan tidak ditemukan');
            ");
            // Menjeda eksekusi selama 2500 milidetik agar proses render/transisi halaman selesai
            $browser->pause(2500);
            
            // 3. Kirim Sekarang
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("
                let btnShipping = document.querySelector('button[\\\\@click*=\"updateStatus(order.id, \\'shipping\\')\"]');
                if(btnShipping) btnShipping.click();
                else throw new Error('Tombol Kirim Sekarang tidak ditemukan');
            ");
            // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(1000);
            
            // Klik Ya, Lanjutkan
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("
                let confirmBtn = document.querySelector('button[\\\\@click*=\"executeConfirm()\"]');
                if(confirmBtn) confirmBtn.click();
                else throw new Error('Tombol Ya, Lanjutkan tidak ditemukan');
            ");
            // Menjeda eksekusi selama 2500 milidetik agar proses render/transisi halaman selesai
            $browser->pause(2500);
            
            // 4. Konfirmasi Sampai & Selesai
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("
                let btnCompleted = document.querySelector('button[\\\\@click*=\"updateStatus(order.id, \\'completed\\')\"]');
                if(btnCompleted) btnCompleted.click();
                else throw new Error('Tombol Konfirmasi Sampai & Selesai tidak ditemukan');
            ");
            // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(1000);
            
            // Klik Ya, Lanjutkan
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("
                let confirmBtn = document.querySelector('button[\\\\@click*=\"executeConfirm()\"]');
                if(confirmBtn) confirmBtn.click();
                else throw new Error('Tombol Ya, Lanjutkan tidak ditemukan');
            ");
            // Menjeda eksekusi selama 2500 milidetik agar proses render/transisi halaman selesai
            $browser->pause(2500);
            
            // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/mitra/orders'
            $browser->assertPathIs('/mitra/orders');
        });
    }

    /**
     * Test PBI-36: Update Status Pengantaran Negatif (Pembatalan) oleh Mitra.
     *
     * @return void
     */
    public function testUpdateStatusPengantaranNegatif()
    {
        $email = 'mitra_neg_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $password = 'password123';

        // 1. Create Mitra User
        $mitra = User::factory()->create([
            'role' => 'mitra',
            'email' => $email,
            'password' => Hash::make($password),
            'name' => 'Mitra PBI 36 Neg',
            'is_verified' => true,
        ]);

        // 2. Create Mitra UserProfile
        UserProfile::create([
            'user_id' => $mitra->id,
            'phone' => '089876543210',
            'address' => 'Jl. PGA No. 8, Bandung',
            'latitude' => -6.974028,
            'longitude' => 107.630528,
            'business_type' => 'Bakery',
            'business_name' => 'Mitra Roti 36 Neg',
            'business_address' => 'Jl. PGA No. 8, Bandung',
            'business_contact' => '089876543210',
            'business_opening_hours' => '08:00 - 20:00',
            'business_description' => 'Roti lezat',
            'is_verified' => true,
            'can_delivery' => true,
            'delivery_fee' => 5000,
            'delivery_slot_limit' => 10,
        ]);

        // 3. Create Consumer User
        $consumer = User::factory()->create([
            'role' => 'consumer',
            'is_verified' => true,
        ]);

        // 4. Create Product
        $product = Product::create([
            'user_id' => $mitra->id,
            'name' => 'Roti Manis Neg',
            'category' => 'Bakery',
            'price' => 15000,
            'stock' => 10,
            'image' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=500&h=300&fit=crop',
            'expires_at' => now()->addHours(8),
        ]);

        // 5. Create Pending Order
        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 30000,
            'status' => 'pending',
            'pickup_code' => 'TESTNEG36',
            'receiving_method' => 'delivery',
            'delivery_fee' => 5000,
            'delivery_time_slot' => '14:00 - 15:00',
            'payment_method' => 'GoPay',
        ]);

        // 6. Create OrderItem
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 15000,
        ]);

        $this->browse(function (Browser $browser) use ($email, $password) {
            $browser->driver->manage()->deleteAllCookies();
            
            // Memaksimalkan ukuran jendela browser agar tampilan terlihat penuh
            $browser->maximize()
                    // Mengunjungi halaman '/login'
                    ->visit('/login')
                    // Menunggu elemen 'elemen terkait' muncul di layar (batas waktu standar detik)
                    ->waitFor('select[name="user_type"]')
                    // Memilih opsi 'mitra' pada dropdown 'user_type'
                    ->select('user_type', 'mitra')
                    // Mengisi input field 'email'
                    ->type('email', $email)
                    // Mengisi input field 'password'
                    ->type('password', $password)
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('button[type="submit"]')
                    // Menunggu halaman berpindah ke rute '/mitra' (batas waktu 15 detik)
                    ->waitForLocation('/mitra', 15)
                    // Mengunjungi halaman 'halaman terkait'
                    ->visit(route('mitra.orders'))
                    // Menunggu halaman berpindah ke rute '/mitra/orders' (batas waktu 15 detik)
                    ->waitForLocation('/mitra/orders', 15)
                    // Menjeda eksekusi selama 2000 milidetik agar proses render/transisi halaman selesai
                    ->pause(2000)
                    // Memastikan teks 'Daftar Pesanan Masuk' terlihat pada halaman browser
                    ->assertSee('Daftar Pesanan Masuk')

                    // Klik tombol "Batalkan"
                    // Menunggu teks '' muncul di layar (batas waktu standar detik)
                    ->waitForText('Batalkan')
                    // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
                    ->script("
                        let btn = Array.from(document.querySelectorAll('button')).find(b => b.textContent.trim() === 'Batalkan');
                        if(btn) btn.click();
                        else throw new Error('Tombol Batalkan tidak ditemukan');
                    ");
                    
            // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(1000)
                    
                    // Tulis stok makanan habis di field alasan pembatalan
                    // Menunggu elemen 'elemen terkait' muncul di layar (batas waktu standar detik)
                    ->waitFor('textarea')
                    // Mengisi input 'textarea' dengan nilai 'stok makanan habis'
                    ->type('textarea', 'stok makanan habis')
                    
                    // Pencet BATALKAN PESANAN
                    // Menekan tombol dengan teks/properti 'BATALKAN PESANAN'
                    ->press('BATALKAN PESANAN')
                    // Menjeda eksekusi selama 2500 milidetik agar proses render/transisi halaman selesai
                    ->pause(2500)
                    
                    // Selesai
                    // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/mitra/orders'
                    ->assertPathIs('/mitra/orders');
        });
    }
}
