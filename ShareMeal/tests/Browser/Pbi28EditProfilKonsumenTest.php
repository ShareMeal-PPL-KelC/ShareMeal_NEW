<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * PBI-28: Edit Profil Konsumen
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
class Pbi28EditProfilKonsumenTest extends DuskTestCase
{
    use DatabaseMigrations;

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
            // Mengunjungi halaman '/login'
            $browser->visit('/login')
                    // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/login'
                    ->assertPathIs('/login')
                    // Memilih opsi 'consumer' pada dropdown 'user_type'
                    ->select('user_type', 'consumer')
                    // Mengisi input field 'email'
                    ->type('email', $email)
                    // Mengisi input field 'password'
                    ->type('password', $password)
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('button[type="submit"]')
                    // Menunggu halaman berpindah ke rute '/consumer' (batas waktu 15 detik)
                    ->waitForLocation('/consumer', 15);

            // Step 2: Buka halaman edit profil (edit.blade.php)
            // Mengunjungi halaman '/profile'
            $browser->visit('/profile')
                    // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/profile'
                    ->assertPathIs('/profile')
                    // Memastikan teks 'Profil Saya' terlihat pada halaman browser
                    ->assertSee('Profil Saya')
                    // Isi 'Nama Lengkap' dengan huruf dan spasi
                    // Mengisi input 'name' dengan nilai 'Budi Santoso'
                    ->type('name', 'Budi Santoso')
                    // Isi 'Alamat' dengan data yang valid
                    // Mengisi input 'address' dengan nilai 'Jl. Sukajadi No. 123'
                    ->type('address', 'Jl. Sukajadi No. 123')
                    // Unggah file foto (JPG/PNG, < 2MB)
                    // Mengunggah berkas dokumen ke input file 'avatar'
                    ->attach('avatar', $avatarFile);

            // Isi 'Nomor Telepon' baru (awalan 08/62, 10-15 digit)
            // Hilangkan atribut readonly terlebih dahulu melalui JavaScript agar bisa diketik
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("document.getElementById('phone').removeAttribute('readonly');");
            // Mengisi input 'phone' dengan nilai '089876543210'
            $browser->type('phone', '089876543210');

            // Klik tombol 'Simpan' (Simpan Profil)
            // Mengeklik elemen 'elemen terkait' di halaman
            $browser->click('form[action*="profile"] button[type="submit"]')
                    // Tunggu reload dan tunggu modal verifikasi OTP muncul
                    // Menunggu elemen 'elemen terkait' muncul di layar (batas waktu standar detik)
                    ->waitFor('input[name="otp"]', 15)
                    // Memastikan teks 'Profil berhasil diperbarui. Masukkan kode OTP untuk memverifikasi nomor telepon baru.' terlihat pada halaman browser
                    ->assertSee('Profil berhasil diperbarui. Masukkan kode OTP untuk memverifikasi nomor telepon baru.');

            // Dapatkan kode OTP dari display demo di halaman
            $otp = $browser->text('span[x-text="demoOtpVal"]');
            $this->assertNotEmpty($otp, "OTP demo tidak ditemukan di halaman.");

            // Masukkan kode OTP 6-digit (dari session/elemen demo) lalu klik 'Verifikasi'
            // Mengisi input field 'otp'
            $browser->type('otp', $otp)
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('form[action*="verify"] button[type="submit"]')
                    // Tunggu modal sukses verifikasi muncul
                    // Menunggu teks 'Verifikasi Berhasil!' muncul di layar (batas waktu 15 detik)
                    ->waitForText('Verifikasi Berhasil!', 15)
                    // Beri jeda waktu agar reload halaman selesai
                    // Menjeda eksekusi selama 3000 milidetik agar proses render/transisi halaman selesai
                    ->pause(3000)
                    // Pastikan nomor telepon pada form telah diperbarui ke nomor yang baru
                    // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/profile'
                    ->assertPathIs('/profile');

            $this->assertEquals('089876543210', $browser->value('#phone'));

            // Buka halaman edit profil (edit.blade.php) kembali
            // Mengunjungi halaman '/profile'
            $browser->visit('/profile')
                    // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/profile'
                    ->assertPathIs('/profile')
                    // Isi 'Nama Lengkap' dengan huruf dan spasi
                    // Mengisi input 'name' dengan nilai 'Budi Santoso Baru'
                    ->type('name', 'Budi Santoso Baru')
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('form[action*="profile"] button[type="submit"]')
                    // Menunggu teks 'Profil berhasil diperbarui.' muncul di layar (batas waktu 15 detik)
                    ->waitForText('Profil berhasil diperbarui.', 15)
                    // Memastikan teks 'Profil berhasil diperbarui.' terlihat pada halaman browser
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
            // Mengunjungi halaman '/login'
            $browser->visit('/login')
                    // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/login'
                    ->assertPathIs('/login')
                    // Memilih opsi 'consumer' pada dropdown 'user_type'
                    ->select('user_type', 'consumer')
                    // Mengisi input field 'email'
                    ->type('email', $email)
                    // Mengisi input field 'password'
                    ->type('password', $password)
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('button[type="submit"]')
                    // Menunggu halaman berpindah ke rute '/consumer' (batas waktu 15 detik)
                    ->waitForLocation('/consumer', 15);

            // Step 2: Buka halaman edit profil
            // Mengunjungi halaman '/profile'
            $browser->visit('/profile')
                    // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/profile'
                    ->assertPathIs('/profile')
                    // Isi 'Nama Lengkap' dengan campuran angka
                    // Mengisi input 'name' dengan nilai 'Budi 123'
                    ->type('name', 'Budi 123')
                    // Isi 'Nomor Telepon' dengan format salah (misal kurang dari 10 digit)
                    // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
                    ->script("document.getElementById('phone').removeAttribute('readonly');");
            
            // Mengisi input 'phone' dengan nilai '0812'
            $browser->type('phone', '0812')
                    // Unggah file PDF
                    // Mengunggah berkas dokumen ke input file 'avatar'
                    ->attach('avatar', $tempPdf)
                    // Klik tombol 'Simpan' (Simpan Profil)
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('form[action*="profile"] button[type="submit"]')
                    // Tunggu pesan kesalahan validasi muncul
                    // Menunggu teks 'Nama hanya boleh berisi huruf dan spasi.' muncul di layar (batas waktu 15 detik)
                    ->waitForText('Nama hanya boleh berisi huruf dan spasi.', 15)
                    // Memastikan teks 'Nama hanya boleh berisi huruf dan spasi.' terlihat pada halaman browser
                    ->assertSee('Nama hanya boleh berisi huruf dan spasi.')
                    // Memastikan teks 'Nomor telepon harus berupa angka valid dengan awalan 08 atau 62 dan panjang 10-15 digit.' terlihat pada halaman browser
                    ->assertSee('Nomor telepon harus berupa angka valid dengan awalan 08 atau 62 dan panjang 10-15 digit.')
                    // Memastikan teks 'Foto profil harus berupa gambar.' terlihat pada halaman browser
                    ->assertSee('Foto profil harus berupa gambar.');
        });

        // Hapus file PDF sementara
        @unlink($tempPdf);
    }
}
