<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * PBI-34: Pengaturan Layanan Antar Makanan
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
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
            // Memaksimalkan ukuran jendela browser agar tampilan terlihat penuh
            $browser->maximize()
                    // Mengunjungi halaman '/'
                    ->visit('/')
                    // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
                    ->pause(1000)
            
                    // Klik tombol masuk di pojok kanan atas
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('a[href="' . route('login') . '"]')
                    // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
                    ->pause(1000)
                    
                    // Pilih tipe penggunannya Mitra
                    // Memilih opsi 'mitra' pada dropdown 'user_type'
                    ->select('user_type', 'mitra')
                    
                    // Masukkan alamat email
                    // Mengisi input 'email' dengan nilai 'mitra@example.com'
                    ->type('email', 'mitra@example.com')
                    
                    // Masukkan kata sandi
                    // Mengisi input 'password' dengan nilai 'password'
                    ->type('password', 'password')
                    
                    // Klik tombol masuk
                    // Menekan tombol dengan teks/properti 'tombol terkait'
                    ->press('button[type="submit"]')
                    // Menjeda eksekusi selama 2000 milidetik agar proses render/transisi halaman selesai
                    ->pause(2000)
                    
                    // Navigasi langsung ke Pengaturan Profil Usaha
                    // Mengunjungi halaman 'halaman terkait'
                    ->visit(route('mitra.profile'))
                    // Menjeda eksekusi selama 1500 milidetik agar proses render/transisi halaman selesai
                    ->pause(1500)
                    
                    // Scroll ke bagian bawah jika perlu
                    // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
                    ->script("window.scrollTo(0, document.body.scrollHeight);");
                    
            // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(1000)
                    // Klik toggle Jasa pengiriman (centang manual dengan javascript)
                    // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
                    ->script("document.querySelector('input[name=\"can_delivery\"][type=\"checkbox\"]').click();");
                    
            // Menjeda eksekusi selama 500 milidetik agar proses render/transisi halaman selesai
            $browser->pause(500)
                    // Klik tombol Simpan Profil Usaha
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('form[action="' . route('mitra.profile.update') . '"] button[type="submit"]')
                    // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
                    ->pause(1000)
                    
                    // (Opsional) Memastikan bahwa pengaturan berhasil disimpan
                    // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/mitra/profile-usaha'
                    ->assertPathIs('/mitra/profile-usaha');
        });
    }
}
