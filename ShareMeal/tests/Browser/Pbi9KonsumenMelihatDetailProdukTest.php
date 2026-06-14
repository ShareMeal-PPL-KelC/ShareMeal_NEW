<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi9KonsumenMelihatDetailProdukTest extends DuskTestCase
{
    use DatabaseMigrations;
    protected $seed = true;

    /**
     * Test PBI-09: Konsumen Melihat Detail Produk.
     *
     * @return void
     */
    public function testMelihatDetailProduk()
    {
        $this->browse(function (Browser $browser) {
            // Dimulai dari homescreen awal
            $browser->maximize()
                    ->visit('/')
                    ->pause(1000)
            
                    // Klik tombol masuk di pojok kanan atas
                    ->click('a[href="' . route('login') . '"]')
                    ->pause(1000)
                    
                    // Pilih tipe penggunannya Konsumen
                    ->select('user_type', 'consumer')
                    
                    // Masukkan alamat email
                    ->type('email', 'budi@example.com')
                    
                    // Masukkan kata sandi
                    ->type('password', 'password')
                    
                    // Klik tombol masuk
                    ->press('button[type="submit"]')
                    ->pause(2000)
                    
                    // Berada di dashboard konsumen
                    // Mencari Card yang berisi teks "Roti Tawar Gandum" atau "Roti Gandum"
                    // Lalu klik tombol plus (+) di dalam card tersebut
                    ->script("
                        let cards = Array.from(document.querySelectorAll('.glass-card, .card, .product-card'));
                        let targetCard = cards.find(c => c.textContent.includes('Roti Tawar Gandum') || c.textContent.includes('Roti Gandum'));
                        if(targetCard) {
                            let plusBtn = targetCard.querySelector('button[type=\"submit\"], button .lucide-plus, button i[data-lucide=\"plus\"]')?.closest('button');
                            if(plusBtn) plusBtn.click();
                            else throw new Error('Tombol plus (+) tidak ditemukan di Card Roti Gandum');
                        } else {
                            throw new Error('Card Roti Gandum tidak ditemukan di halaman dashboard');
                        }
                    ");
            
            $browser->pause(2000);
            
            // Verifikasi alur (biasanya diarahkan ke cart atau muncul detail)
            // Karena instruksi hanya sampai klik plus, kita asumsikan berhasil jika tidak ada error JS
            $browser->assertPathIs('/consumer/cart');
        });
    }
}
