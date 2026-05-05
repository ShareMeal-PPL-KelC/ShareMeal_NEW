<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

/**
 * PBI #4 TC.Admin.002 - Menguji penolakan dokumen oleh admin (Negative)
 * 
 * Skenario: Admin menolak dokumen pendaftaran ('Tolak')
 */
class AdminRejectVerificationTest extends DuskTestCase
{
    public function test_admin_menolak_dokumen()
    {
        // Setup: Buat user unverified (Mitra) secara langsung di database untuk di-test
        $uniqueId = time();
        $unverifiedEmail = 'mitra_pending_reject_' . $uniqueId . '@example.com';
        $unverifiedName = 'Mitra Pending Reject Test ' . $uniqueId;
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
                    ->visit('/admin/verification')
                    ->waitForText('Verifikasi Dokumen', 15)
                    ->assertSee($unverifiedUser->name) // Memastikan user yang pending muncul
                    
                    // Step 1: Klik 'Preview Dokumen'
                    ->click('#btn-preview-' . $unverifiedUser->id . '-ktp')
                    ->pause(1000) // Tunggu modal terbuka
                    ->assertVisible('.fixed.inset-0.z-\\[100\\]') // Cek apakah modal preview terlihat
                    
                    // Tutup modal preview (klik tombol close X)
                    ->click('.fixed.inset-0.z-\\[100\\] button')
                    ->pause(500)
                    
                    // Step 2: Klik tombol 'Tolak'
                    ->click('#btn-reject-' . $unverifiedUser->id)
                    ->pause(1000) // Tunggu modal reject terbuka
                    ->assertVisible('textarea[name="reason"]') // Cek form penolakan
                    
                    // Isi alasan penolakan
                    ->type('reason', 'Dokumen blur dan tidak bisa dibaca.')
                    
                    // Klik konfirmasi tolak
                    ->click('#btn-confirm-reject')
                    ->pause(2000) // Tunggu proses
                    
                    ->assertPathIs('/admin/verification');
        });

        // Verifikasi database bahwa user tersebut ditolak
        $this->assertDatabaseHas('users', [
            'id' => $unverifiedUser->id,
            'is_verified' => 0,
            'verification_rejection_reason' => 'Dokumen blur dan tidak bisa dibaca.'
        ]);
    }
}
