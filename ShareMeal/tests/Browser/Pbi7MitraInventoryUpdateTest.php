<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * PBI-7: Mitra Inventory Update
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
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
                    ->waitFor('@edit-produk-btn')
                    // Mengeklik elemen '@edit-produk-btn' di halaman
                    ->click('@edit-produk-btn')
                    // Menunggu elemen 'elemen terkait' muncul di layar (batas waktu standar detik)
                    ->waitFor('input[name="name"]')
                    // Clear inputs and submit
                    // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
                    ->script([
                        "document.querySelector('input[name=\"name\"]').value = '';",
                        "document.querySelector('form[action*=\'/mitra/inventory/\']').submit();"
                    ]);

            // Menunggu halaman berpindah ke rute '/mitra/inventory' (batas waktu 15 detik)
            $browser->waitForLocation('/mitra/inventory', 15)
                    // Memastikan teks 'The name field is required.' terlihat pada halaman browser
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
                    ->waitFor('@edit-produk-btn')
                    // Mengeklik elemen '@edit-produk-btn' di halaman
                    ->click('@edit-produk-btn')
                    // Menunggu elemen 'elemen terkait' muncul di layar (batas waktu standar detik)
                    ->waitFor('input[name="name"]')
                    // Mengisi input 'name' dengan nilai 'Roti Tawar Gandum Baru'
                    ->type('name', 'Roti Tawar Gandum Baru')
                    // Mengisi input 'price' dengan nilai '15000'
                    ->type('price', '15000')
                    // Mengisi input 'stock' dengan nilai '25'
                    ->type('stock', '25')
                    // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
                    ->script([
                        "document.querySelector('input[name=\"expires_at\"]').value = '2026-06-30T12:00';",
                        "document.querySelector('input[name=\"pickup_start_time\"]').value = '09:00';",
                        "document.querySelector('input[name=\"pickup_end_time\"]').value = '18:00';"
                    ]);

            // Mengeklik elemen 'elemen terkait' di halaman
            $browser->click('form[action*="/mitra/inventory/"] button[type="submit"]')
                    // Menunggu teks 'Informasi produk berhasil diperbarui.' muncul di layar (batas waktu 15 detik)
                    ->waitForText('Informasi produk berhasil diperbarui.', 15)
                    // Memastikan teks 'Informasi produk berhasil diperbarui.' terlihat pada halaman browser
                    ->assertSee('Informasi produk berhasil diperbarui.')
                    // Memastikan teks 'Roti Tawar Gandum Baru' terlihat pada halaman browser
                    ->assertSee('Roti Tawar Gandum Baru')
                    // Memastikan teks '25 Pcs' terlihat pada halaman browser
                    ->assertSee('25 Pcs');
        });
    }
}
