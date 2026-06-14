<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class Pbi5MitraInventoryTest extends DuskTestCase
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
     * 1. Menguji validasi tambah produk flash sale dengan data kosong (Negative Test).
     */
    public function test_mitra_gagal_tambah_produk_karena_form_kosong(): void
    {
        $email = 'mitra5_neg_' . time() . '_' . rand(1000, 9999) . '@example.com';
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
                    ->waitFor('@tambah-produk-btn')
                    ->click('@tambah-produk-btn')
                    ->waitFor('input[name="name"]')
                    // Submit directly using script to bypass HTML5 client-side validation
                    ->script('document.querySelector("form[action*=\'/mitra/inventory\']").submit();');

            $browser->waitForLocation('/mitra/inventory', 15)
                    ->assertSee('The name field is required.');
        });
    }

    /**
     * 2. Menguji fungsionalitas menambahkan produk flash sale (Positive Test).
     */
    public function test_mitra_berhasil_menambahkan_produk_flash_sale(): void
    {
        $email = 'mitra5_pos_' . time() . '_' . rand(1000, 9999) . '@example.com';
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
                    ->waitFor('@tambah-produk-btn')
                    ->click('@tambah-produk-btn')
                    ->waitFor('input[name="name"]')
                    ->type('name', 'Roti Cokelat Spesial')
                    ->select('category', 'Bakery')
                    ->type('price', '15000')
                    ->type('stock', '10')
                    ->script([
                        "document.querySelector('input[name=\"expires_at\"]').value = '2026-06-30T12:00';",
                        "document.querySelector('input[name=\"pickup_start_time\"]').value = '09:00';",
                        "document.querySelector('input[name=\"pickup_end_time\"]').value = '18:00';"
                    ]);

            $browser->click('form[action*="/mitra/inventory"] button[type="submit"]')
                    ->waitForText('Produk berhasil ditambahkan.', 15)
                    ->assertSee('Produk berhasil ditambahkan.')
                    ->assertSee('Roti Cokelat Spesial');

            // Step 2: Aktifkan flash sale untuk produk tersebut
            $browser->click('@flash-sale-btn')
                    ->acceptDialog()
                    ->waitForText('Flash sale diaktifkan.', 15)
                    ->assertSee('Flash sale diaktifkan.');
        });
    }
}
