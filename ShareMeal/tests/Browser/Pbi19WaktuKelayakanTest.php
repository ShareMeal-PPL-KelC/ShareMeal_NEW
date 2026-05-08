<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Donation;

class Pbi19WaktuKelayakanTest extends DuskTestCase
{
    public function test_positive_lembaga_melihat_waktu_layak_konsumsi(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::factory()->create(['role' => 'mitra', 'name' => 'Resto PBI 19']);
            $lembaga = User::factory()->create(['role' => 'lembaga']);
            $donation = Donation::create([
                'mitra_id' => $mitra->id,
                'title' => 'Roti Manis',
                'quantity' => 10,
                'unit' => 'pcs',
                'status' => 'pending',
                'expires_at' => now()->addHours(3)
            ]);

            $browser->loginAs($lembaga)
                    ->visit('/lembaga/donations')
                    ->waitForText('Roti Manis')
                    ->assertSee('Tersedia sampai:');
        });
    }

    public function test_negative_lembaga_tidak_bisa_mengklaim_donasi_expired(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::factory()->create(['role' => 'mitra', 'name' => 'Resto Expired']);
            $lembaga = User::factory()->create(['role' => 'lembaga']);
            $donation = Donation::create([
                'mitra_id' => $mitra->id,
                'title' => 'Roti Basi',
                'quantity' => 10,
                'unit' => 'pcs',
                'status' => 'pending',
                'expires_at' => now()->subHours(1)
            ]);

            $browser->loginAs($lembaga)
                    ->visit('/lembaga/donations')
                    ->assertDontSee('Roti Basi');
        });
    }
}
