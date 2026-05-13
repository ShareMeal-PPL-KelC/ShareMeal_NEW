<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PBI20_NotifikasiTokoFavoritTest extends DuskTestCase
{
    /**
     * POSITIVE TEST: Konsumen berhasil melihat notifikasi flash sale dari toko favorit.
     */
    public function testNotifikasiTokoFavorit(): void
    {
        $this->browse(function (Browser $browser) {
            $email = 'test_fav_' . Str::random(4) . '@example.com';
            $password = 'password123';

            // 1. SETUP DATA (Sangat aman, akan dihapus otomatis)
            $consumer = User::create([
                'name' => 'Consumer Fav Test',
                'email' => $email,
                'password' => bcrypt($password),
                'role' => 'consumer',
            ]);

            // Simulasi notifikasi flash sale di database
            $consumer->notifications()->create([
                'id' => Str::uuid(),
                'type' => 'App\Notifications\FlashSaleNotification',
                'data' => [
                    'title' => 'Toko favorite anda mengeluarkan promo flash sale',
                    'message' => 'Toko Roti Makmur baru saja memulai flash sale!',
                    'store_name' => 'Toko Roti Makmur',
                    'item_name' => 'Roti Tawar',
                ],
            ]);

            try {
                // 2. JALANKAN TEST
                $browser->visit('/login')
                    ->select('user_type', 'consumer')
                    ->type('email', $consumer->email)
                    ->type('password', $password)
                    ->press('Masuk')
                    ->waitForLocation('/consumer', 15)
                    ->assertSee('Dashboard Konsumen')
                    ->pause(2000)
                    // Selector CSS langsung ke icon lonceng
                    ->click('nav button i[data-lucide="bell"], nav button svg.lucide-bell')
                    ->pause(2000)
                    ->assertSee('Toko favorite anda mengeluarkan promo flash sale');
            } finally {
                // 3. PEMBERSIHAN (Database tetap bersih)
                $consumer->delete();
            }
        });
    }
}
