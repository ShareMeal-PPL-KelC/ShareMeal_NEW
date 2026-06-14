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
            
            $browser->maximize()
                    ->visit('/login')
                    ->waitFor('select[name="user_type"]')
                    ->select('user_type', 'mitra')
                    ->type('email', $email)
                    ->type('password', $password)
                    ->click('button[type="submit"]')
                    ->waitForLocation('/mitra', 15)
                    ->visit(route('mitra.orders'))
                    ->waitForLocation('/mitra/orders', 15)
                    ->pause(2000);
            
            // 1. Konfirmasi Pembayaran dan Proses Pesanan
            $browser->script("
                let btn = document.querySelector('button[\\\\@click*=\"updateStatus(order.id, \\'processing\\')\"]');
                if(btn) btn.click();
                else throw new Error('Tombol Proses Pesanan tidak ditemukan');
            ");
            $browser->pause(1000);
            
            // Klik Ya, Lanjutkan
            $browser->script("
                let confirmBtn = document.querySelector('button[\\\\@click*=\"executeConfirm()\"]');
                if(confirmBtn) confirmBtn.click();
                else throw new Error('Tombol Ya, Lanjutkan tidak ditemukan');
            ");
            $browser->pause(2500); // Tunggu animasi dan render ulang
            
            // 2. Pesanan Siap
            $browser->script("
                let btnReady = document.querySelector('button[\\\\@click*=\"updateStatus(order.id, \\'ready\\')\"]');
                if(btnReady) btnReady.click();
                else throw new Error('Tombol Pesanan Siap tidak ditemukan');
            ");
            $browser->pause(1000);
            
            // Klik Ya, Lanjutkan
            $browser->script("
                let confirmBtn = document.querySelector('button[\\\\@click*=\"executeConfirm()\"]');
                if(confirmBtn) confirmBtn.click();
                else throw new Error('Tombol Ya, Lanjutkan tidak ditemukan');
            ");
            $browser->pause(2500);
            
            // 3. Kirim Sekarang
            $browser->script("
                let btnShipping = document.querySelector('button[\\\\@click*=\"updateStatus(order.id, \\'shipping\\')\"]');
                if(btnShipping) btnShipping.click();
                else throw new Error('Tombol Kirim Sekarang tidak ditemukan');
            ");
            $browser->pause(1000);
            
            // Klik Ya, Lanjutkan
            $browser->script("
                let confirmBtn = document.querySelector('button[\\\\@click*=\"executeConfirm()\"]');
                if(confirmBtn) confirmBtn.click();
                else throw new Error('Tombol Ya, Lanjutkan tidak ditemukan');
            ");
            $browser->pause(2500);
            
            // 4. Konfirmasi Sampai & Selesai
            $browser->script("
                let btnCompleted = document.querySelector('button[\\\\@click*=\"updateStatus(order.id, \\'completed\\')\"]');
                if(btnCompleted) btnCompleted.click();
                else throw new Error('Tombol Konfirmasi Sampai & Selesai tidak ditemukan');
            ");
            $browser->pause(1000);
            
            // Klik Ya, Lanjutkan
            $browser->script("
                let confirmBtn = document.querySelector('button[\\\\@click*=\"executeConfirm()\"]');
                if(confirmBtn) confirmBtn.click();
                else throw new Error('Tombol Ya, Lanjutkan tidak ditemukan');
            ");
            $browser->pause(2500);
            
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
            
            $browser->maximize()
                    ->visit('/login')
                    ->waitFor('select[name="user_type"]')
                    ->select('user_type', 'mitra')
                    ->type('email', $email)
                    ->type('password', $password)
                    ->click('button[type="submit"]')
                    ->waitForLocation('/mitra', 15)
                    ->visit(route('mitra.orders'))
                    ->waitForLocation('/mitra/orders', 15)
                    ->pause(2000)
                    ->assertSee('Daftar Pesanan Masuk')

                    // Klik tombol "Batalkan"
                    ->waitForText('Batalkan')
                    ->script("
                        let btn = Array.from(document.querySelectorAll('button')).find(b => b.textContent.trim() === 'Batalkan');
                        if(btn) btn.click();
                        else throw new Error('Tombol Batalkan tidak ditemukan');
                    ");
                    
            $browser->pause(1000)
                    
                    // Tulis stok makanan habis di field alasan pembatalan
                    ->waitFor('textarea')
                    ->type('textarea', 'stok makanan habis')
                    
                    // Pencet BATALKAN PESANAN
                    ->press('BATALKAN PESANAN')
                    ->pause(2500)
                    
                    // Selesai
                    ->assertPathIs('/mitra/orders');
        });
    }
}
