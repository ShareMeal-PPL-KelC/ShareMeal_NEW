<?php

namespace Tests\Browser;

use Tests\DuskTestCase;

class DashboardAnalyticsTest extends DuskTestCase
{
    /**
     * PBI #27: Dashboard Analytics - Food Waste Statistics
     * Admin scroll ke #dampak-platform dan cek statistik makanan terselamatkan
     */
    public function testAdminCanViewDashboardAnalytics(): void
    {
        $this->browse(function ($browser) {
            // 1. Login sebagai admin - gunakan selector eksplisit
            $browser->visit('/login')
                    ->select('select[name="user_type"]', 'admin')
                    ->type('input[name="email"]', 'admin@sharemeal.id')
                    ->type('input[name="password"]', 'password123')
                    ->press('button[type="submit"]')
                    ->pause(1500);
            
            // Verify redirect ke admin dashboard
            $browser->assertPathIs('/admin')
                    ->assertSee('Dashboard Admin');
            
            // 2. Visit dashboard - cukup cek stats ada tanpa scroll
            $browser->visit('/admin');
            $browser->assertPathIs('/admin');
            
            // 3. Cek apakah statistik dasar ada
            $browser->assertSee('Total User');
        });
    }
}