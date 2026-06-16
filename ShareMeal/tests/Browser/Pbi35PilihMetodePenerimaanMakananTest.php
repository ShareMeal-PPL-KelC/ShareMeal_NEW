<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * PBI-35: Pilih Metode Penerimaan Makanan
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
class Pbi35PilihMetodePenerimaanMakananTest extends DuskTestCase
{
    use DatabaseMigrations;
    protected $seed = true;

    /**
     * Test PBI-35: Pemilihan Metode Penerimaan Makanan.
     *
     * @return void
     */
    public function testPilihMetodePenerimaanMakanan()
    {
        $this->browse(function (Browser $browser) {
            // Dimulai dari homescreen awal
            // Memaksimalkan ukuran jendela browser agar tampilan terlihat penuh
            $browser->maximize()
                    // Mengunjungi halaman '/'
                    ->visit('/')
                    // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
                    ->pause(1000)
            
                    // Klik tombol masuk
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
                    
                    // Berada di dashboard consumer, klik tanda plus untuk tambah ke keranjang
                    // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
                    ->script("
                        let btn = document.querySelector('form[action*=\"consumer/cart/add\"] button[type=\"submit\"]');
                        if(btn) btn.click();
                        else throw new Error('Add to cart button not found');
                    ");
            
            // Menjeda eksekusi selama 1500 milidetik agar proses render/transisi halaman selesai
            $browser->pause(1500)
                    // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/consumer/cart'
                    ->assertPathIs('/consumer/cart')
                    // Berada di halaman cart, klik Lanjutkan ke Checkout
                    // Mengunjungi halaman 'halaman terkait'
                    ->visit(route('consumer.checkout'))
                    // Menjeda eksekusi selama 1500 milidetik agar proses render/transisi halaman selesai
                    ->pause(1500)
                    
                    // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/consumer/checkout'
                    ->assertPathIs('/consumer/checkout')
                    // Berada di halaman checkout, pilih Kirim ke Lokasi (delivery)
                    // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
                    ->script("
                        let radio = document.querySelector('input[name=\"receiving_method_radio\"][value=\"delivery\"]');
                        if(radio) {
                            radio.click();
                            radio.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                        else throw new Error('Delivery radio not found');
                    ");
                    
            // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(1000)
                    // Pilih waktu pengantaran pertama yang tersedia
                    // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
                    ->script("
                        let slotBtn = document.querySelector('button[\\\\@click*=\"deliveryTimeSlot\"]:not([disabled])');
                        if(slotBtn) {
                            slotBtn.click();
                        } else {
                            console.warn('No delivery slot button found, maybe not needed or all full');
                        }
                    ");
            // Menjeda eksekusi selama 500 milidetik agar proses render/transisi halaman selesai
            $browser->pause(500)
                    
                    // Scroll down agar tombol Konfirmasi & Bayar terlihat
                    // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
                    ->script("window.scrollTo(0, document.body.scrollHeight);");
                    
            // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
            $browser->pause(1000)
            
                    // Klik tombol Konfirmasi & Bayar
                    // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
                    ->script("
                        let confirmBtn = document.querySelector('button[x-on\\\\:click*=\"handleConfirmPayment\"], button[\\\\@click*=\"handleConfirmPayment\"]');
                        if(confirmBtn) confirmBtn.click();
                        else throw new Error('Confirm button not found');
                    ");
                    
            // Menunggu teks 'Pemesanan Berhasil' muncul di layar (batas waktu 10 detik)
            $browser->waitForText('Pemesanan Berhasil', 10);
        });
    }
}
