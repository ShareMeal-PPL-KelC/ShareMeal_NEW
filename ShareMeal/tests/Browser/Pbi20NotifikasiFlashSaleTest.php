<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\UserProfile;
use App\Models\Store;
use Illuminate\Support\Facades\DB;

class Pbi20NotifikasiFlashSaleTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * [PBI 20] Notifikasi Sistem - Konsumen
     * Alur: Konsumen memfavoritkan toko -> Toko aktifkan flash sale -> Konsumen dapat notif.
     */
    public function test_positive_konsumen_menerima_notifikasi_dari_toko_favorit(): void
    {
        $this->browse(function (Browser $browser) {
            // --- 1. SETUP DATA ---
            $mitra = User::factory()->create([
                'role' => 'mitra',
                'name' => 'Resto Favorit PBI 20',
                'is_verified' => true,
            ]);

            UserProfile::create([
                'user_id' => $mitra->id,
                'business_name' => 'Resto Favorit PBI 20',
                'business_type' => 'Bakery',
                'business_address' => 'Jl. Testing No. 123',
                'business_contact' => '08123456789',
                'business_opening_hours' => '08:00 - 20:00',
                'business_description' => 'Toko roti favorit.',
                'phone' => '08123456789',
                'address' => 'Jl. Testing No. 123',
                'opening_hours' => '08:00 - 20:00'
            ]);

            // Buat record Store agar foreign key di favorite_stores tidak error
            $store = Store::create([
                'owner_user_id' => $mitra->id,
                'name' => 'Resto Favorit PBI 20',
                'category' => 'Bakery',
                'address' => 'Jl. Testing No. 123',
                'rating' => 5.0,
                'reviews_count' => 0
            ]);
            
            $consumer = User::factory()->create([
                'role' => 'consumer',
                'name' => 'Budi Konsumen'
            ]);

            // --- PENTING: Langkah Memfavoritkan Toko ---
            DB::table('favorite_stores')->insert([
                'user_id' => $consumer->id,
                'store_id' => $store->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // --- 2. MITRA: AKTIFKAN FLASH SALE ---
            $browser->loginAs($mitra)
                    ->visit('/mitra/inventory');
            
            Product::create([
                'user_id' => $mitra->id,
                'name' => 'Roti Manis PBI 20',
                'category' => 'Bakery',
                'price' => 20000,
                'stock' => 10,
                'status' => 'normal',
                'expires_at' => now()->addHours(24),
                'pickup_start_time' => '08:00',
                'pickup_end_time' => '20:00'
            ]);

            $browser->refresh()
                    ->waitForText('Roti Manis PBI 20', 10)
                    ->script('window.confirm = function() { return true; };');
            
            $browser->click('button.bg-orange-50') 
                    ->waitForText('Flash sale diaktifkan', 10);

            // --- 3. KONSUMEN: CEK NOTIFIKASI ---
            $browser->loginAs($consumer)
                    ->visitRoute('notifications.index')
                    ->waitForText('Toko favorite anda mengeluarkan promo flash sale', 15)
                    ->assertSee('Resto Favorit PBI 20 baru saja memulai flash sale untuk Roti Manis PBI 20')
                    ->click('@notification-link')
                    ->pause(2000)
                    ->assertPathIs('/consumer/search')
                    ->assertSee('Roti Manis PBI 20');
        });
    }

    public function test_negative_konsumen_tidak_menerima_notifikasi_jika_produk_bukan_flash_sale(): void
    {
        $this->browse(function (Browser $browser) {
            // Setup Mitra
            $mitra = User::factory()->create([
                'role' => 'mitra',
                'name' => 'Resto Berkah PBI 20 Neg',
                'is_verified' => true,
            ]);

            UserProfile::create([
                'user_id' => $mitra->id,
                'business_name' => 'Resto Berkah PBI 20 Neg',
                'business_type' => 'Bakery',
                'business_address' => 'Jl. Testing No. 789',
                'business_contact' => '08123456781',
                'business_opening_hours' => '08:00 - 20:00',
                'business_description' => 'Toko roti normal.',
                'phone' => '08123456781',
                'address' => 'Jl. Testing No. 789',
                'opening_hours' => '08:00 - 20:00'
            ]);

            $store = Store::create([
                'owner_user_id' => $mitra->id,
                'name' => 'Resto Berkah PBI 20 Neg',
                'category' => 'Bakery',
                'address' => 'Jl. Testing No. 789',
                'rating' => 4.0,
                'reviews_count' => 0
            ]);
            
            $consumer = User::factory()->create([
                'role' => 'consumer',
                'name' => 'Budi Konsumen Neg'
            ]);

            // Hubungkan sebagai favorite store (seperti skenario positif)
            DB::table('favorite_stores')->insert([
                'user_id' => $consumer->id,
                'store_id' => $store->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Mitra buat produk normal (bukan flash sale)
            $browser->loginAs($mitra)
                    ->visit('/mitra/inventory');
            
            Product::create([
                'user_id' => $mitra->id,
                'name' => 'Roti Normal PBI 20 Neg',
                'category' => 'Bakery',
                'price' => 12000,
                'stock' => 10,
                'status' => 'normal', // status normal, bukan flash-sale
                'expires_at' => now()->addHours(24),
                'pickup_start_time' => '08:00',
                'pickup_end_time' => '20:00'
            ]);

            // Konsumen cek notifikasi (tidak boleh ada notif flash sale untuk produk ini)
            $browser->loginAs($consumer)
                    ->visitRoute('notifications.index')
                    ->assertDontSee('Promo flash sale')
                    ->assertDontSee('Roti Normal PBI 20 Neg');
        });
    }
}

