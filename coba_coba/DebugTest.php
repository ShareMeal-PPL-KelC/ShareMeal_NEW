<?php

namespace Tests\Browser;

use Tests\DuskTestCase;

class DebugTest extends DuskTestCase
{
    public function testDebug(): void
    {
        $this->browse(function ($browser) {
            // Login as admin
            $browser->visit('/login')
                    ->select('user_type', 'admin')
                    ->type('email', 'admin@sharemeal.id')
                    ->type('password', 'password123')
                    ->press('Masuk')
                    ->pause(1500);
            
            // Visit transactions page
            $browser->visit('/admin/transactions');
            
            $source = $browser->driver->getPageSource();
            
            // Check what text is on page
            $checks = [
                'Pemantauan Transaksi' => strpos($source, 'Pemantauan Transaksi') !== false,
                'Total Transaksi' => strpos($source, 'Total Transaksi') !== false,
                'Transaksi' => strpos($source, 'Transaksi') !== false,
                '5420' => strpos($source, '5420') !== false,
            ];
            
            foreach ($checks as $name => $found) {
                echo "$name: " . ($found ? 'FOUND' : 'NOT FOUND') . "\n";
            }
            
            $this->assertTrue(true);
        });
    }
}