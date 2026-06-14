<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Donation;

class Pbi42DonationStatusUpdateTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_positive_mitra_berhasil_konfirmasi_penyerahan(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::factory()->create([
                'role' => 'mitra',
                'name' => 'Mitra Berkah PBI 42',
                'is_verified' => true,
            ]);

            UserProfile::create([
                'user_id' => $mitra->id,
                'business_name' => 'Mitra Berkah PBI 42',
                'business_type' => 'Restoran',
                'business_address' => 'Jl. Berkah No. 42',
                'business_contact' => '081234567894',
                'business_opening_hours' => '08:00 - 20:00',
                'business_description' => 'Toko makanan berkah PBI 42.',
            ]);

            $lembaga = User::factory()->create([
                'role' => 'lembaga',
                'name' => 'Yayasan Peduli PBI 42',
                'is_verified' => true,
            ]);

            UserProfile::create([
                'user_id' => $lembaga->id,
                'phone' => '089876543212',
                'address' => 'Jl. Peduli No. 42',
            ]);

            $donation = Donation::create([
                'mitra_id' => $mitra->id,
                'lembaga_id' => $lembaga->id,
                'title' => 'Nasi Bungkus PBI 42',
                'quantity' => 10,
                'unit' => 'porsi',
                'status' => 'claimed',
                'expires_at' => now()->addDays(2),
                'pickup_time' => now()->addHours(2),
                'pickup_start_time' => '08:00',
                'pickup_end_time' => '20:00',
                'claimed_at' => now(),
            ]);

            $browser->loginAs($mitra)
                    ->visit('/mitra/donations')
                    ->waitForText('Nasi Bungkus PBI 42')
                    ->assertSee('Nasi Bungkus PBI 42')
                    ->assertSee('TERKLAIM')
                    ->click('form[action*="prepare"] button[type="submit"]') // Click "Siapkan Donasi"
                    ->waitForText('Donasi berhasil ditandai sebagai siap diambil.')
                    ->assertSee('SIAP DIAMBIL')
                    ->click('form[action*="complete"] button[type="submit"]') // Click "Konfirmasi Penyerahan"
                    ->waitForText('Donasi dikonfirmasi telah diserahkan.')
                    ->assertSee('Donasi dikonfirmasi telah diserahkan.')
                    ->assertSee('SELESAI');

            // Assert status updated in database
            $donation->refresh();
            $this->assertEquals('completed', $donation->status);
            $this->assertEquals('delivered', $donation->tracking_status);
            $this->assertNotNull($donation->delivered_at);

            $browser->blank();
        });
    }

    public function test_negative_mitra_tidak_bisa_konfirmasi_donasi_pending(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::factory()->create([
                'role' => 'mitra',
                'name' => 'Mitra Berkah PBI 42 Neg',
                'is_verified' => true,
            ]);

            UserProfile::create([
                'user_id' => $mitra->id,
                'business_name' => 'Mitra Berkah PBI 42 Neg',
                'business_type' => 'Restoran',
                'business_address' => 'Jl. Berkah No. 43',
                'business_contact' => '081234567895',
                'business_opening_hours' => '08:00 - 20:00',
                'business_description' => 'Toko makanan berkah PBI 42 Neg.',
            ]);

            $donation = Donation::create([
                'mitra_id' => $mitra->id,
                'title' => 'Nasi Kotak PBI 42 Neg',
                'quantity' => 10,
                'unit' => 'porsi',
                'status' => 'pending',
                'expires_at' => now()->addDays(2),
                'pickup_start_time' => '08:00',
                'pickup_end_time' => '20:00',
            ]);

            $browser->loginAs($mitra)
                    ->visit('/mitra/donations')
                    ->waitForText('Nasi Kotak PBI 42 Neg')
                    ->assertSee('Nasi Kotak PBI 42 Neg')
                    ->assertSee('MENUNGGU KLAIM')
                    ->assertMissing('form[action*="complete"] button[type="submit"]') // "Konfirmasi Penyerahan" should not exist
                    ->assertPresent('form[action*="cancel"] button[type="submit"]'); // "Batalkan" should exist

            // Assert status remains pending in database
            $donation->refresh();
            $this->assertEquals('pending', $donation->status);

            $browser->blank();
        });
    }

    public function test_positive_lembaga_only_sees_wa_during_processing_and_all_actions_when_prepared(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::factory()->create([
                'role' => 'mitra',
                'name' => 'Mitra Berkah PBI 42 Lembaga',
                'is_verified' => true,
            ]);

            UserProfile::create([
                'user_id' => $mitra->id,
                'business_name' => 'Mitra Berkah PBI 42 Lembaga',
                'business_type' => 'Restoran',
                'business_address' => 'Jl. Berkah No. 45',
                'business_contact' => '081234567896',
                'business_opening_hours' => '08:00 - 20:00',
                'business_description' => 'Toko makanan berkah PBI 42.',
            ]);

            $lembaga = User::factory()->create([
                'role' => 'lembaga',
                'name' => 'Yayasan Peduli PBI 42 Lembaga',
                'is_verified' => true,
            ]);

            UserProfile::create([
                'user_id' => $lembaga->id,
                'phone' => '089876543213',
                'address' => 'Jl. Peduli No. 45',
            ]);

            // Create a claimed donation
            $donation = Donation::create([
                'mitra_id' => $mitra->id,
                'lembaga_id' => $lembaga->id,
                'title' => 'Nasi Bungkus Lembaga Test',
                'quantity' => 10,
                'unit' => 'porsi',
                'status' => 'claimed',
                'expires_at' => now()->addDays(2),
                'pickup_start_time' => '08:00',
                'pickup_end_time' => '20:00',
                'claimed_at' => now(),
            ]);

            // 1. Visit as Lembaga, verify DIPROSES tab has Hubungi WA, but no Rute Resto or Konfirmasi
            $browser->loginAs($lembaga)
                    ->visit('/lembaga/donations')
                    ->click('#tab-claimed') // Click DIPROSES tab button
                    ->pause(1000) // wait for tab switch animation
                    ->waitForText('Nasi Bungkus Lembaga Test')
                    ->assertSee('HUBUNGI WA')
                    ->assertDontSee('RUTE RESTO')
                    ->assertMissing('form[action*="complete"] button[type="submit"]');

            // 2. Change status to prepared
            $donation->update(['status' => 'prepared', 'tracking_status' => 'prepared']);

            // 3. Refresh and check SIAP DIAMBIL tab, verify all actions (Rute Resto, Hubungi WA, Konfirmasi) exist
            $browser->visit('/lembaga/donations')
                    ->click('#tab-prepared') // Click SIAP DIAMBIL tab button
                    ->pause(1000)
                    ->waitForText('Nasi Bungkus Lembaga Test')
                    ->assertSee('HUBUNGI WA')
                    ->assertSee('RUTE RESTO')
                    ->assertPresent('form[action*="complete"] button[type="submit"]')
                    ->click('form[action*="complete"] button[type="submit"]') // Complete donation
                    ->waitForText('Donasi dikonfirmasi sudah diterima.')
                    ->assertSee('Donasi dikonfirmasi sudah diterima.');

            $browser->blank();
        });
    }
}
