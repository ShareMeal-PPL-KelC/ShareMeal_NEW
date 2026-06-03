<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;

class LoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * TC.Login.001 - PBI #28
     * Menguji fungsionalitas halaman Login - Postive Case
     */
    public function testLoginBerhasil()
    {
        $this->browse(function (Browser $browser) {
            $kya = User::firstOrCreate(
                ['email' => 'kya@gmail.com'],
                [
                    'name' => 'Kya Test User',
                    'password' => bcrypt('password'),
                    'role' => 'consumer',
                    'is_verified' => true
                ]
            );

            $browser->loginAs($kya)
                    ->visit('/consumer');
            });
    }
}