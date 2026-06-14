<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi10KonsumenMelakukanPemesananProdukTest extends DuskTestCase
{
    use DatabaseMigrations;
    protected $seed = true;

    /**
     * Test PBI-10: Konsumen Melakukan Pemesanan Produk.
     *
     * @return void
     */
    public function testMelakukanPemesananProduk()
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
                    // Mencari Card yang berisi teks "Roti Gandum"
                    // Lalu klik tombol plus (+) untuk tambah ke keranjang
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
            
            $browser->pause(2000)
                    // Berada di halaman cart, klik LANJUTKAN KE CHECKOUT
                    ->visit(route('consumer.checkout'))
                    ->pause(2000)
                    
                    // Scroll down agar tombol Konfirmasi & Bayar terlihat
                    ->script("window.scrollTo(0, document.body.scrollHeight);");
            
            $browser->pause(1000)
                    // Klik tombol KONFIRMASI & BAYAR
                    ->script("
                        let confirmBtn = document.querySelector('button[x-on\\\\:click*=\"handleConfirmPayment\"], button[\\\\@click*=\"handleConfirmPayment\"]');
                        if(confirmBtn) confirmBtn.click();
                        else throw new Error('Tombol Konfirmasi & Bayar tidak ditemukan');
                    ");
            
            // Tunggu hingga pemesanan berhasil
            $browser->waitForText('Pemesanan Berhasil', 15);
        });
    }
}
