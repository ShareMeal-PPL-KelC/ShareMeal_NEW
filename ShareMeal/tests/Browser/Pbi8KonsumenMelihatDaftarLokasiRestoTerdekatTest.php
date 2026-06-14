<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi8KonsumenMelihatDaftarLokasiRestoTerdekatTest extends DuskTestCase
{
    use DatabaseMigrations;
    protected $seed = true;

    /**
     * Test PBI-08: Konsumen Melihat Daftar Lokasi Resto Terdekat.
     *
     * @return void
     */
    public function testMelihatDaftarRestoTerdekat()
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
                    
                    // Klik di sidebar tombol "Cari Makanan"
                    ->visit(route('consumer.search'))
                    ->pause(2000);
            
            // Klik tombol "GANTI LOKASI"
            $browser->script("
                let btns = Array.from(document.querySelectorAll('button'));
                let targetBtn = btns.find(b => b.textContent.trim().toUpperCase() === 'GANTI LOKASI');
                if(targetBtn) targetBtn.click();
                else throw new Error('Tombol Ganti Lokasi tidak ditemukan');
            ");
            $browser->pause(2000); // Tunggu modal map terbuka
            
            // Klik tombol "Konfirmasi Lokasi" di dalam modal
            $browser->script("
                let btns = Array.from(document.querySelectorAll('button'));
                let targetBtn = btns.find(b => b.textContent.trim().toUpperCase() === 'KONFIRMASI LOKASI');
                if(targetBtn) targetBtn.click();
                else throw new Error('Tombol Konfirmasi Lokasi tidak ditemukan');
            ");
            $browser->pause(2000);
            
            // Memastikan tetap di halaman search atau melihat hasil pencarian
            $browser->assertPathIs('/consumer/search');
        });
    }
}
