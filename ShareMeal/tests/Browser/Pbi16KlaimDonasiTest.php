<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Donation;

/**
 * PBI-16: Klaim Donasi
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
class Pbi16KlaimDonasiTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_positive_lembaga_berhasil_klaim_donasi(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::factory()->create([
                'role' => 'mitra',
                'name' => 'Resto Berkah',
                'is_verified' => true,
            ]);

            UserProfile::create([
                'user_id' => $mitra->id,
                'business_name' => 'Resto Berkah',
                'business_type' => 'Restoran',
                'business_address' => 'Jl. Berkah No. 12',
                'business_contact' => '081234567892',
                'business_opening_hours' => '08:00 - 20:00',
                'business_description' => 'Toko makanan berkah.',
            ]);

            $lembaga = User::factory()->create([
                'role' => 'lembaga',
                'name' => 'Lembaga Peduli',
                'is_verified' => true,
            ]);

            UserProfile::create([
                'user_id' => $lembaga->id,
                'phone' => '089876543210',
                'address' => 'Jl. Peduli No. 5',
            ]);

            $donation = Donation::create([
                'mitra_id' => $mitra->id,
                'title' => 'Nasi Kotak PBI 16',
                'quantity' => 10,
                'unit' => 'box',
                'status' => 'pending',
                'expires_at' => now()->addDay(),
                'pickup_start_time' => '08:00',
                'pickup_end_time' => '20:00',
            ]);

            $browser->loginAs($lembaga)
                    // Mengunjungi halaman '/lembaga/donations'
                    ->visit('/lembaga/donations')
                    // Menunggu teks '' muncul di layar (batas waktu standar detik)
                    ->waitForText('Nasi Kotak PBI 16')
                    // Memastikan teks 'Nasi Kotak PBI 16' terlihat pada halaman browser
                    ->assertSee('Nasi Kotak PBI 16')
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
            
            $browser->blank();
        });
    }

    public function test_negative_lembaga_gagal_klaim_karena_stok_habis(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::factory()->create(['role' => 'mitra', 'name' => 'Resto Berkah 2', 'is_verified' => true]);
            
            UserProfile::create([
                'user_id' => $mitra->id,
                'business_name' => 'Resto Berkah 2',
                'business_type' => 'Restoran',
                'business_address' => 'Jl. Berkah No. 13',
                'business_contact' => '081234567893',
                'business_opening_hours' => '08:00 - 20:00',
                'business_description' => 'Toko makanan berkah kedua.',
            ]);

            $lembaga = User::factory()->create(['role' => 'lembaga', 'is_verified' => true]);
            UserProfile::create([
                'user_id' => $lembaga->id,
                'phone' => '089876543211',
                'address' => 'Jl. Peduli No. 6',
            ]);

            $lembaga2 = User::factory()->create(['role' => 'lembaga', 'is_verified' => true]);
            UserProfile::create([
                'user_id' => $lembaga2->id,
                'phone' => '089876543212',
                'address' => 'Jl. Peduli No. 7',
            ]);

            $donation = Donation::create([
                'mitra_id' => $mitra->id,
                'lembaga_id' => $lembaga2->id,
                'title' => 'Sate Ayam Habis',
                'quantity' => 10,
                'unit' => 'box',
                'status' => 'claimed',
                'claimed_at' => now(),
                'expires_at' => now()->addDay()
            ]);

            $browser->loginAs($lembaga)
                    // Mengunjungi halaman '/lembaga/donations'
                    ->visit('/lembaga/donations')
                    // Memastikan teks 'Sate Ayam Habis' TIDAK muncul pada halaman browser
                    ->assertDontSee('Sate Ayam Habis');

            $browser->blank();
        });
    }
}
