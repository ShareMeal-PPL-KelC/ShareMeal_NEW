<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi34PengaturanLayananAntarMakananTest extends DuskTestCase
{
    use DatabaseMigrations;
    protected $seed = true;

    /**
     * Test PBI-34: Pengaturan Layanan Antar Makanan oleh Mitra.
     *
     * @return void
     */
    public function testPengaturanLayananAntarMakanan()
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
                    
                    // Masukkan alamat email
                    ->type('email', 'mitra@example.com')
                    
                    // Masukkan kata sandi
                    ->type('password', 'password')
                    
                    // Klik tombol masuk
                    ->press('button[type="submit"]')
                    ->pause(2000)
                    
                    // Navigasi langsung ke Pengaturan Profil Usaha
                    ->visit(route('mitra.profile'))
                    ->pause(1500)
                    
                    // Scroll ke bagian bawah jika perlu
                    ->script("window.scrollTo(0, document.body.scrollHeight);");
                    
            $browser->pause(1000)
                    // Klik toggle Jasa pengiriman (centang manual dengan javascript)
                    ->script("document.querySelector('input[name=\"can_delivery\"][type=\"checkbox\"]').click();");
                    
            $browser->pause(500)
                    // Klik tombol Simpan Profil Usaha
                    ->click('form[action="' . route('mitra.profile.update') . '"] button[type="submit"]')
                    ->pause(1000)
                    
                    // (Opsional) Memastikan bahwa pengaturan berhasil disimpan
                    ->assertPathIs('/mitra/profile-usaha');
        });
    }
}
