<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Donation;

/**
 * PBI-41: Pilih Jadwal Klaim
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
class Pbi41PilihJadwalKlaimTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_positive_lembaga_berhasil_pilih_jadwal_pengambilan(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::factory()->create([
                'role' => 'mitra',
                'name' => 'Mitra Berkah',
                'is_verified' => true,
            ]);

            UserProfile::create([
                'user_id' => $mitra->id,
                'business_name' => 'Mitra Berkah',
                'business_type' => 'Restoran',
                'business_address' => 'Jl. Berkah No. 12',
                'business_contact' => '081234567892',
                'business_opening_hours' => '08:00 - 20:00',
                'business_description' => 'Toko makanan berkah.',
            ]);

            $lembaga = User::factory()->create([
                'role' => 'lembaga',
                'name' => 'Yayasan Peduli',
                'is_verified' => true,
            ]);

            UserProfile::create([
                'user_id' => $lembaga->id,
                'phone' => '089876543210',
                'address' => 'Jl. Peduli No. 5',
            ]);

            $donation = Donation::create([
                'mitra_id' => $mitra->id,
                'title' => 'Nasi Bungkus PBI 41',
                'quantity' => 10,
                'unit' => 'porsi',
                'status' => 'pending',
                'expires_at' => now()->addDays(2),
                'pickup_start_time' => '08:00',
                'pickup_end_time' => '20:00',
            ]);

            $browser->loginAs($lembaga)
                    // Mengunjungi halaman '/lembaga/donations'
                    ->visit('/lembaga/donations')
                    // Menunggu teks '' muncul di layar (batas waktu standar detik)
                    ->waitForText('Nasi Bungkus PBI 41')
                    // Memastikan teks 'Nasi Bungkus PBI 41' terlihat pada halaman browser
                    ->assertSee('Nasi Bungkus PBI 41')
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('div[x-show="activeTab === \'available\'"] button.bg-purple-600') // Open Claim Modal
                    // Menunggu teks '' muncul di layar (batas waktu standar detik)
                    ->waitForText('Konfirmasi Klaim Donasi') // Wait for modal to open
                    ->assertPresent('input[name="pickup_time"]')
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('input[name="pickup_time"] + div') // Select the first available slot
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('form[action*="claim"] button[type="submit"]') // Submit claim
                    // Menunggu teks '' muncul di layar (batas waktu standar detik)
                    ->waitForText('Donasi berhasil diklaim')
                    // Memastikan teks 'Donasi berhasil diklaim' terlihat pada halaman browser
                    ->assertSee('Donasi berhasil diklaim');

            // Assert status updated in database
            $this->assertEquals('claimed', $donation->fresh()->status);
            $this->assertNotNull($donation->fresh()->pickup_time);

            $browser->blank();
        });
    }

    public function test_negative_lembaga_gagal_klaim_tanpa_pilih_jadwal(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::factory()->create([
                'role' => 'mitra',
                'name' => 'Mitra Berkah 2',
                'is_verified' => true,
            ]);

            UserProfile::create([
                'user_id' => $mitra->id,
                'business_name' => 'Mitra Berkah 2',
                'business_type' => 'Restoran',
                'business_address' => 'Jl. Berkah No. 13',
                'business_contact' => '081234567893',
                'business_opening_hours' => '08:00 - 20:00',
                'business_description' => 'Toko makanan berkah kedua.',
            ]);

            $lembaga = User::factory()->create([
                'role' => 'lembaga',
                'name' => 'Yayasan Peduli 2',
                'is_verified' => true,
            ]);

            UserProfile::create([
                'user_id' => $lembaga->id,
                'phone' => '089876543211',
                'address' => 'Jl. Peduli No. 6',
            ]);

            $donation = Donation::create([
                'mitra_id' => $mitra->id,
                'title' => 'Nasi Kotak PBI 41',
                'quantity' => 10,
                'unit' => 'porsi',
                'status' => 'pending',
                'expires_at' => now()->addDays(2),
                'pickup_start_time' => '08:00',
                'pickup_end_time' => '20:00',
            ]);

            $browser->loginAs($lembaga)
                    // Mengunjungi halaman '/lembaga/donations'
                    ->visit('/lembaga/donations')
                    // Menunggu teks '' muncul di layar (batas waktu standar detik)
                    ->waitForText('Nasi Kotak PBI 41')
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('div[x-show="activeTab === \'available\'"] button.bg-purple-600') // Open Claim Modal
                    // Menunggu teks '' muncul di layar (batas waktu standar detik)
                    ->waitForText('Konfirmasi Klaim Donasi') // Wait for modal to open
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('form[action*="claim"] button[type="submit"]'); // Try to submit without choosing a slot

            // Wait 2 seconds to make sure it didn't submit
            // Menjeda eksekusi selama 2000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(2000)
                    // Memastikan teks 'Konfirmasi Klaim Donasi' terlihat pada halaman browser
                    ->assertSee('Konfirmasi Klaim Donasi'); // We should still be on the modal

            // Assert status remains pending in database
            $this->assertEquals('pending', $donation->fresh()->status);
            $this->assertNull($donation->fresh()->pickup_time);

            $browser->blank();
        });
    }
}
