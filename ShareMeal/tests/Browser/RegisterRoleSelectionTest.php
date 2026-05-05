<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * PBI #1 TC.Role.001 - Menguji pemilihan role pengguna (Positive)
 * 
 * Skenario: User memilih salah satu role (konsumen, lembaga sosial, atau mitra)
 * Expected: Role yang dipilih tersorot atau tertandai aktif
 */
class RegisterRoleSelectionTest extends DuskTestCase
{
    
    public function test_halaman_register_menampilkan_pilihan_role()
    {
        $this->browse(function (Browser $browser) {
            $browser->driver->manage()->deleteAllCookies();
            $browser->visit('/register')
                    ->assertSee('Buat Akun Baru')
                    ->assertSee('PILIH PERAN ANDA')
                    ->assertSee('Mitra')
                    ->assertSee('Konsumen')
                    ->assertSee('Lembaga');
        });
    }

    public function test_pemilihan_role_konsumen()
    {
        $this->browse(function (Browser $browser) {
            $browser->driver->manage()->deleteAllCookies();
            $browser->visit('/register')
                    // Mitra adalah default (seperti di x-data="{ userType: 'mitra' }")
                    ->assertRadioSelected('user_type', 'mitra')
                    
                    // Klik radio button Konsumen
                    ->radio('user_type', 'consumer')
                    ->pause(300) // Tunggu transisi AlpineJS
                    ->assertRadioSelected('user_type', 'consumer');
        });
    }

    public function test_pemilihan_role_mitra()
    {
        $this->browse(function (Browser $browser) {
            $browser->driver->manage()->deleteAllCookies();
            $browser->visit('/register')
                    // Klik role lain dulu untuk memastikan perubahannya
                    ->radio('user_type', 'consumer')
                    ->pause(300)
                    
                    // Klik radio button Mitra
                    ->radio('user_type', 'mitra')
                    ->pause(300)
                    ->assertRadioSelected('user_type', 'mitra');
        });
    }

    public function test_pemilihan_role_lembaga_sosial()
    {
        $this->browse(function (Browser $browser) {
            $browser->driver->manage()->deleteAllCookies();
            $browser->visit('/register')
                    // Klik radio button Lembaga
                    ->radio('user_type', 'lembaga')
                    ->pause(300)
                    ->assertRadioSelected('user_type', 'lembaga');
        });
    }
}
