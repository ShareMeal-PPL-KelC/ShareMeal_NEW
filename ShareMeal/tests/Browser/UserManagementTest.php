<?php

namespace Tests\Browser;

use Tests\DuskTestCase;

class UserManagementTest extends DuskTestCase
{
    /**
     * PBI #24: Lihat Data Pengguna
     */
    public function testAdminCanViewUserList(): void
    {
        $this->browse(function ($browser) {
            // 1. Login sebagai admin
            $browser->visit('/login')
                    ->select('user_type', 'admin')
                    ->type('email', 'admin@sharemeal.id')
                    ->type('password', 'password123')
                    ->press('Masuk')
                    ->pause(1500);
            
            // Verify redirect ke admin dashboard
            $browser->assertPathIs('/admin')
                    ->assertSee('Dashboard Admin');
            
            // 2. Navigasi ke Kelola User
            $browser->visit('/admin/users');
            
            // 3. Pastikan daftar pengguna muncul - PBI #24
            $browser->assertPathIs('/admin/users')
                    ->assertSee('Kelola Pengguna');
        });
    }

    /**
     * PBI #25: Filter Akun Konsumen
     */
    public function testAdminCanFilterKonsumen(): void
    {
        $this->browse(function ($browser) {
            // Langsung visit halaman Kelola User
            $browser->visit('/admin/users')
                    ->assertSee('Kelola Pengguna');
            
            // Pilih button 'Konsumen'
            $browser->press('Konsumen')
                    ->pause(1000);
            
            // Pastikan konsumen muncul
            $browser->assertSee('konsumen');
        });
    }
}