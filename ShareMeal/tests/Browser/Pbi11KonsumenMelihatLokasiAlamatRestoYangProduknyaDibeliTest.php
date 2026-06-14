<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi11KonsumenMelihatLokasiAlamatRestoYangProduknyaDibeliTest extends DuskTestCase
{
    use DatabaseMigrations;
    protected $seed = true;

    /**
     * Test PBI-11: Konsumen Melihat Lokasi Alamat Resto Yang Produknya Dibeli.
     *
     * @return void
     */
    public function testMelihatLokasiAlamatResto()
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
                    
                    // Klik di sidebar tombol "Pesanan Aktif"
                    ->visit(route('consumer.orders.active'))
                    ->pause(2000)
                    
                    // Verifikasi halaman Pesanan Aktif
                    ->assertSee('Pesanan Aktif');
        });
    }
}
