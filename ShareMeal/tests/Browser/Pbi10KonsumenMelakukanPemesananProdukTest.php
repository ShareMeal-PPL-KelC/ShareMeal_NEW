<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * PBI-10: Konsumen Melakukan Pemesanan Produk
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
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
                    // Mencari Card yang berisi teks "Roti Gandum"
                    // Lalu klik tombol plus (+) untuk tambah ke keranjang
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
            $browser->pause(2000)
                    // Berada di halaman cart, klik LANJUTKAN KE CHECKOUT
                    // Mengunjungi halaman 'halaman terkait'
                    ->visit(route('consumer.checkout'))
                    // Menjeda eksekusi selama 2000 milidetik agar proses render/transisi halaman selesai
                    ->pause(2000)
                    
                    // Scroll down agar tombol Konfirmasi & Bayar terlihat
                    // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
                    ->script("window.scrollTo(0, document.body.scrollHeight);");
            
            // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(1000)
                    // Klik tombol KONFIRMASI & BAYAR
                    // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
                    ->script("
                        let confirmBtn = document.querySelector('button[x-on\\\\:click*=\"handleConfirmPayment\"], button[\\\\@click*=\"handleConfirmPayment\"]');
                        if(confirmBtn) confirmBtn.click();
                        else throw new Error('Tombol Konfirmasi & Bayar tidak ditemukan');
                    ");
            
            // Tunggu hingga pemesanan berhasil
            // Menunggu teks 'Pemesanan Berhasil' muncul di layar (batas waktu 15 detik)
            $browser->waitForText('Pemesanan Berhasil', 15);
        });
    }
}
