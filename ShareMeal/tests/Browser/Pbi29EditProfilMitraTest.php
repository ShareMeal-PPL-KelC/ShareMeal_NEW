<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi29EditProfilMitraTest extends DuskTestCase
{
    public function createApplication()
    {
        $basePath = dirname(__DIR__, 2);
        $sqliteDatabase = str_replace('\\', '/', $basePath . '/database/testing.sqlite');

        if (! file_exists($sqliteDatabase)) {
            @touch($sqliteDatabase);
        }

        // Dynamically override the .env file on disk. This is necessary because the web server
        // (started separately, e.g. via php artisan serve) reads the .env file on every request,
        // and we need it to use the SQLite configuration and file-based sessions so it does not
        // attempt to connect to a MySQL database that might not be running or accessible.
        $envPath = $basePath . '/.env';
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            
            $replacements = [
                'DB_CONNECTION' => 'sqlite',
                'DB_DATABASE' => '"' . $sqliteDatabase . '"',
                'SESSION_DRIVER' => 'file',
                'CACHE_STORE' => 'array',
                'DB_HOST' => '',
                'DB_PORT' => '',
                'DB_USERNAME' => '',
                'DB_PASSWORD' => '',
            ];

            foreach ($replacements as $key => $value) {
                if (preg_match("/^{$key}=/m", $envContent)) {
                    $envContent = preg_replace("/^{$key}=.*$/m", "{$key}={$value}", $envContent);
                } else {
                    $envContent .= "\n{$key}={$value}";
                }
            }
            
            file_put_contents($envPath, $envContent);
        }

        $envOverrides = [
            'APP_ENV' => 'testing',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => $sqliteDatabase,
            'CACHE_DRIVER' => 'array',
            'SESSION_DRIVER' => 'file',
            'QUEUE_CONNECTION' => 'sync',
        ];

        foreach ($envOverrides as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        $app = parent::createApplication();

        $app['config']->set('app.env', 'testing');
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', $sqliteDatabase);
        $app['config']->set('cache.default', 'array');
        $app['config']->set('session.driver', 'file');
        $app['config']->set('queue.default', 'sync');

        try {
            $app->make(\Illuminate\Contracts\Console\Kernel::class)->call('migrate', ['--force' => true]);
        } catch (\Throwable $e) {
            // Ignore if migration fails
        }

        return $app;
    }

    public function test_positive_update_mitra_profile(): void
    {
        $email = 'mitra_pos_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $password = 'password123';
        
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Mitra Owner',
                'password' => Hash::make($password),
                'role' => 'mitra',
                'status' => 'active',
                'is_verified' => true,
                'joined_at' => now(),
            ]
        );
        $user->profile()->create([
            'business_name' => 'Toko Lama',
            'business_type' => 'Bakery',
            'business_address' => 'Alamat Lama',
            'business_contact' => '081234567890',
            'business_opening_hours' => '08:00 - 20:00',
            'opening_hours' => '08:00 - 20:00',
            'business_description' => 'Deskripsi Lama',
            'description' => 'Deskripsi Lama',
        ]);

        $avatarFile = public_path('images/logo.png');

        $this->browse(function (Browser $browser) use ($email, $password, $avatarFile) {
            // Step 1: Login
            $browser->visit('/login')
                    ->assertPathIs('/login')
                    ->select('user_type', 'mitra')
                    ->type('email', $email)
                    ->type('password', $password)
                    ->click('button[type="submit"]')
                    ->waitForLocation('/mitra', 15);

            // Step 2: Buka Halaman pengaturan Profil Usaha
            $browser->visit('/mitra/profile-usaha')
                    ->assertPathIs('/mitra/profile-usaha')
                    ->assertSee('Profil Usaha')
                    // Isi kolom wajib: Nama Usaha, Kategori, Alamat, Deskripsi
                    ->type('business_name', 'Toko Roti Baru')
                    ->type('business_type', 'Restoran')
                    ->type('business_address', 'Jl. Baru No. 100')
                    ->type('business_description', 'Menyediakan roti hangat dan lezat setiap hari.')
                    // Set 'Jam Buka' dan 'Jam Tutup' dengan format yang benar (Tutup > Buka)
                    ->type('opening_start', '08:00')
                    ->type('opening_end', '20:00');

            // Aktifkan 'Jasa Pengiriman' dengan mengeset checked dan men-dispatch event change
            $browser->script('const el = document.querySelector(\'input[name="can_delivery"][type="checkbox"]\'); if (el) { el.checked = true; el.dispatchEvent(new Event("change", { bubbles: true })); if (window.Alpine && window.Alpine.$data) { try { window.Alpine.$data(el).canDelivery = true; } catch(e) {} } }');

            // Isi 'Biaya Ongkir' dan 'Limit Slot Harian'
            $browser->type('delivery_fee', '15000')
                    ->type('delivery_slot_limit', '25')
                    // Unggah 'Gambar Toko' (JPG/PNG, < 2MB)
                    ->attach('store_image', $avatarFile);

            // Isi 'Kontak Usaha' dengan nomor baru
            // Hilangkan readonly agar bisa diketik
            $browser->script("document.getElementById('business_contact').removeAttribute('readonly');");
            $browser->type('business_contact', '089876543210');

            // Klik tombol 'Simpan' / 'Update' (Simpan Profil Usaha)
            $browser->click('form[action*="profile-usaha"] button[type="submit"]')
                    // Tunggu reload dan tunggu modal verifikasi OTP muncul
                    ->waitFor('input[name="otp"]', 15)
                    ->assertSee('Profil usaha berhasil diperbarui. Masukkan kode OTP untuk memverifikasi kontak usaha baru.');

            // Dapatkan kode OTP dari display demo di halaman
            $otp = $browser->text('span[x-text="demoOtpVal"]');
            $this->assertNotEmpty($otp, "OTP demo tidak ditemukan di halaman.");

            // Masukkan kode OTP 6-digit (dari session/elemen demo) lalu klik 'Verifikasi'
            $browser->type('otp', $otp)
                    ->click('form[action*="contact/verify"] button[type="submit"]')
                    // Tunggu modal sukses verifikasi muncul
                    ->waitForText('Verifikasi Berhasil!', 15)
                    // Beri jeda waktu agar reload halaman selesai
                    ->pause(3000)
                    // Pastikan nomor telepon pada form telah diperbarui ke nomor yang baru
                    ->assertPathIs('/mitra/profile-usaha');

            $this->assertEquals('089876543210', $browser->value('#business_contact'));
        });
    }

    public function test_negative_update_mitra_profile(): void
    {
        $email = 'mitra_neg_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $password = 'password123';
        
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Mitra Owner Neg',
                'password' => Hash::make($password),
                'role' => 'mitra',
                'status' => 'active',
                'is_verified' => true,
                'joined_at' => now(),
            ]
        );
        $user->profile()->create([
            'business_name' => 'Toko Lama Neg',
            'business_type' => 'Bakery',
            'business_address' => 'Alamat Lama Neg',
            'business_contact' => '081234567890',
            'business_opening_hours' => '08:00 - 20:00',
            'opening_hours' => '08:00 - 20:00',
            'business_description' => 'Deskripsi Lama Neg',
            'description' => 'Deskripsi Lama Neg',
            'can_delivery' => false,
            'delivery_fee' => 0,
            'delivery_slot_limit' => 10,
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

            // Step 2: Buka Halaman pengaturan Profil Usaha
            $browser->visit('/mitra/profile-usaha')
                    ->assertPathIs('/mitra/profile-usaha')
                    ->assertSee('Profil Usaha')
                    // Set 'Jam Tutup' lebih awal daripada 'Jam Buka' (Buka 10:00, Tutup 08:00)
                    ->type('opening_start', '10:00')
                    ->type('opening_end', '08:00');

            // Aktifkan opsi 'Jasa Pengiriman' dengan mengklik div toggle-nya
            $browser->click('input[name="can_delivery"][type="checkbox"] + div')
                    ->pause(500); // Tunggu AlpineJS transition selesai

            // Kosongkan 'Biaya Ongkir' dan 'Limit Slot'
            $browser->type('delivery_fee', '')
                    ->type('delivery_slot_limit', '');

            // Klik tombol 'Simpan' / 'Update' (Simpan Profil Usaha)
            $browser->click('form[action*="profile-usaha"] button[type="submit"]')
                    // Tunggu pesan kesalahan validasi muncul
                    ->waitForText('Jam tutup harus lebih akhir dari jam buka.', 15)
                    ->assertSee('Jam tutup harus lebih akhir dari jam buka.');

            // Beri jeda sebentar agar AlpineJS pada halaman baru terinisialisasi
            $browser->pause(1000);

            // Aktifkan kembali Jasa Pengiriman dengan mengklik div toggle-nya di halaman baru
            $browser->click('input[name="can_delivery"][type="checkbox"] + div')
                    ->pause(500); // Tunggu transisi AlpineJS

            // Sekarang error-nya harus terlihat!
            $browser->assertSee('Biaya ongkir wajib diisi jika jasa kirim diaktifkan.')
                    ->assertSee('Limit slot wajib diisi jika jasa kirim diaktifkan.');
        });
    }
}
