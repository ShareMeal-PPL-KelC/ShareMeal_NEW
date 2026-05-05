<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class LoginFailedTest extends DuskTestCase
{
    /**
     * TC.Login.002 - Negative Case
     * Menguji login dengan password yang salah
     */
    public function testLoginGagalPasswordSalah()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->select('user_type', 'consumer')
                    ->type('email', 'kya@gmail.com')
                    ->type('password', 'ini-password-salah-123')
                    ->press('Masuk')
                    ->assertPathIs('/login')
                    ->assertSee('Email, password, atau tipe pengguna tidak sesuai.');
        });
    }

    /**
     * TC.Login.003 - Negative Case
     * Menguji login dengan email yang tidak terdaftar
     */
    public function testLoginGagalEmailTidakSesuai()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->select('user_type', 'consumer')
                    ->type('email', 'akun-ngasal@gmail.com')
                    ->type('password', 'password123')
                    ->press('Masuk')
                    ->assertPathIs('/login')
                    ->assertSee('Email, password, atau tipe pengguna tidak sesuai.');
        });
    }
}