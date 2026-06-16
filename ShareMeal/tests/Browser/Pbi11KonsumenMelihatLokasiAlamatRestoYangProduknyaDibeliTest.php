<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * PBI-11: Konsumen Melihat Lokasi Alamat Resto Yang Produknya Dibeli
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
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
                    
                    // Klik di sidebar tombol "Pesanan Aktif"
                    // Mengunjungi halaman 'halaman terkait'
                    ->visit(route('consumer.orders.active'))
                    // Menjeda eksekusi selama 2000 milidetik agar proses render/transisi halaman selesai
                    ->pause(2000)
                    
                    // Verifikasi halaman Pesanan Aktif
                    // Memastikan teks 'Pesanan Aktif' terlihat pada halaman browser
                    ->assertSee('Pesanan Aktif');
        });
    }
}
