<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

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

            $browser->visit('/register')
                    ->assertPathIs('/register')
                    // Choose Lembaga role
                    ->script("document.querySelector('input[value=\"lembaga\"]').click();");
            $browser->pause(500)
                    // Klik 'Choose File' pada bagian 'Dokumen Legalitas Dasar'.
                    ->attach('document_legalitas_lembaga', $legalitasFile)
                    // Klik 'Choose File' pada bagian 'Dokumen Izin Operasional & Registrasi Sosial'.
                    ->attach('document_izin_lembaga', $izinFile)
                    // Klik 'Choose File' pada bagian 'Dokumen Identitas & Lokasi'.
                    ->attach('document_identitas_lembaga', $identitasFile)
                    // Fill required organization name for Lembaga registration
                    ->type('organization_name', 'Lembaga PBI Tiga')
                    // Lengkapi Nama Lengkap, Email, dan Kata Sandi.
                    ->type('name', 'Lembaga Pengurus')
                    ->type('email', $email)
                    ->type('password', 'password123')
                    ->type('password_confirmation', 'password123')
                    // Check terms and conditions checkbox
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
