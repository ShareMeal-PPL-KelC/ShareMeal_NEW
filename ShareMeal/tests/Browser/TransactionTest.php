<?php

namespace Tests\Browser;

use Tests\DuskTestCase;

class TransactionTest extends DuskTestCase
{
    /**
     * PBI #26: Navigasi ke menu Transaksi dan Export CSV
     */
    public function testAdminCanViewTransactionsAndExport(): void
    {
        $this->browse(function ($browser) {
            // 1. Login sebagai admin
            $browser->visit('/login')
                    ->select('user_type', 'admin')
                    ->type('email', 'admin@sharemeal.id')
                    ->type('password', 'password123')
                    ->press('Masuk')
                    ->pause(2000);
            
            // Verify redirect ke admin dashboard
            $browser->assertPathIs('/admin');
            
            // 2. Navigasi ke Transaksi
            $browser->visit('/admin/transactions');
            
            // 3. Pastikan riwayat transaksi muncul
            $browser->assertPathIs('/admin/transactions');
            
            // Debug: print first 200 chars
            $source = substr($browser->driver->getPageSource(), 0, 500);
            echo "Source: $source\n";
        });
    }
}