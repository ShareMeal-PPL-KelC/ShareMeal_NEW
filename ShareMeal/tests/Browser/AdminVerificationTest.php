<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

/**
 * PBI #4 TC.Admin.001 - Menguji verifikasi dan persetujuan dokumen oleh admin (Positive)
 * 
 * Skenario: Admin memeriksa dokumen ('Preview Dokumen') dan menyetujui pendaftaran ('Setujui')
 */
class AdminVerificationTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_admin_menyetujui_dokumen()
    {
        // Setup: Buat user unverified (Mitra) secara langsung di database untuk di-test
        $uniqueId = time();
        $unverifiedEmail = 'mitra_pending_' . $uniqueId . '@example.com';
        $unverifiedName = 'Mitra Pending Test ' . $uniqueId;
        $unverifiedUser = User::create([
            'name' => $unverifiedName,
            'email' => $unverifiedEmail,
            'password' => bcrypt('password'),
            'role' => 'mitra',
            'is_verified' => false,
            'document_ktp' => 'documents/dummy.pdf',
            'document_siup' => 'documents/dummy.pdf',
            'document_nib' => 'documents/dummy.pdf',
            'status' => 'active'
        ]);

        $this->browse(function (Browser $browser) use ($unverifiedUser) {
            // Ensure admin user exists
            User::firstOrCreate(
                ['email' => 'admin@sharemeal.id'],
                [
                    'name' => 'Admin ShareMeal',
                    'password' => bcrypt('password123'),
                    'role' => 'admin',
                    'is_verified' => true
                ]
            );

            $browser->driver->manage()->deleteAllCookies();
            
            // Login sebagai admin
            $browser->visit('/login')
                    ->waitFor('select[name="user_type"]', 15)
                    ->select('user_type', 'admin')
                    ->pause(500)
                    ->type('email', 'admin@sharemeal.id')
                    ->type('password', 'password123')
                    ->press('Masuk')
                    ->waitForLocation('/admin', 15)
                    
                    // Step 1: Buka menu 'Verifikasi Pengguna'
                    // Bisa klik dari navigasi samping atau langsung visit route
                    ->visit('/admin/verification')
                    ->waitForText('Verifikasi Dokumen', 15)
                    ->assertSee($unverifiedUser->name) // Memastikan user yang pending muncul
                    
                    // Step 2: Klik 'Preview Dokumen'
                    ->click('#btn-preview-' . $unverifiedUser->id . '-ktp')
                    ->pause(1000) // Tunggu modal terbuka
                    ->assertVisible('.fixed.inset-0.z-\\[100\\]') // Cek apakah modal preview terlihat
                    
                    // Tutup modal preview (klik tombol close X)
                    ->click('.fixed.inset-0.z-\\[100\\] button')
                    ->pause(500)
                    
                    // Step 3 & 4: Klik tombol 'Setujui'
                    ->click('#btn-approve-' . $unverifiedUser->id)
                    ->pause(2000) // Tunggu proses persetujuan dan refresh halaman
                    ->assertPathIs('/admin/verification')
                    
                    // Verifikasi bahwa user tersebut sudah hilang dari daftar pending
                    ->assertDontSee($unverifiedUser->email)
                    ->assertDontSee($unverifiedUser->name);
        });

        // Verifikasi database bahwa user tersebut sudah terverifikasi
        $this->assertDatabaseHas('users', [
            'id' => $unverifiedUser->id,
            'is_verified' => 1
        ]);
    }

}
