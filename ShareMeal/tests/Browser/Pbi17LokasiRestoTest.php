<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Donation;

class Pbi17LokasiRestoTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_positive_lembaga_melihat_lokasi_resto_pada_donasi_diklaim(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::factory()->create(['role' => 'mitra', 'name' => 'Resto Mitra PBI 17']);
            $lembaga = User::factory()->create(['role' => 'lembaga']);
            $donation = Donation::create([
                'mitra_id' => $mitra->id,
                'lembaga_id' => $lembaga->id,
                'title' => 'Roti Bakar',
                'quantity' => 5,
                'unit' => 'pcs',
                'status' => 'claimed',
                'claimed_at' => now(),
                'expires_at' => now()->addDay()
            ]);

            $browser->loginAs($lembaga)
                    ->visit('/lembaga/donations?tab=claimed')
                    ->waitForText('Roti Bakar')
                    ->assertSee('Lokasi Resto');
        });
    }

    public function test_negative_lembaga_tidak_melihat_lokasi_pada_donasi_belum_diklaim(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::factory()->create(['role' => 'mitra', 'name' => 'Resto Mitra 2 PBI 17']);
            $lembaga = User::factory()->create(['role' => 'lembaga']);
            $donation = Donation::create([
                'mitra_id' => $mitra->id,
                'title' => 'Roti Bakar Tersedia',
                'quantity' => 5,
                'unit' => 'pcs',
                'status' => 'pending',
                'expires_at' => now()->addDay()
            ]);

            $browser->loginAs($lembaga)
                    ->visit('/lembaga/donations?tab=available')
                    ->waitForText('Roti Bakar Tersedia')
                    ->assertDontSee('Lokasi Resto');
        });
    }
}
