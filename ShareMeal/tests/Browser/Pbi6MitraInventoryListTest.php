<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class Pbi6MitraInventoryListTest extends DuskTestCase
{
    use DatabaseMigrations;

    private function createMitraWithProfile(string $email, string $password): User
    {
        $mitra = User::factory()->create([
            'role' => 'mitra',
            'status' => 'active',
            'email' => $email,
            'password' => Hash::make($password),
            'is_verified' => true,
        ]);

        $mitra->profile()->create([
            'business_name' => 'Resto Flash Sale',
            'business_type' => 'Bakery',
            'business_address' => 'Jl. Pahlawan No. 45',
            'business_contact' => '081234567890',
            'business_opening_hours' => '08:00 - 20:00',
            'opening_hours' => '08:00 - 20:00',
            'description' => 'Menyediakan kue dan roti segar setiap hari.',
            'business_description' => 'Menyediakan kue dan roti segar setiap hari.',
            'is_verified' => true,
        ]);

        return $mitra;
    }

    /**
     * 1. Menguji kondisi daftar produk ketika belum ada data (Negative / Empty State).
     */
    public function test_mitra_melihat_tampilan_kosong_saat_tidak_ada_produk(): void
    {
        $email = 'mitra6_neg_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $password = 'password123';
        $this->createMitraWithProfile($email, $password);

        $this->browse(function (Browser $browser) use ($email, $password) {
            $browser->driver->manage()->deleteAllCookies();

            $browser->visit('/login')
                    ->select('user_type', 'mitra')
                    ->type('email', $email)
                    ->type('password', $password)
                    ->click('button[type="submit"]')
                    ->waitForLocation('/mitra', 15)
                    ->visit('/mitra/inventory')
                    ->waitForText('Manajemen Inventaris Surplus', 15)
                    ->assertDontSee('Roti Keju Spesial');
        });
    }

    /**
     * 2. Menguji tampilan daftar produk flash sale (Positive Test).
     */
    public function test_mitra_dapat_melihat_daftar_produk_flash_sale(): void
    {
        $email = 'mitra6_pos_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $password = 'password123';
        $mitra = $this->createMitraWithProfile($email, $password);

        // Seeding produk
        Product::create([
            'user_id' => $mitra->id,
            'name' => 'Roti Keju Spesial',
            'category' => 'Bakery',
            'price' => 15000,
            'discount_price' => 10500,
            'stock' => 15,
            'status' => 'flash-sale',
            'expires_at' => now()->addDays(2),
            'pickup_start_time' => '09:00',
            'pickup_end_time' => '18:00',
        ]);

        $this->browse(function (Browser $browser) use ($email, $password) {
            $browser->driver->manage()->deleteAllCookies();

            $browser->visit('/login')
                    ->select('user_type', 'mitra')
                    ->type('email', $email)
                    ->type('password', $password)
                    ->click('button[type="submit"]')
                    ->waitForLocation('/mitra', 15)
                    ->visit('/mitra/inventory')
                    ->waitForText('Roti Keju Spesial', 15)
                    ->assertSee('Roti Keju Spesial')
                    ->assertSee('15 Pcs');
        });
    }
}
