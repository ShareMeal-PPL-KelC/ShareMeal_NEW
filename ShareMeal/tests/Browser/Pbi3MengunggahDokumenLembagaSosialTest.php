<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * PBI-3: Mengunggah Dokumen Lembaga Sosial
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
class Pbi3MengunggahDokumenLembagaSosialTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_lembaga_can_register_by_uploading_documents(): void
    {
        $legalitasFile = public_path('images/logo.png');
        $izinFile = public_path('images/logo2.png');
        $identitasFile = public_path('images/screen.png');

        $this->browse(function (Browser $browser) use ($legalitasFile, $izinFile, $identitasFile) {
            // Generate a unique email to avoid unique database constraints on repeated tests
            $email = 'lembagapbi3_' . time() . '_' . rand(1000, 9999) . '@example.com';

            // Mengunjungi halaman '/register'
            $browser->visit('/register')
                    // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/register'
                    ->assertPathIs('/register')
                    // Choose Lembaga role
                    // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
                    ->script("document.querySelector('input[value=\"lembaga\"]').click();");
            // Menjeda eksekusi selama 500 milidetik agar proses render/transisi halaman selesai
            $browser->pause(500)
                    // Klik 'Choose File' pada bagian 'Dokumen Legalitas Dasar'.
                    // Mengunggah berkas dokumen ke input file 'document_legalitas_lembaga'
                    ->attach('document_legalitas_lembaga', $legalitasFile)
                    // Klik 'Choose File' pada bagian 'Dokumen Izin Operasional & Registrasi Sosial'.
                    // Mengunggah berkas dokumen ke input file 'document_izin_lembaga'
                    ->attach('document_izin_lembaga', $izinFile)
                    // Klik 'Choose File' pada bagian 'Dokumen Identitas & Lokasi'.
                    // Mengunggah berkas dokumen ke input file 'document_identitas_lembaga'
                    ->attach('document_identitas_lembaga', $identitasFile)
                    // Fill required organization name for Lembaga registration
                    // Mengisi input 'organization_name' dengan nilai 'Lembaga PBI Tiga'
                    ->type('organization_name', 'Lembaga PBI Tiga')
                    // Lengkapi Nama Lengkap, Email, dan Kata Sandi.
                    // Mengisi input 'name' dengan nilai 'Lembaga Pengurus'
                    ->type('name', 'Lembaga Pengurus')
                    // Mengisi input field 'email'
                    ->type('email', $email)
                    // Mengisi input 'password' dengan nilai 'password123'
                    ->type('password', 'password123')
                    // Mengisi input 'password_confirmation' dengan nilai 'password123'
                    ->type('password_confirmation', 'password123')
                    // Check terms and conditions checkbox
                    // Mencentang (check) checkbox 'terms'
                    ->check('terms')
                    // Klik tombol Daftar/Daftar Sekarang.
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('button[type="submit"]')
                    // Wait for redirection to /login and check the path and success message
                    // Menunggu halaman berpindah ke rute '/login' (batas waktu 15 detik)
                    ->waitForLocation('/login', 15)
                    // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/login'
                    ->assertPathIs('/login')
                    // Memastikan teks 'Registrasi berhasil. Akun Anda sedang dalam proses verifikasi oleh admin.' terlihat pada halaman browser
                    ->assertSee('Registrasi berhasil. Akun Anda sedang dalam proses verifikasi oleh admin.');
        });
    }
}
