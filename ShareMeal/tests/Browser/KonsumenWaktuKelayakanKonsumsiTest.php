<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Order;
use Carbon\Carbon;

class KonsumenWaktuKelayakanKonsumsiTest extends DuskTestCase
{
    /**
     * TC.Cons.004 - PBI #13
     * Memeriksa waktu kelayakan konsumsi pada pesanan yang sudah expired
     */
    public function testWaktuKelayakanKonsumsi()
    {
        $this->browse(function (Browser $browser) {
            $kina = User::where('email', 'kina@gmail.com')->first();
            $browser->loginAs($kina)
                    ->visit('/consumer/history')
                    ->assertSee('Sisa waktu sebelum layak konsumsi:');
        });
    }
}
