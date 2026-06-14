<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Donation;

class Pbi17LokasiRestoTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_positive_lembaga_melihat_lokasi_resto_pada_donasi_diklaim(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::factory()->create([
                'role' => 'mitra',
                'name' => 'Resto Berkah PBI 17',
                'is_verified' => true,
            ]);

            UserProfile::create([
                'user_id' => $mitra->id,
                'business_name' => 'Resto Berkah PBI 17',
                'business_type' => 'Restoran',
                'business_address' => 'Jl. Berkah No. 17',
                'business_contact' => '081234567897',
                'business_opening_hours' => '08:00 - 20:00',
                'business_description' => 'Toko makanan berkah PBI 17.',
            ]);

            $lembaga = User::factory()->create([
                'role' => 'lembaga',
                'name' => 'Lembaga Peduli PBI 17',
                'is_verified' => true,
            ]);

            UserProfile::create([
                'user_id' => $lembaga->id,
                'phone' => '089876543214',
                'address' => 'Jl. Peduli No. 17',
            ]);

            // Create a claimed donation
            $donation = Donation::create([
                'mitra_id' => $mitra->id,
                'lembaga_id' => $lembaga->id,
                'title' => 'Nasi Kotak PBI 17 Claimed',
                'quantity' => 10,
                'unit' => 'box',
                'status' => 'claimed',
                'expires_at' => now()->addDay(),
                'pickup_start_time' => '08:00',
                'pickup_end_time' => '20:00',
                'claimed_at' => now(),
            ]);

            // Visit as Lembaga and check DIPROSES tab
            $browser->loginAs($lembaga)
                    ->visit('/lembaga/donations')
                    ->click('#tab-claimed')
                    ->pause(1000)
                    ->waitForText('Nasi Kotak PBI 17 Claimed')
                    // Verify that the restaurant's address is visible (uppercase due to CSS text-transform)
                    ->assertSee('JL. BERKAH NO. 17');

            $browser->blank();
        });
    }

    public function test_negative_lembaga_tidak_melihat_lokasi_pada_donasi_belum_diklaim(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::factory()->create([
                'role' => 'mitra',
                'name' => 'Resto Berkah PBI 17 Neg',
                'is_verified' => true,
            ]);

            UserProfile::create([
                'user_id' => $mitra->id,
                'business_name' => 'Resto Berkah PBI 17 Neg',
                'business_type' => 'Restoran',
                'business_address' => 'Jl. Berkah No. 18',
                'business_contact' => '081234567898',
                'business_opening_hours' => '08:00 - 20:00',
                'business_description' => 'Toko makanan berkah PBI 17 Neg.',
            ]);

            $lembaga = User::factory()->create([
                'role' => 'lembaga',
                'name' => 'Lembaga Peduli PBI 17 Neg',
                'is_verified' => true,
            ]);

            UserProfile::create([
                'user_id' => $lembaga->id,
                'phone' => '089876543215',
                'address' => 'Jl. Peduli No. 18',
            ]);

            // Create a pending donation
            $donation = Donation::create([
                'mitra_id' => $mitra->id,
                'title' => 'Nasi Kotak PBI 17 Available',
                'quantity' => 10,
                'unit' => 'box',
                'status' => 'pending',
                'expires_at' => now()->addDay(),
                'pickup_start_time' => '08:00',
                'pickup_end_time' => '20:00',
            ]);

            $browser->loginAs($lembaga)
                    ->visit('/lembaga/donations')
                    ->waitForText('Nasi Kotak PBI 17 Available')
                    // Verify that "Rute Resto" (or specific button) is missing on available donations
                    ->assertMissing('a[href*="maps.google.com"]');

            $browser->blank();
        });
    }
}
