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

class Pbi12MelihatPesananMasukTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * TC-PBI12-001 - Mitra Toko Roti Makmur dapat melihat pesanan Budi Santoso.
     */
    public function test_mitra_toko_roti_makmur_dapat_melihat_pesanan_budi(): void
    {
        $mitraEmail = 'mitra12_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $mitraPassword = 'password123';

        // 1. Create Mitra
        $mitra = User::factory()->create([
            'role' => 'mitra',
            'name' => 'Hendra Wijaya',
            'email' => $mitraEmail,
            'password' => Hash::make($mitraPassword),
            'is_verified' => true,
        ]);
        UserProfile::create([
            'user_id' => $mitra->id,
            'phone' => '089876543210',
            'address' => 'Jl. Sukabirus No. 45, Dayeuhkolot, Bandung',
            'business_type' => 'Bakery',
            'business_name' => 'Toko Roti Makmur',
            'business_address' => 'Jl. Sukabirus No. 45, Dayeuhkolot, Bandung',
            'business_contact' => '089876543210',
            'business_opening_hours' => '08:00 - 20:00',
            'business_description' => 'Roti lezat',
            'is_verified' => true,
            'can_delivery' => true,
            'delivery_fee' => 5000,
            'delivery_slot_limit' => 10,
        ]);

        // 2. Create Consumer
        $consumer = User::factory()->create([
            'role' => 'consumer',
            'name' => 'Budi Santoso',
            'is_verified' => true,
        ]);
        UserProfile::create([
            'user_id' => $consumer->id,
            'phone' => '081234567890',
            'address' => 'Kost Orange, Bandung',
            'is_verified' => true,
        ]);

        // 3. Create Product
        $product = Product::create([
            'user_id' => $mitra->id,
            'name' => 'Susu Kurma Segar',
            'category' => 'Healthy',
            'price' => 15000,
            'stock' => 10,
            'image' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=500&h=300&fit=crop',
            'expires_at' => now()->addHours(8),
        ]);

        // 4. Create Order
        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 15000,
            'status' => 'pending',
            'pickup_code' => 'TEST12POS',
            'receiving_method' => 'pickup',
            'payment_method' => 'GoPay',
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 15000,
        ]);

        $this->browse(function (Browser $browser) use ($mitraEmail, $mitraPassword) {
            $browser->driver->manage()->deleteAllCookies();

            $browser->maximize()
                ->visit('/login')
                ->pause(2000)
                ->script("
                    let select = document.querySelector('select[name=\"user_type\"]');
                    if (select) {
                        select.value = 'mitra';
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                ");
            $browser->type('email', $mitraEmail)
                ->type('password', $mitraPassword)
                ->press('button[type="submit"]')
                ->pause(2000)
                ->visit(route('mitra.orders'))
                ->waitForLocation('/mitra/orders', 15)
                ->waitForText('Susu Kurma Segar', 15)
                ->assertSee('Susu Kurma Segar')
                ->assertSee('Budi Santoso');
        });
    }

    /**
     * TC-PBI12-002 - Mitra lain tidak boleh melihat pesanan milik Toko Roti Makmur.
     */
    public function test_mitra_lain_tidak_melihat_pesanan_mitra_lain(): void
    {
        $mitraEmail = 'mitra12_a_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $mitraPassword = 'password123';
        $otherMitraEmail = 'mitra12_b_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $otherMitraPassword = 'password123';

        // 1. Create Mitra (Toko Roti Makmur)
        $mitra = User::factory()->create([
            'role' => 'mitra',
            'name' => 'Hendra Wijaya',
            'email' => $mitraEmail,
            'password' => Hash::make($mitraPassword),
            'is_verified' => true,
        ]);
        UserProfile::create([
            'user_id' => $mitra->id,
            'phone' => '089876543210',
            'address' => 'Jl. Sukabirus No. 45, Dayeuhkolot, Bandung',
            'business_type' => 'Bakery',
            'business_name' => 'Toko Roti Makmur',
            'business_address' => 'Jl. Sukabirus No. 45, Dayeuhkolot, Bandung',
            'business_contact' => '089876543210',
            'business_opening_hours' => '08:00 - 20:00',
            'business_description' => 'Roti lezat',
            'is_verified' => true,
            'can_delivery' => true,
            'delivery_fee' => 5000,
            'delivery_slot_limit' => 10,
        ]);

        // 2. Create Other Mitra (Warmindo Barokah)
        $otherMitra = User::factory()->create([
            'role' => 'mitra',
            'name' => 'Ahmad Barokah',
            'email' => $otherMitraEmail,
            'password' => Hash::make($otherMitraPassword),
            'is_verified' => true,
        ]);
        UserProfile::create([
            'user_id' => $otherMitra->id,
            'phone' => '082123456789',
            'address' => 'Jl. Telekomunikasi No. 20, Terusan Buah Batu, Bandung',
            'business_type' => 'Meals',
            'business_name' => 'Warmindo Barokah',
            'business_address' => 'Jl. Telekomunikasi No. 20, Terusan Buah Batu, Bandung',
            'business_contact' => '082123456789',
            'business_opening_hours' => '00:00 - 23:59',
            'business_description' => 'Warung makan mie instan',
            'is_verified' => true,
            'can_delivery' => true,
            'delivery_fee' => 3000,
            'delivery_slot_limit' => 15,
        ]);

        // 3. Create Consumer
        $consumer = User::factory()->create([
            'role' => 'consumer',
            'name' => 'Budi Santoso',
            'is_verified' => true,
        ]);
        UserProfile::create([
            'user_id' => $consumer->id,
            'phone' => '081234567890',
            'address' => 'Kost Orange, Bandung',
            'is_verified' => true,
        ]);

        // 4. Create Product belonging to Toko Roti Makmur
        $product = Product::create([
            'user_id' => $mitra->id,
            'name' => 'Susu Kurma Segar',
            'category' => 'Healthy',
            'price' => 15000,
            'stock' => 10,
            'image' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=500&h=300&fit=crop',
            'expires_at' => now()->addHours(8),
        ]);

        // 5. Create Order belonging to Toko Roti Makmur
        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 15000,
            'status' => 'pending',
            'pickup_code' => 'TEST12POS',
            'receiving_method' => 'pickup',
            'payment_method' => 'GoPay',
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 15000,
        ]);

        $this->browse(function (Browser $browser) use ($otherMitraEmail, $otherMitraPassword) {
            $browser->driver->manage()->deleteAllCookies();

            $browser->maximize()
                ->visit('/login')
                ->pause(2000)
                ->script("
                    let select = document.querySelector('select[name=\"user_type\"]');
                    if (select) {
                        select.value = 'mitra';
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                ");
            $browser->type('email', $otherMitraEmail)
                ->type('password', $otherMitraPassword)
                ->press('button[type="submit"]')
                ->pause(2000)
                ->visit(route('mitra.orders'))
                ->waitForLocation('/mitra/orders', 15)
                ->pause(2000)
                ->assertDontSee('Susu Kurma Segar');
        });
    }
}