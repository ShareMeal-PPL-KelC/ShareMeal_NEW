<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

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

            $browser->visit('/register')
                    ->assertPathIs('/register')
                    // Klik 'Choose File' pada Foto KTP Pemilik.
                    ->attach('document_ktp_mitra', $ktpFile)
                    // Klik 'Choose File' pada SIUP/TDP.
                    ->attach('document_siup_mitra', $siupFile)
                    // Klik 'Choose File' pada NIB.
                    ->attach('document_nib_mitra', $nibFile)
                    // Fill required store / organization name for Mitra registration
                    ->type('organization_name', 'Mitra PBI Dua')
                    // Isi Nama Lengkap (Nama Pemilik), Email, dan Kata Sandi.
                    ->type('name', 'Mitra Pemilik')
                    ->type('email', $email)
                    ->type('password', 'password123')
                    ->type('password_confirmation', 'password123')
                    // Check terms and conditions checkbox (required to submit form)
                    ->check('terms')
                    // Klik tombol Daftar/Daftar Sekarang.
                    ->click('button[type="submit"]')
                    // Wait for redirection to /login and check the path and success message
                    ->waitForLocation('/login', 15)
                    ->assertPathIs('/login')
                    ->assertSee('Registrasi berhasil. Akun Anda sedang dalam proses verifikasi oleh admin.');
        });
    }
}
