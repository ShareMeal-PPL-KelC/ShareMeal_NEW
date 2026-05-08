<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Donation;

class Pbi18InfoLembagaTest extends DuskTestCase
{
    public function test_positive_mitra_melihat_informasi_lembaga_pada_donasi_diklaim(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::factory()->create(['role' => 'mitra', 'name' => 'Resto PBI 18']);
            $lembaga = User::factory()->create(['role' => 'lembaga', 'name' => 'Yayasan PBI 18']);
            $donation = Donation::create([
                'mitra_id' => $mitra->id,
                'lembaga_id' => $lembaga->id,
                'title' => 'Sayur Sop',
                'quantity' => 20,
                'unit' => 'porsi',
                'status' => 'claimed',
                'claimed_at' => now(),
                'expires_at' => now()->addDay()
            ]);

            $browser->loginAs($mitra)
                    ->visit('/mitra/donations')
                    ->waitForText('Sayur Sop')
                    ->assertSee('Yayasan PBI 18')
                    ->assertSee('INFORMASI LEMBAGA');
        });
    }

    public function test_negative_mitra_melihat_informasi_kosong_pada_donasi_belum_diklaim(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::factory()->create(['role' => 'mitra', 'name' => 'Resto 2 PBI 18']);
            $donation = Donation::create([
                'mitra_id' => $mitra->id,
                'title' => 'Sayur Lodeh',
                'quantity' => 15,
                'unit' => 'porsi',
                'status' => 'pending',
                'expires_at' => now()->addDay()
            ]);

            $browser->loginAs($mitra)
                    ->visit('/mitra/donations')
                    ->waitForText('Sayur Lodeh')
                    ->assertSee('Belum ada lembaga yang mengklaim donasi ini.');
        });
    }
}
