<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Donation;

/**
 * PBI-18: Informasi Lembaga
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
class Pbi18InformasiLembagaTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_positive_mitra_melihat_informasi_lembaga_pada_donasi_diklaim(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::factory()->create([
                'role' => 'mitra',
                'name' => 'Resto Berkah PBI 18',
                'is_verified' => true,
            ]);

            UserProfile::create([
                'user_id' => $mitra->id,
                'business_name' => 'Resto Berkah PBI 18',
                'business_type' => 'Restoran',
                'business_address' => 'Jl. Berkah No. 18',
                'business_contact' => '081234567899',
                'business_opening_hours' => '08:00 - 20:00',
                'business_description' => 'Toko makanan berkah PBI 18.',
            ]);

            $lembaga = User::factory()->create([
                'role' => 'lembaga',
                'name' => 'Yayasan PBI 18',
                'email' => 'yayasan_pbi18@example.com',
                'phone' => '089876543216',
                'is_verified' => true,
            ]);

            UserProfile::create([
                'user_id' => $lembaga->id,
                'phone' => '089876543216',
                'address' => 'Jl. Peduli No. 18',
            ]);

            // Create a claimed donation
            $donation = Donation::create([
                'mitra_id' => $mitra->id,
                'lembaga_id' => $lembaga->id,
                'title' => 'Nasi Bungkus PBI 18',
                'quantity' => 10,
                'unit' => 'porsi',
                'status' => 'claimed',
                'expires_at' => now()->addDays(2),
                'pickup_start_time' => '08:00',
                'pickup_end_time' => '20:00',
                'claimed_at' => now(),
            ]);

            $browser->loginAs($mitra)
                    // Mengunjungi halaman '/mitra/donations'
                    ->visit('/mitra/donations')
                    // Menunggu teks '' muncul di layar (batas waktu standar detik)
                    ->waitForText('Nasi Bungkus PBI 18')
                    // Memastikan teks 'Nasi Bungkus PBI 18' terlihat pada halaman browser
                    ->assertSee('Nasi Bungkus PBI 18')
                    // Memastikan teks 'Yayasan PBI 18' terlihat pada halaman browser
                    ->assertSee('Yayasan PBI 18')
                    // Memastikan teks 'yayasan_pbi18@example.com' terlihat pada halaman browser
                    ->assertSee('yayasan_pbi18@example.com')
                    // Memastikan teks '089876543216' terlihat pada halaman browser
                    ->assertSee('089876543216');

            $browser->blank();
        });
    }

    public function test_negative_mitra_melihat_informasi_kosong_pada_donasi_belum_diklaim(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::factory()->create([
                'role' => 'mitra',
                'name' => 'Resto Berkah PBI 18 Neg',
                'is_verified' => true,
            ]);

            UserProfile::create([
                'user_id' => $mitra->id,
                'business_name' => 'Resto Berkah PBI 18 Neg',
                'business_type' => 'Restoran',
                'business_address' => 'Jl. Berkah No. 19',
                'business_contact' => '081234567800',
                'business_opening_hours' => '08:00 - 20:00',
                'business_description' => 'Toko makanan berkah PBI 18 Neg.',
            ]);

            // Create a pending donation
            $donation = Donation::create([
                'mitra_id' => $mitra->id,
                'title' => 'Nasi Kotak PBI 18 Neg',
                'quantity' => 10,
                'unit' => 'porsi',
                'status' => 'pending',
                'expires_at' => now()->addDays(2),
                'pickup_start_time' => '08:00',
                'pickup_end_time' => '20:00',
            ]);

            $browser->loginAs($mitra)
                    // Mengunjungi halaman '/mitra/donations'
                    ->visit('/mitra/donations')
                    // Menunggu teks '' muncul di layar (batas waktu standar detik)
                    ->waitForText('Nasi Kotak PBI 18 Neg')
                    // Memastikan teks 'Belum ada lembaga yang mengklaim donasi ini' terlihat pada halaman browser
                    ->assertSee('Belum ada lembaga yang mengklaim donasi ini');

            $browser->blank();
        });
    }
}
