<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Donation;

class Pbi19WaktuKelayakanTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_positive_lembaga_melihat_waktu_layak_konsumsi(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::factory()->create([
                'role' => 'mitra',
                'name' => 'Resto Berkah PBI 19',
                'is_verified' => true,
            ]);

            UserProfile::create([
                'user_id' => $mitra->id,
                'business_name' => 'Resto Berkah PBI 19',
                'business_type' => 'Restoran',
                'business_address' => 'Jl. Berkah No. 19',
                'business_contact' => '081234567801',
                'business_opening_hours' => '08:00 - 20:00',
                'business_description' => 'Toko makanan berkah PBI 19.',
            ]);

            $lembaga = User::factory()->create([
                'role' => 'lembaga',
                'name' => 'Lembaga Peduli PBI 19',
                'is_verified' => true,
            ]);

            UserProfile::create([
                'user_id' => $lembaga->id,
                'phone' => '089876543217',
                'address' => 'Jl. Peduli No. 19',
            ]);

            // Create a pending donation that expires in 2 days
            $expiresAt = now()->addDays(2);
            $donation = Donation::create([
                'mitra_id' => $mitra->id,
                'title' => 'Nasi Kotak Layak Konsumsi PBI 19',
                'quantity' => 10,
                'unit' => 'box',
                'status' => 'pending',
                'expires_at' => $expiresAt,
                'pickup_start_time' => '08:00',
                'pickup_end_time' => '20:00',
            ]);

            $browser->loginAs($lembaga)
                    ->visit('/lembaga/donations')
                    ->waitForText('Nasi Kotak Layak Konsumsi PBI 19')
                    ->assertSee('Nasi Kotak Layak Konsumsi PBI 19')
                    // Verify uppercase due to CSS transform
                    ->assertSee('TERSEDIA SAMPAI:');

            $browser->blank();
        });
    }

    public function test_negative_lembaga_tidak_bisa_mengklaim_donasi_expired(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::factory()->create([
                'role' => 'mitra',
                'name' => 'Resto Berkah PBI 19 Neg',
                'is_verified' => true,
            ]);

            UserProfile::create([
                'user_id' => $mitra->id,
                'business_name' => 'Resto Berkah PBI 19 Neg',
                'business_type' => 'Restoran',
                'business_address' => 'Jl. Berkah No. 20',
                'business_contact' => '081234567802',
                'business_opening_hours' => '08:00 - 20:00',
                'business_description' => 'Toko makanan berkah PBI 19 Neg.',
            ]);

            $lembaga = User::factory()->create([
                'role' => 'lembaga',
                'name' => 'Lembaga Peduli PBI 19 Neg',
                'is_verified' => true,
            ]);

            UserProfile::create([
                'user_id' => $lembaga->id,
                'phone' => '089876543218',
                'address' => 'Jl. Peduli No. 20',
            ]);

            // Create a pending donation that already expired (yesterday)
            $expiresAt = now()->subDay();
            $donation = Donation::create([
                'mitra_id' => $mitra->id,
                'title' => 'Sayur Basi PBI 19 Neg',
                'quantity' => 10,
                'unit' => 'box',
                'status' => 'pending',
                'expires_at' => $expiresAt,
                'pickup_start_time' => '08:00',
                'pickup_end_time' => '20:00',
            ]);

            $browser->loginAs($lembaga)
                    ->visit('/lembaga/donations')
                    // Verify that the expired donation is not visible
                    ->assertDontSee('Sayur Basi PBI 19 Neg');

            $browser->blank();
        });
    }
}
