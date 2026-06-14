<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi28EditProfilKonsumenTest extends DuskTestCase
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

    public function test_positive_update_profile(): void
    {
        $email = 'consumer_pos_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $password = 'password123';
        
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Budi Lama',
                'password' => Hash::make($password),
                'role' => 'consumer',
                'status' => 'active',
                'is_verified' => true,
                'joined_at' => now(),
            ]
        );
        $user->profile()->create([
            'address' => 'Alamat Lama',
            'phone' => '081234567890',
        ]);

        $avatarFile = public_path('images/logo.png');

        $this->browse(function (Browser $browser) use ($email, $password, $avatarFile) {
            // Step 1: Login
            $browser->visit('/login')
                    ->assertPathIs('/login')
                    ->select('user_type', 'consumer')
                    ->type('email', $email)
                    ->type('password', $password)
                    ->click('button[type="submit"]')
                    ->waitForLocation('/consumer', 15);

            // Step 2: Buka halaman edit profil (edit.blade.php)
            $browser->visit('/profile')
                    ->assertPathIs('/profile')
                    ->assertSee('Profil Saya')
                    // Isi 'Nama Lengkap' dengan huruf dan spasi
                    ->type('name', 'Budi Santoso')
                    // Isi 'Alamat' dengan data yang valid
                    ->type('address', 'Jl. Sukajadi No. 123')
                    // Unggah file foto (JPG/PNG, < 2MB)
                    ->attach('avatar', $avatarFile);

            // Isi 'Nomor Telepon' baru (awalan 08/62, 10-15 digit)
            // Hilangkan atribut readonly terlebih dahulu melalui JavaScript agar bisa diketik
            $browser->script("document.getElementById('phone').removeAttribute('readonly');");
            $browser->type('phone', '089876543210');

            // Klik tombol 'Simpan' (Simpan Profil)
            $browser->click('form[action*="profile"] button[type="submit"]')
                    // Tunggu reload dan tunggu modal verifikasi OTP muncul
                    ->waitFor('input[name="otp"]', 15)
                    ->assertSee('Profil berhasil diperbarui. Masukkan kode OTP untuk memverifikasi nomor telepon baru.');

            // Dapatkan kode OTP dari display demo di halaman
            $otp = $browser->text('span[x-text="demoOtpVal"]');
            $this->assertNotEmpty($otp, "OTP demo tidak ditemukan di halaman.");

            // Masukkan kode OTP 6-digit (dari session/elemen demo) lalu klik 'Verifikasi'
            $browser->type('otp', $otp)
                    ->click('form[action*="verify"] button[type="submit"]')
                    // Tunggu modal sukses verifikasi muncul
                    ->waitForText('Verifikasi Berhasil!', 15)
                    // Beri jeda waktu agar reload halaman selesai
                    ->pause(3000)
                    // Pastikan nomor telepon pada form telah diperbarui ke nomor yang baru
                    ->assertPathIs('/profile');

            $this->assertEquals('089876543210', $browser->value('#phone'));

            // Buka halaman edit profil (edit.blade.php) kembali
            $browser->visit('/profile')
                    ->assertPathIs('/profile')
                    // Isi 'Nama Lengkap' dengan huruf dan spasi
                    ->type('name', 'Budi Santoso Baru')
                    ->click('form[action*="profile"] button[type="submit"]')
                    ->waitForText('Profil berhasil diperbarui.', 15)
                    ->assertSee('Profil berhasil diperbarui.');
        });
    }

    public function test_negative_update_profile(): void
    {
        $email = 'consumer_neg_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $password = 'password123';
        
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Budi Lama Neg',
                'password' => Hash::make($password),
                'role' => 'consumer',
                'status' => 'active',
                'is_verified' => true,
                'joined_at' => now(),
            ]
        );
        $user->profile()->create([
            'address' => 'Alamat Lama Neg',
            'phone' => '081234567890',
        ]);

        // Buat file PDF palsu sementara untuk pengujian
        $tempPdf = tempnam(sys_get_temp_dir(), 'test') . '.pdf';
        file_put_contents($tempPdf, 'fake pdf content');

        $this->browse(function (Browser $browser) use ($email, $password, $tempPdf) {
            // Step 1: Login
            $browser->visit('/login')
                    ->assertPathIs('/login')
                    ->select('user_type', 'consumer')
                    ->type('email', $email)
                    ->type('password', $password)
                    ->click('button[type="submit"]')
                    ->waitForLocation('/consumer', 15);

            // Step 2: Buka halaman edit profil
            $browser->visit('/profile')
                    ->assertPathIs('/profile')
                    // Isi 'Nama Lengkap' dengan campuran angka
                    ->type('name', 'Budi 123')
                    // Isi 'Nomor Telepon' dengan format salah (misal kurang dari 10 digit)
                    ->script("document.getElementById('phone').removeAttribute('readonly');");
            
            $browser->type('phone', '0812')
                    // Unggah file PDF
                    ->attach('avatar', $tempPdf)
                    // Klik tombol 'Simpan' (Simpan Profil)
                    ->click('form[action*="profile"] button[type="submit"]')
                    // Tunggu pesan kesalahan validasi muncul
                    ->waitForText('Nama hanya boleh berisi huruf dan spasi.', 15)
                    ->assertSee('Nama hanya boleh berisi huruf dan spasi.')
                    ->assertSee('Nomor telepon harus berupa angka valid dengan awalan 08 atau 62 dan panjang 10-15 digit.')
                    ->assertSee('Foto profil harus berupa gambar.');
        });

        // Hapus file PDF sementara
        @unlink($tempPdf);
    }
}
