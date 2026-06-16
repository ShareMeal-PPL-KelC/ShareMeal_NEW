<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Product;

/**
 * PBI-40: Toggle Donasi
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
class Pbi40ToggleDonasiTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_positive_mitra_berhasil_toggle_donasi(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::factory()->create([
                'role' => 'mitra',
                'name' => 'Mitra Sukses',
                'is_verified' => true,
            ]);

            UserProfile::create([
                'user_id' => $mitra->id,
                'business_name' => 'Mitra Sukses',
                'business_type' => 'Restoran',
                'business_address' => 'Jl. Sukses No. 40',
                'business_contact' => '081234567890',
                'business_opening_hours' => '08:00 - 20:00',
                'business_description' => 'Toko makanan sukses berkah.',
            ]);

            $product = Product::factory()->create([
                'user_id' => $mitra->id,
                'name' => 'Roti Tawar Gandum',
                'price' => 15000,
                'discount_price' => 0,
                'stock' => 10,
                'status' => 'normal',
                'expires_at' => now()->addHours(5),
                'donatable' => false,
            ]);

            $browser->loginAs($mitra)
                    // Mengunjungi halaman '/mitra/inventory'
                    ->visit('/mitra/inventory')
                    // Menunggu teks '' muncul di layar (batas waktu standar detik)
                    ->waitForText('Roti Tawar Gandum')
                    // Memastikan teks 'Roti Tawar Gandum' terlihat pada halaman browser
                    ->assertSee('Roti Tawar Gandum')
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('button[title="Aktifkan donasi otomatis"]')
                    // Menunggu teks '' muncul di layar (batas waktu standar detik)
                    ->waitForText('Donasi otomatis untuk "Roti Tawar Gandum" berhasil diaktifkan')
                    // Memastikan teks yang diharapkan muncul di layar
                    ->assertSee('Donasi otomatis untuk "Roti Tawar Gandum" berhasil diaktifkan');
        });
    }

    public function test_negative_mitra_gagal_toggle_donasi_expired_atau_habis(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::factory()->create([
                'role' => 'mitra',
                'name' => 'Mitra Gagal',
                'is_verified' => true,
            ]);

            // Create complete profile to bypass EnsureProfileIsComplete middleware
            UserProfile::create([
                'user_id' => $mitra->id,
                'business_name' => 'Mitra Gagal',
                'business_type' => 'Restoran',
                'business_address' => 'Jl. Gagal No. 40',
                'business_contact' => '081234567891',
                'business_opening_hours' => '08:00 - 20:00',
                'business_description' => 'Toko makanan gagal berkah.',
            ]);

            $product = Product::factory()->create([
                'user_id' => $mitra->id,
                'name' => 'Kue Sus Expired',
                'price' => 10000,
                'discount_price' => 0,
                'stock' => 0, // out of stock
                'status' => 'normal',
                'expires_at' => now()->subHours(1), // expired
                'donatable' => false,
            ]);

            $browser->loginAs($mitra)
                    // Mengunjungi halaman '/mitra/inventory'
                    ->visit('/mitra/inventory')
                    // Menunggu teks '' muncul di layar (batas waktu standar detik)
                    ->waitForText('Kue Sus Expired')
                    // Verify the button has the title 'Produk habis atau kedaluwarsa' and is disabled
                    ->assertPresent('button[title="Produk habis atau kedaluwarsa"]')
                    // Force remove disabled attribute via JS to simulate bypassing client side validation
                    // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
                    ->script("document.querySelector('button[title=\"Produk habis atau kedaluwarsa\"]').removeAttribute('disabled')");
            
            // Click the button
            // Mengeklik elemen 'elemen terkait' di halaman
            $browser->click('button[title="Produk habis atau kedaluwarsa"]')
                    // Menunggu teks '' muncul di layar (batas waktu standar detik)
                    ->waitForText('Produk sudah habis atau kedaluwarsa')
                    // Memastikan teks 'Produk sudah habis atau kedaluwarsa' terlihat pada halaman browser
                    ->assertSee('Produk sudah habis atau kedaluwarsa');
        });
    }
}
