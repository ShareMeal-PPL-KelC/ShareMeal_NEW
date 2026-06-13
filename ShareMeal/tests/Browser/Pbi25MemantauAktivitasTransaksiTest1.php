<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi25MemantauAktivitasTransaksiTest1 extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test admin can monitor transaction activity.
     */
    public function test_admin_bisa_memantau_aktivitas_transaksi(): void
    {
        $this->browse(function (Browser $browser) {
            // Seed Admin account
            User::factory()->create([
                'name' => 'Admin ShareMeal',
                'email' => 'admin@sharemeal.id',
                'password' => bcrypt('password123'),
                'role' => 'admin',
                'status' => 'active',
                'is_verified' => true,
            ]);

            // Seed Customer, Mitra, and a Transaction Order
            $customer = User::factory()->create([
                'name' => 'Budi Santoso',
                'role' => 'consumer',
            ]);

            $mitra = User::factory()->create([
                'name' => 'Toko Roti Makmur',
                'role' => 'mitra',
            ]);

            Order::create([
                'customer_id' => $customer->id,
                'mitra_id' => $mitra->id,
                'total_amount' => 28000,
                'status' => 'pending',
                'pickup_code' => 'XYZ123',
                'receiving_method' => 'pickup',
                'payment_method' => 'GoPay',
            ]);

            // Visit the landing/home page
            $browser->visit('/')
                    ->assertSee('ShareMeal')
                    
                    // Navigate to Login page
                    ->clickLink('Masuk')
                    ->assertPathIs('/login')
                    
                    // Login using Admin account
                    ->select('user_type', 'admin')
                    ->type('email', 'admin@sharemeal.id')
                    ->type('password', 'password123')
                    ->press('Masuk')
                    
                    // Assert redirection to Admin Dashboard
                    ->assertPathIs('/admin')
                    ->assertSee('Dashboard Admin')
                    
                    // Navigate to Transactions page using sidebar link
                    ->click('a[href*="/admin/transactions"]')
                    
                    // Assert path and transaction table elements
                    ->assertPathIs('/admin/transactions')
                    ->assertSee('Pemantauan Transaksi')
                    ->assertSee('Riwayat Transaksi')
                    ->assertSee('ID Transaksi')
                    ->assertSee('Budi Santoso')
                    ->assertSee('Toko Roti Makmur')
                    ->assertSee('Rp 28.000');
        });
    }
}
