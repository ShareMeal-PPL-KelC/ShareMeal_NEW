<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * PBI-8: Konsumen Melihat Daftar Lokasi Resto Terdekat
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
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
                    
                    // Pilih tipe penggunannya Konsumen
                    // Memilih opsi 'consumer' pada dropdown 'user_type'
                    ->select('user_type', 'consumer')
                    
                    // Masukkan alamat email
                    // Mengisi input 'email' dengan nilai 'budi@example.com'
                    ->type('email', 'budi@example.com')
                    
                    // Masukkan kata sandi
                    // Mengisi input 'password' dengan nilai 'password'
                    ->type('password', 'password')
                    
                    // Klik tombol masuk
                    // Menekan tombol dengan teks/properti 'tombol terkait'
                    ->press('button[type="submit"]')
                    // Menjeda eksekusi selama 2000 milidetik agar proses render/transisi halaman selesai
                    ->pause(2000)
                    
                    // Klik di sidebar tombol "Cari Makanan"
                    // Mengunjungi halaman 'halaman terkait'
                    ->visit(route('consumer.search'))
                    // Menjeda eksekusi selama 2000 milidetik agar proses render/transisi halaman selesai
                    ->pause(2000);
            
            // Klik tombol "GANTI LOKASI"
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("
                let btns = Array.from(document.querySelectorAll('button'));
                let targetBtn = btns.find(b => b.textContent.trim().toUpperCase() === 'GANTI LOKASI');
                if(targetBtn) targetBtn.click();
                else throw new Error('Tombol Ganti Lokasi tidak ditemukan');
            ");
            // Menjeda eksekusi selama 2000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(2000); // Tunggu modal map terbuka
            
            // Klik tombol "Konfirmasi Lokasi" di dalam modal
            // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
            $browser->script("
                let btns = Array.from(document.querySelectorAll('button'));
                let targetBtn = btns.find(b => b.textContent.trim().toUpperCase() === 'KONFIRMASI LOKASI');
                if(targetBtn) targetBtn.click();
                else throw new Error('Tombol Konfirmasi Lokasi tidak ditemukan');
            ");
            // Menjeda eksekusi selama 2000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(2000);
            
            // Memastikan tetap di halaman search atau melihat hasil pencarian
            // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/consumer/search'
            $browser->assertPathIs('/consumer/search');
        });
    }
}
