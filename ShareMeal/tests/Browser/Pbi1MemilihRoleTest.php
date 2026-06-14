<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class Pbi1MemilihRoleTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_user_can_select_role_on_login_page(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->assertPathIs('/login')
                    ->waitFor('select[name="user_type"]', 30)
                    ->select('user_type', 'consumer')
                    ->assertSelected('user_type', 'consumer');
        });
    }
}

