<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * PBI-30: Pengaturan Jam Pengambilan Makanan
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
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
            // Mengunjungi halaman '/login'
            $browser->visit('/login')
                    // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/login'
                    ->assertPathIs('/login')
                    // Memilih opsi 'mitra' pada dropdown 'user_type'
                    ->select('user_type', 'mitra')
                    // Mengisi input field 'email'
                    ->type('email', $email)
                    // Mengisi input field 'password'
                    ->type('password', $password)
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('button[type="submit"]')
                    // Menunggu halaman berpindah ke rute '/mitra' (batas waktu 15 detik)
                    ->waitForLocation('/mitra', 15);

            // Step 2: Buka Halaman Pengaturan Inventaris
            // Mengunjungi halaman '/mitra/inventory'
            $browser->visit('/mitra/inventory')
                    // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/mitra/inventory'
                    ->assertPathIs('/mitra/inventory')
                    // Memastikan teks 'Manajemen Inventaris Surplus' terlihat pada halaman browser
                    ->assertSee('Manajemen Inventaris Surplus');

            // Klik 'Tambah Produk'
            // Mengeklik elemen 'elemen terkait' di halaman
            $browser->click('[dusk="tambah-produk-btn"]')
                    // Menunggu teks 'Tambah Produk Baru' muncul di layar (batas waktu 10 detik)
                    ->waitForText('Tambah Produk Baru', 10);

            // Isi data wajib produk
            // Mengisi input 'name' dengan nilai 'Roti Manis Enak'
            $browser->type('name', 'Roti Manis Enak')
                    // Mengisi input 'price' dengan nilai '15000'
                    ->type('price', '15000')
                    // Mengisi input 'stock' dengan nilai '15'
                    ->type('stock', '15');

            // Set waktu expired menggunakan JavaScript karena datetime-local
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("document.querySelector('input[name=\"expires_at\"]').value = '2026-06-15T12:00';");

            // Set 'Jam Mulai Pengambilan' dan 'Jam Akhir Pengambilan' yang valid (dalam jam operasional: 08:00 - 18:00)
            // Mengisi input 'pickup_start_time' dengan nilai '10:00'
            $browser->type('pickup_start_time', '10:00')
                    // Mengisi input 'pickup_end_time' dengan nilai '12:00'
                    ->type('pickup_end_time', '12:00');

            // Klik tombol 'Simpan Produk'
            // Mengeklik elemen 'elemen terkait' di halaman
            $browser->click('form[action*="inventory"] button[type="submit"]')
                    // Tunggu reload dan verifikasi pesan sukses
                    // Menunggu teks 'Produk berhasil ditambahkan.' muncul di layar (batas waktu 15 detik)
                    ->waitForText('Produk berhasil ditambahkan.', 15)
                    // Memastikan teks 'Produk berhasil ditambahkan.' terlihat pada halaman browser
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
            // Mengunjungi halaman '/login'
            $browser->visit('/login')
                    // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/login'
                    ->assertPathIs('/login')
                    // Memilih opsi 'mitra' pada dropdown 'user_type'
                    ->select('user_type', 'mitra')
                    // Mengisi input field 'email'
                    ->type('email', $email)
                    // Mengisi input field 'password'
                    ->type('password', $password)
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('button[type="submit"]')
                    // Menunggu halaman berpindah ke rute '/mitra' (batas waktu 15 detik)
                    ->waitForLocation('/mitra', 15);

            // Step 2: Buka Halaman Pengaturan Inventaris
            // Mengunjungi halaman '/mitra/inventory'
            $browser->visit('/mitra/inventory')
                    // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/mitra/inventory'
                    ->assertPathIs('/mitra/inventory')
                    // Memastikan teks 'Manajemen Inventaris Surplus' terlihat pada halaman browser
                    ->assertSee('Manajemen Inventaris Surplus');

            // Klik 'Tambah Produk'
            // Mengeklik elemen 'elemen terkait' di halaman
            $browser->click('[dusk="tambah-produk-btn"]')
                    // Menunggu teks 'Tambah Produk Baru' muncul di layar (batas waktu 10 detik)
                    ->waitForText('Tambah Produk Baru', 10);

            // Isi data wajib produk
            // Mengisi input 'name' dengan nilai 'Roti Gagal Pengambilan'
            $browser->type('name', 'Roti Gagal Pengambilan')
                    // Mengisi input 'price' dengan nilai '15000'
                    ->type('price', '15000')
                    // Mengisi input 'stock' dengan nilai '10'
                    ->type('stock', '10');

            // Set waktu expired
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("document.querySelector('input[name=\"expires_at\"]').value = '2026-06-15T12:00';");

            // Set 'Jam Mulai' dan 'Jam Akhir' Pengambilan di luar jam operasional (jam operasional: 08:00 - 18:00)
            // Mengisi input 'pickup_start_time' dengan nilai '19:00'
            $browser->type('pickup_start_time', '19:00')
                    // Mengisi input 'pickup_end_time' dengan nilai '20:00'
                    ->type('pickup_end_time', '20:00');

            // Klik tombol 'Simpan Produk'
            // Mengeklik elemen 'elemen terkait' di halaman
            $browser->click('form[action*="inventory"] button[type="submit"]')
                    // Tunggu pesan kesalahan validasi muncul
                    // Menunggu teks 'Jam mulai pengambilan harus di dalam jam operasional (08:00 - 18:00).' muncul di layar (batas waktu 15 detik)
                    ->waitForText('Jam mulai pengambilan harus di dalam jam operasional (08:00 - 18:00).', 15)
                    // Memastikan teks 'Jam mulai pengambilan harus di dalam jam operasional (08:00 - 18:00).' terlihat pada halaman browser
                    ->assertSee('Jam mulai pengambilan harus di dalam jam operasional (08:00 - 18:00).');
        });
    }
}
