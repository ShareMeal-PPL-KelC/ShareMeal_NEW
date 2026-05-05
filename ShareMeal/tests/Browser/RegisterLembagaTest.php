<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

/**
 * PBI #3 TC.LS.001 - Menguji unggah dokumen lembaga sosial (Positive)
 * 
 * Skenario: Lembaga sosial mengunggah dokumen legalitas yang valid
 * Expected: Pendaftaran sukses, data tersimpan, dan diarahkan ke halaman konfirmasi/login.
 */
class RegisterLembagaTest extends DuskTestCase
{
    public function test_register_lembaga_berhasil()
    {
        $uniqueEmail = 'lembagabaru_' . time() . '@example.com';
        $dummyFilePath = __DIR__ . '/dummy.pdf';

        $this->browse(function (Browser $browser) use ($uniqueEmail, $dummyFilePath) {
            $browser->driver->manage()->deleteAllCookies();
            $browser->visit('/register')
                    ->waitFor('input[name="user_type"]', 15) // Wait for form to be ready
                    
                    // Pilih peran Lembaga
                    ->pause(1000)
                    ->radio('user_type', 'lembaga')
                    ->pause(500) // Tunggu AlpineJS memunculkan form dokumen lembaga

                    // Step 1: Upload Dokumen Legalitas Dasar
                    ->attach('document_legalitas_lembaga', $dummyFilePath)
                    
                    // Step 2: Upload Dokumen Izin Operasional & Registrasi Sosial
                    ->attach('document_izin_lembaga', $dummyFilePath)
                    
                    // Step 3: Upload Dokumen Identitas & Lokasi
                    ->attach('document_identitas_lembaga', $dummyFilePath)
                    
                    // Step 4: Isi Biodata
                    ->type('name', 'Yayasan ShareMeal Indonesia')
                    ->type('email', $uniqueEmail)
                    ->type('password', 'password123')
                    ->type('password_confirmation', 'password123')
                    
                    // Centang Syarat & Ketentuan
                    ->check('terms')
                    
                    // Step 5: Submit form
                    ->press('Daftar Sekarang')
                    ->pause(2000) // Tunggu proses upload & register
                    
                    // Verifikasi bahwa user diarahkan dengan benar
                    ->assertPathIs('/login')
                    ->waitForText('Registrasi berhasil. Akun Anda sedang dalam proses verifikasi oleh admin.', 15)
                    ->assertSee('Registrasi berhasil. Akun Anda sedang dalam proses verifikasi oleh admin.');
        });

        // Verifikasi ke database
        $this->assertDatabaseHas('users', [
            'email' => $uniqueEmail,
            'role' => 'lembaga',
            'is_verified' => 0 // Status 'Menunggu Verifikasi Admin'
        ]);
        
        // Verifikasi kolom dokumen di tabel users
        $user = User::where('email', $uniqueEmail)->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->document_legalitas);
        $this->assertNotNull($user->document_izin);
        $this->assertNotNull($user->document_identitas);
    }
}
