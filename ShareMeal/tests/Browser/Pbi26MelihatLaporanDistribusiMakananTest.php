<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class Pbi26MelihatLaporanDistribusiMakananTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test admin login and view distribution report page.
     */
    public function testAdminCanViewDistributionReport()
    {
        // Create admin user for the test
        User::create([
            'name' => 'Admin',
            'email' => 'admin@sharemeal.id',
            'password' => Hash::make('password123'),
            // Assuming a role column exists; adjust if needed
            'role' => 'admin',
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->select('user_type', 'admin')
                ->type('email', 'admin@sharemeal.id')
                ->type('password', 'password123')
                ->press('Masuk')
                ->assertPathIs('/admin')
                ->visit('/admin/reports')
                ->assertPathIs('/admin/reports')
                ->assertSee('Laporan Distribusi Makanan');
        });
    }
}
?>
