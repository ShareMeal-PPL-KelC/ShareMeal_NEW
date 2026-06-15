<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * PBI-9: Konsumen Melihat Detail Produk
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
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
                    
                    // Berada di dashboard konsumen
                    // Mencari Card yang berisi teks "Roti Tawar Gandum" atau "Roti Gandum"
                    // Lalu klik tombol plus (+) di dalam card tersebut
                    // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
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
            
            // Menjeda eksekusi selama 2000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(2000);
            
            // Verifikasi alur (biasanya diarahkan ke cart atau muncul detail)
            // Karena instruksi hanya sampai klik plus, kita asumsikan berhasil jika tidak ada error JS
            // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/consumer/cart'
            $browser->assertPathIs('/consumer/cart');
        });
    }
}
