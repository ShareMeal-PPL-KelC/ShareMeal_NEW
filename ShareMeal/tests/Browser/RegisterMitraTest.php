<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

/**
 * PBI #2 TC.Mitra.001 - Menguji unggah dokumen mitra (Positive)
 * 
 * Skenario: Mitra mengunggah dokumen usaha yang valid
 * Expected: Akun terbuat. Data dan dokumen tersimpan, status akun 'Menunggu Verifikasi Admin'.
 */
class RegisterMitraTest extends DuskTestCase
{
    public function test_register_mitra_berhasil()
    {
        $uniqueEmail = 'mitrabaru_' . time() . '@example.com';
        $dummyFilePath = __DIR__ . '/dummy.pdf';

        $this->browse(function (Browser $browser) use ($uniqueEmail, $dummyFilePath) {
            $browser->driver->manage()->deleteAllCookies();
            $browser->visit('/register')
                    ->waitFor('input[name="user_type"]', 15) 
                    
                    // Pilih peran Mitra (defaultnya mitra, tapi pastikan diklik lagi jika perlu, atau asumsikan sudah mitra)
                    // Karena x-data default 'mitra', elementnya langsung muncul, tapi Alpine cloak bisa bikin telat
                    ->pause(1000)
                    ->radio('user_type', 'mitra')
                    ->pause(500)

                    // Step 1: Upload KTP
                    ->attach('document_ktp_mitra', $dummyFilePath)
                    
                    // Step 2: Upload SIUP & NIB
                    ->attach('document_siup_mitra', $dummyFilePath)
                    ->attach('document_nib_mitra', $dummyFilePath)
                    
                    // Step 3: Isi Biodata
                    ->type('name', 'Mitra Baru ShareMeal')
                    ->type('email', $uniqueEmail)
                    ->type('password', 'password123')
                    ->type('password_confirmation', 'password123')
                    
                    // Centang Syarat & Ketentuan
                    ->check('terms')
                    
                    // Step 4: Submit form
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
            'role' => 'mitra',
            'is_verified' => 0 // Status 'Menunggu Verifikasi Admin'
        ]);
        
        // Verifikasi kolom dokumen di tabel users
        $user = User::where('email', $uniqueEmail)->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->document_ktp);
        $this->assertNotNull($user->document_siup);
        $this->assertNotNull($user->document_nib);
    }
}
