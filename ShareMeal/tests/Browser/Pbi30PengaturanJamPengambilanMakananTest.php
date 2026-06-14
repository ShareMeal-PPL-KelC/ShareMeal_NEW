<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class Pbi30PengaturanJamPengambilanMakananTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_positive_set_pickup_hours(): void
    {
        $email = 'mitra_pos_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $password = 'password123';
        
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Mitra Jam Pos',
                'password' => Hash::make($password),
                'role' => 'mitra',
                'status' => 'active',
                'is_verified' => true,
                'joined_at' => now(),
            ]
        );
        $user->profile()->create([
            'business_name' => 'Toko Jam Pos',
            'business_type' => 'Bakery',
            'business_address' => 'Alamat Pos',
            'business_contact' => '081234567890',
            'business_opening_hours' => '08:00 - 18:00',
            'opening_hours' => '08:00 - 18:00',
            'business_description' => 'Deskripsi Toko',
            'description' => 'Deskripsi Toko',
        ]);

        $this->browse(function (Browser $browser) use ($email, $password) {
            // Step 1: Login
            $browser->visit('/login')
                    ->assertPathIs('/login')
                    ->select('user_type', 'mitra')
                    ->type('email', $email)
                    ->type('password', $password)
                    ->click('button[type="submit"]')
                    ->waitForLocation('/mitra', 15);

            // Step 2: Buka Halaman Pengaturan Inventaris
            $browser->visit('/mitra/inventory')
                    ->assertPathIs('/mitra/inventory')
                    ->assertSee('Manajemen Inventaris Surplus');

            // Klik 'Tambah Produk'
            $browser->click('[dusk="tambah-produk-btn"]')
                    ->waitForText('Tambah Produk Baru', 10);

            // Isi data wajib produk
            $browser->type('name', 'Roti Manis Enak')
                    ->type('price', '15000')
                    ->type('stock', '15');

            // Set waktu expired menggunakan JavaScript karena datetime-local
            $browser->script("document.querySelector('input[name=\"expires_at\"]').value = '2026-06-15T12:00';");

            // Set 'Jam Mulai Pengambilan' dan 'Jam Akhir Pengambilan' yang valid (dalam jam operasional: 08:00 - 18:00)
            $browser->type('pickup_start_time', '10:00')
                    ->type('pickup_end_time', '12:00');

            // Klik tombol 'Simpan Produk'
            $browser->click('form[action*="inventory"] button[type="submit"]')
                    // Tunggu reload dan verifikasi pesan sukses
                    ->waitForText('Produk berhasil ditambahkan.', 15)
                    ->assertSee('Produk berhasil ditambahkan.');
        });
    }

    public function test_negative_set_pickup_hours(): void
    {
        $email = 'mitra_neg_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $password = 'password123';
        
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Mitra Jam Neg',
                'password' => Hash::make($password),
                'role' => 'mitra',
                'status' => 'active',
                'is_verified' => true,
                'joined_at' => now(),
            ]
        );
        $user->profile()->create([
            'business_name' => 'Toko Jam Neg',
            'business_type' => 'Bakery',
            'business_address' => 'Alamat Neg',
            'business_contact' => '081234567890',
            'business_opening_hours' => '08:00 - 18:00',
            'opening_hours' => '08:00 - 18:00',
            'business_description' => 'Deskripsi Toko',
            'description' => 'Deskripsi Toko',
        ]);

        $this->browse(function (Browser $browser) use ($email, $password) {
            // Step 1: Login
            $browser->visit('/login')
                    ->assertPathIs('/login')
                    ->select('user_type', 'mitra')
                    ->type('email', $email)
                    ->type('password', $password)
                    ->click('button[type="submit"]')
                    ->waitForLocation('/mitra', 15);

            // Step 2: Buka Halaman Pengaturan Inventaris
            $browser->visit('/mitra/inventory')
                    ->assertPathIs('/mitra/inventory')
                    ->assertSee('Manajemen Inventaris Surplus');

            // Klik 'Tambah Produk'
            $browser->click('[dusk="tambah-produk-btn"]')
                    ->waitForText('Tambah Produk Baru', 10);

            // Isi data wajib produk
            $browser->type('name', 'Roti Gagal Pengambilan')
                    ->type('price', '15000')
                    ->type('stock', '10');

            // Set waktu expired
            $browser->script("document.querySelector('input[name=\"expires_at\"]').value = '2026-06-15T12:00';");

            // Set 'Jam Mulai' dan 'Jam Akhir' Pengambilan di luar jam operasional (jam operasional: 08:00 - 18:00)
            $browser->type('pickup_start_time', '19:00')
                    ->type('pickup_end_time', '20:00');

            // Klik tombol 'Simpan Produk'
            $browser->click('form[action*="inventory"] button[type="submit"]')
                    // Tunggu pesan kesalahan validasi muncul
                    ->waitForText('Jam mulai pengambilan harus di dalam jam operasional (08:00 - 18:00).', 15)
                    ->assertSee('Jam mulai pengambilan harus di dalam jam operasional (08:00 - 18:00).');
        });
    }
}
