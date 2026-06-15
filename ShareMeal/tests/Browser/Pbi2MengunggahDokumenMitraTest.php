<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * PBI-2: Mengunggah Dokumen Mitra
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
class Pbi2MengunggahDokumenMitraTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_mitra_can_register_by_uploading_documents(): void
    {
        $ktpFile = public_path('images/logo.png');
        $siupFile = public_path('images/logo2.png');
        $nibFile = public_path('images/screen.png');

        $this->browse(function (Browser $browser) use ($ktpFile, $siupFile, $nibFile) {
            // Generate a unique email to avoid unique database constraints on repeated tests
            $email = 'mitrapbi2_' . time() . '_' . rand(1000, 9999) . '@example.com';

            // Mengunjungi halaman '/register'
            $browser->visit('/register')
                    // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/register'
                    ->assertPathIs('/register')
                    // Klik 'Choose File' pada Foto KTP Pemilik.
                    // Mengunggah berkas dokumen ke input file 'document_ktp_mitra'
                    ->attach('document_ktp_mitra', $ktpFile)
                    // Klik 'Choose File' pada SIUP/TDP.
                    // Mengunggah berkas dokumen ke input file 'document_siup_mitra'
                    ->attach('document_siup_mitra', $siupFile)
                    // Klik 'Choose File' pada NIB.
                    // Mengunggah berkas dokumen ke input file 'document_nib_mitra'
                    ->attach('document_nib_mitra', $nibFile)
                    // Fill required store / organization name for Mitra registration
                    // Mengisi input 'organization_name' dengan nilai 'Mitra PBI Dua'
                    ->type('organization_name', 'Mitra PBI Dua')
                    // Isi Nama Lengkap (Nama Pemilik), Email, dan Kata Sandi.
                    // Mengisi input 'name' dengan nilai 'Mitra Pemilik'
                    ->type('name', 'Mitra Pemilik')
                    // Mengisi input field 'email'
                    ->type('email', $email)
                    // Mengisi input 'password' dengan nilai 'password123'
                    ->type('password', 'password123')
                    // Mengisi input 'password_confirmation' dengan nilai 'password123'
                    ->type('password_confirmation', 'password123')
                    // Check terms and conditions checkbox (required to submit form)
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
