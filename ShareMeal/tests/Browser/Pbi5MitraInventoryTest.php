<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * PBI-5: Mitra Inventory
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
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

            // Mengunjungi halaman '/login'
            $browser->visit('/login')
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
                    // Mengunjungi halaman '/mitra/inventory'
                    ->visit('/mitra/inventory')
                    // Menunggu elemen 'elemen terkait' muncul di layar (batas waktu standar detik)
                    ->waitFor('@tambah-produk-btn')
                    // Mengeklik elemen '@tambah-produk-btn' di halaman
                    ->click('@tambah-produk-btn')
                    // Menunggu elemen 'elemen terkait' muncul di layar (batas waktu standar detik)
                    ->waitFor('input[name="name"]')
                    // Submit directly using script to bypass HTML5 client-side validation
                    // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
                    ->script('document.querySelector("form[action*=\'/mitra/inventory\']").submit();');

            // Menunggu halaman berpindah ke rute '/mitra/inventory' (batas waktu 15 detik)
            $browser->waitForLocation('/mitra/inventory', 15)
                    // Memastikan teks 'The name field is required.' terlihat pada halaman browser
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

            // Mengunjungi halaman '/login'
            $browser->visit('/login')
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
                    // Mengunjungi halaman '/mitra/inventory'
                    ->visit('/mitra/inventory')
                    // Menunggu elemen 'elemen terkait' muncul di layar (batas waktu standar detik)
                    ->waitFor('@tambah-produk-btn')
                    // Mengeklik elemen '@tambah-produk-btn' di halaman
                    ->click('@tambah-produk-btn')
                    // Menunggu elemen 'elemen terkait' muncul di layar (batas waktu standar detik)
                    ->waitFor('input[name="name"]')
                    // Mengisi input 'name' dengan nilai 'Roti Cokelat Spesial'
                    ->type('name', 'Roti Cokelat Spesial')
                    // Memilih opsi 'Bakery' pada dropdown 'category'
                    ->select('category', 'Bakery')
                    // Mengisi input 'price' dengan nilai '15000'
                    ->type('price', '15000')
                    // Mengisi input 'stock' dengan nilai '10'
                    ->type('stock', '10')
                    // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
                    ->script([
                        "document.querySelector('input[name=\"expires_at\"]').value = '2026-06-30T12:00';",
                        "document.querySelector('input[name=\"pickup_start_time\"]').value = '09:00';",
                        "document.querySelector('input[name=\"pickup_end_time\"]').value = '18:00';"
                    ]);

            // Mengeklik elemen 'elemen terkait' di halaman
            $browser->click('form[action*="/mitra/inventory"] button[type="submit"]')
                    // Menunggu teks 'Produk berhasil ditambahkan.' muncul di layar (batas waktu 15 detik)
                    ->waitForText('Produk berhasil ditambahkan.', 15)
                    // Memastikan teks 'Produk berhasil ditambahkan.' terlihat pada halaman browser
                    ->assertSee('Produk berhasil ditambahkan.')
                    // Memastikan teks 'Roti Cokelat Spesial' terlihat pada halaman browser
                    ->assertSee('Roti Cokelat Spesial');

            // Step 2: Aktifkan flash sale untuk produk tersebut
            // Mengeklik elemen '@flash-sale-btn' di halaman
            $browser->click('@flash-sale-btn')
                    ->acceptDialog()
                    // Menunggu teks 'Flash sale diaktifkan.' muncul di layar (batas waktu 15 detik)
                    ->waitForText('Flash sale diaktifkan.', 15)
                    // Memastikan teks 'Flash sale diaktifkan.' terlihat pada halaman browser
                    ->assertSee('Flash sale diaktifkan.');
        });
    }
}
