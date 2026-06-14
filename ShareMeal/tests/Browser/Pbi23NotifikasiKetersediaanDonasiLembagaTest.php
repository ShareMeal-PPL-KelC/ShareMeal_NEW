<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Donation;
use App\Models\UserProfile;
use App\Notifications\DonationAvailableNotification;
use Illuminate\Support\Facades\Notification;

class Pbi23NotifikasiKetersediaanDonasiLembagaTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * [PBI 23] Notifikasi Sistem - Lembaga
     * Deskripsi: Sebagai lembaga, saya ingin menerima notifikasi ketersediaan donasi agar dapat segera mengklaim donasi resto.
     */
    public function test_positive_lembaga_menerima_notifikasi_donasi_baru_tersedia(): void
    {
        $this->browse(function (Browser $browser) {
            // --- 1. SETUP DATA (Dibelakang Layar) ---
            $mitra = User::factory()->create([
                'role' => 'mitra',
                'name' => 'Resto Berkah',
                'is_verified' => true
            ]);

            $lembaga = User::factory()->create([
                'role' => 'lembaga',
                'name' => 'Yayasan Peduli',
                'is_verified' => true
            ]);

            UserProfile::create([
                'user_id' => $lembaga->id,
                'phone' => '0899999999',
                'address' => 'Jl. Kemanusiaan No. 1'
            ]);

            // --- 2. EXECUTION ---

            // Step 2: Simulasi Mitra menambahkan donasi baru
            $donation = Donation::create([
                'mitra_id' => $mitra->id,
                'title' => 'Nasi Kotak Ayam Bakar',
                'quantity' => 20,
                'unit' => 'box',
                'status' => 'pending',
                'expires_at' => now()->addHours(5),
                'pickup_start_time' => '10:00',
                'pickup_end_time' => '15:00'
            ]);

            // Trigger broadcast notification
            $lembagas = User::where('role', 'lembaga')->get();
            Notification::send($lembagas, new DonationAvailableNotification(
                $mitra->name, 
                $donation->title, 
                $donation->quantity . ' ' . $donation->unit
            ));

            // Bypass UI Login yang sering error karena animasi landing page
            $browser->loginAs($lembaga)
                    ->visitRoute('notifications.index')
                    
                    // Step 6: Tunggu elemen notifikasi muncul
                    ->waitForText('Donasi Baru Tersedia!', 15)
                    
                    // Step 7: Validasi Pesan
                    ->assertSee('Donasi Baru Tersedia!')
                    ->assertSee('Resto Berkah baru saja mendonasikan 20 box Nasi Kotak Ayam Bakar')
                    
                    // Step 8: Klik Notifikasi (Klaim) menggunakan dusk attribute
                    ->click('@notification-link') 
                    ->pause(2000)
                    ->assertPathIs('/lembaga/donations') 
                    ->assertSee('Nasi Kotak Ayam Bakar');
        });
    }

    public function test_negative_non_lembaga_tidak_menerima_notifikasi_ketersediaan_donasi(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::factory()->create([
                'role' => 'mitra',
                'name' => 'Resto Berkah',
                'is_verified' => true
            ]);

            $consumer = User::factory()->create([
                'role' => 'consumer',
                'name' => 'Budi Konsumen'
            ]);

            $donation = Donation::create([
                'mitra_id' => $mitra->id,
                'title' => 'Sayur Sop Donasi',
                'quantity' => 15,
                'unit' => 'box',
                'status' => 'pending',
                'expires_at' => now()->addHours(5),
                'pickup_start_time' => '10:00',
                'pickup_end_time' => '15:00'
            ]);

            // Kirim notifikasi ke semua Lembaga (seperti biasa)
            $lembagas = User::where('role', 'lembaga')->get();
            Notification::send($lembagas, new DonationAvailableNotification(
                $mitra->name, 
                $donation->title, 
                $donation->quantity . ' ' . $donation->unit
            ));

            // Login sebagai Consumer (non-lembaga) dan pastikan tidak ada notif donasi
            $browser->loginAs($consumer)
                    ->visitRoute('notifications.index')
                    ->assertDontSee('Donasi Baru Tersedia!')
                    ->assertDontSee('Resto Berkah baru saja mendonasikan');
        });
    }
}

