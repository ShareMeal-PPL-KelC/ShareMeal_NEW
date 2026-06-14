<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

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
            $browser->maximize()
                    ->visit('/')
                    ->pause(1000)
            
                    // Klik tombol masuk
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
                    
                    // Berada di dashboard consumer, klik tanda plus untuk tambah ke keranjang
                    ->script("
                        let btn = document.querySelector('form[action*=\"consumer/cart/add\"] button[type=\"submit\"]');
                        if(btn) btn.click();
                        else throw new Error('Add to cart button not found');
                    ");
            
            $browser->pause(1500)
                    ->assertPathIs('/consumer/cart')
                    // Berada di halaman cart, klik Lanjutkan ke Checkout
                    ->visit(route('consumer.checkout'))
                    ->pause(1500)
                    
                    ->assertPathIs('/consumer/checkout')
                    // Berada di halaman checkout, pilih Kirim ke Lokasi (delivery)
                    ->script("
                        let radio = document.querySelector('input[name=\"receiving_method_radio\"][value=\"delivery\"]');
                        if(radio) {
                            radio.click();
                            radio.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                        else throw new Error('Delivery radio not found');
                    ");
                    
            $browser->pause(1000)
                    // Pilih waktu pengantaran pertama yang tersedia
                    ->script("
                        let slotBtn = document.querySelector('button[\\\\@click*=\"deliveryTimeSlot\"]:not([disabled])');
                        if(slotBtn) {
                            slotBtn.click();
                        } else {
                            console.warn('No delivery slot button found, maybe not needed or all full');
                        }
                    ");
            $browser->pause(500)
                    
                    // Scroll down agar tombol Konfirmasi & Bayar terlihat
                    ->script("window.scrollTo(0, document.body.scrollHeight);");
                    
            $browser->pause(1000)
            
                    // Klik tombol Konfirmasi & Bayar
                    ->script("
                        let confirmBtn = document.querySelector('button[x-on\\\\:click*=\"handleConfirmPayment\"], button[\\\\@click*=\"handleConfirmPayment\"]');
                        if(confirmBtn) confirmBtn.click();
                        else throw new Error('Confirm button not found');
                    ");
                    
            $browser->waitForText('Pemesanan Berhasil', 10);
        });
    }
}
