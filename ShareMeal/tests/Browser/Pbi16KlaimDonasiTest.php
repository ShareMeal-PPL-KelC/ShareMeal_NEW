<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Donation;

class Pbi16KlaimDonasiTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_positive_lembaga_berhasil_klaim_donasi(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::factory()->create(['role' => 'mitra', 'name' => 'Resto Berkah']);
            $lembaga = User::factory()->create(['role' => 'lembaga']);
            $donation = Donation::create([
                'mitra_id' => $mitra->id,
                'title' => 'Nasi Kotak PBI 16',
                'quantity' => 10,
                'unit' => 'box',
                'status' => 'pending',
                'expires_at' => now()->addDay()
            ]);

            $browser->loginAs($lembaga)
                    ->visit('/lembaga/donations')
                    ->assertSee('Nasi Kotak PBI 16')
                    ->press('Klaim Donasi')
                    ->waitForText('Donasi berhasil diklaim')
                    ->assertSee('Diproses');
        });
    }

    public function test_negative_lembaga_gagal_klaim_karena_stok_habis(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::factory()->create(['role' => 'mitra', 'name' => 'Resto Berkah 2']);
            $lembaga = User::factory()->create(['role' => 'lembaga']);
            $lembaga2 = User::factory()->create(['role' => 'lembaga']);
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
                    ->visit('/lembaga/donations')
                    ->assertDontSee('Sate Ayam Habis');
        });
    }
}
