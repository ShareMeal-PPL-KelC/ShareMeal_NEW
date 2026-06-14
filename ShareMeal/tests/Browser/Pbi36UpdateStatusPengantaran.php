<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\Order;

class Pbi36UpdateStatusPengantaran extends DuskTestCase
{
    /**
     * Test PBI-36: Update Status Pengantaran oleh Mitra.
     *
     * @return void
     */
    public function testUpdateStatusPengantaran()
    {
        $this->browse(function (Browser $browser) {
            // Kita butuh sebuah order dengan metode 'delivery' untuk diuji
            // Jika ada order yang pending, kita akan memprosesnya.
            
            // Dimulai dari homescreen awal
            $browser->maximize()
                    ->visit('/')
                    ->pause(1000)
            
                    // Klik tombol masuk
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
                    
                    // Masuk ke halaman pesanan
                    ->visit(route('mitra.orders'))
                    ->pause(2000);
            
            // 1. Konfirmasi Pembayaran dan Proses Pesanan
            $browser->script("
                let btn = document.querySelector('button[\\\\@click*=\"updateStatus(order.id, \\'processing\\')\"]');
                if(btn) btn.click();
                else throw new Error('Tombol Proses Pesanan tidak ditemukan');
            ");
            $browser->pause(1000);
            
            // Klik Ya, Lanjutkan
            $browser->script("
                let confirmBtn = document.querySelector('button[\\\\@click*=\"executeConfirm()\"]');
                if(confirmBtn) confirmBtn.click();
                else throw new Error('Tombol Ya, Lanjutkan tidak ditemukan');
            ");
            $browser->pause(2000); // Tunggu animasi dan render ulang
            
            // 2. Pesanan Siap
            $browser->script("
                let btnReady = document.querySelector('button[\\\\@click*=\"updateStatus(order.id, \\'ready\\')\"]');
                if(btnReady) btnReady.click();
                else throw new Error('Tombol Pesanan Siap tidak ditemukan');
            ");
            $browser->pause(1000);
            
            // Klik Ya, Lanjutkan
            $browser->script("
                let confirmBtn = document.querySelector('button[\\\\@click*=\"executeConfirm()\"]');
                if(confirmBtn) confirmBtn.click();
                else throw new Error('Tombol Ya, Lanjutkan tidak ditemukan');
            ");
            $browser->pause(2000);
            
            // 3. Kirim Sekarang
            $browser->script("
                let btnShipping = document.querySelector('button[\\\\@click*=\"updateStatus(order.id, \\'shipping\\')\"]');
                if(btnShipping) btnShipping.click();
                else throw new Error('Tombol Kirim Sekarang tidak ditemukan');
            ");
            $browser->pause(1000);
            
            // Klik Ya, Lanjutkan
            $browser->script("
                let confirmBtn = document.querySelector('button[\\\\@click*=\"executeConfirm()\"]');
                if(confirmBtn) confirmBtn.click();
                else throw new Error('Tombol Ya, Lanjutkan tidak ditemukan');
            ");
            $browser->pause(2000);
            
            // 4. Konfirmasi Sampai & Selesai
            $browser->script("
                let btnCompleted = document.querySelector('button[\\\\@click*=\"updateStatus(order.id, \\'completed\\')\"]');
                if(btnCompleted) btnCompleted.click();
                else throw new Error('Tombol Konfirmasi Sampai & Selesai tidak ditemukan');
            ");
            $browser->pause(1000);
            
            // Klik Ya, Lanjutkan
            $browser->script("
                let confirmBtn = document.querySelector('button[\\\\@click*=\"executeConfirm()\"]');
                if(confirmBtn) confirmBtn.click();
                else throw new Error('Tombol Ya, Lanjutkan tidak ditemukan');
            ");
            $browser->pause(2000);
            
            // (Opsional) Memastikan tidak ada error dan status pesanan telah berubah menjadi selesai
            // Dapat diverifikasi jika tombol tadi berhasil hilang atau muncul label Selesai
            $browser->assertPathIs('/mitra/orders');
        });
    }
}
