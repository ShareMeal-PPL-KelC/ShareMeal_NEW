<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class Pbi7MitraInventoryUpdateTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Helper method untuk membuat Mitra dengan profil lengkap.
     */
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
     * 1. Menguji validasi update produk dengan data kosong (Negative Test).
     */
    public function test_mitra_gagal_update_produk_karena_form_kosong(): void
    {
        $email = 'mitra7_neg_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $password = 'password123';
        $mitra = $this->createMitraWithProfile($email, $password);

        // Seed produk untuk diedit
        $product = Product::create([
            'user_id' => $mitra->id,
            'name' => 'Roti Cokelat Lama',
            'category' => 'Bakery',
            'price' => 15000,
            'discount_price' => 10500,
            'stock' => 10,
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
                    ->waitFor('@edit-produk-btn')
                    ->click('@edit-produk-btn')
                    ->waitFor('input[name="name"]')
                    // Clear inputs and submit
                    ->script([
                        "document.querySelector('input[name=\"name\"]').value = '';",
                        "document.querySelector('form[action*=\'/mitra/inventory/\']').submit();"
                    ]);

            $browser->waitForLocation('/mitra/inventory', 15)
                    ->assertSee('The name field is required.');
        });
    }

    /**
     * 2. Menguji fungsionalitas update informasi produk flash sale (Positive Test).
     */
    public function test_mitra_berhasil_update_informasi_produk_flash_sale(): void
    {
        $email = 'mitra7_pos_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $password = 'password123';
        $mitra = $this->createMitraWithProfile($email, $password);

        // Seed produk untuk diedit
        $product = Product::create([
            'user_id' => $mitra->id,
            'name' => 'Roti Tawar Lama',
            'category' => 'Bakery',
            'price' => 12000,
            'discount_price' => 8400,
            'stock' => 10,
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
                    ->waitFor('@edit-produk-btn')
                    ->click('@edit-produk-btn')
                    ->waitFor('input[name="name"]')
                    ->type('name', 'Roti Tawar Gandum Baru')
                    ->type('price', '15000')
                    ->type('stock', '25')
                    ->script([
                        "document.querySelector('input[name=\"expires_at\"]').value = '2026-06-30T12:00';",
                        "document.querySelector('input[name=\"pickup_start_time\"]').value = '09:00';",
                        "document.querySelector('input[name=\"pickup_end_time\"]').value = '18:00';"
                    ]);

            $browser->click('form[action*="/mitra/inventory/"] button[type="submit"]')
                    ->waitForText('Informasi produk berhasil diperbarui.', 15)
                    ->assertSee('Informasi produk berhasil diperbarui.')
                    ->assertSee('Roti Tawar Gandum Baru')
                    ->assertSee('25 Pcs');
        });
    }
}
