<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\Order;

class Pbi36UpdateStatusPengantaranNegatif extends DuskTestCase
{
    /**
     * Test PBI-36: Update Status Pengantaran Negatif (Pembatalan) oleh Mitra.
     *
     * @return void
     */
    public function testUpdateStatusPengantaranNegatif()
    {
        $this->browse(function (Browser $browser) {
            // Dimulai dari homescreen awal
            $browser->maximize()
                    ->visit('/')
                    ->pause(1000)
            
                    // Klik tombol masuk di pojok kanan atas
                    ->click('a[href="' . route('login') . '"]')
                    ->pause(1000)
                    
                    // Pilih tipe penggunannya Mitra
                    ->select('user_type', 'mitra')
                    
                    // Alamat emailnya: mitra@example.com
                    ->type('email', 'mitra@example.com')
                    
                    // Kata sandinya: password
                    ->type('password', 'password')
                    
                    // Klik tombol masuk
                    ->press('button[type="submit"]')
                    ->pause(2000)
                    
                    // Klik di side bar ada tombol "Pesanan"
                    ->script("document.querySelector('a[href*=\"/mitra/orders\"]').click();");
            
            $browser->pause(2000)
                    ->assertPathIs('/mitra/orders')
                    ->assertSee('Daftar Pesanan Masuk')

                    // Klik tombol "Batalkan"
                    ->waitForText('Batalkan')
                    ->script("
                        let btn = Array.from(document.querySelectorAll('button')).find(b => b.textContent.trim() === 'Batalkan');
                        if(btn) btn.click();
                        else throw new Error('Tombol Batalkan tidak ditemukan');
                    ");
                    
            $browser->pause(1000)
                    
                    // Tulis stok makanan habis di field alasan pembatalan
                    ->waitFor('textarea')
                    ->type('textarea', 'stok makanan habis')
                    
                    // Pencet BATALKAN PESANAN
                    ->press('BATALKAN PESANAN')
                    ->pause(2000)
                    
                    // Selesai
                    ->assertPathIs('/mitra/orders');
        });
    }
}
