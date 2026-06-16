<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * PBI-1: Memilih Role
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
class Pbi1MemilihRoleTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_user_can_select_role_on_login_page(): void
    {
        $this->browse(function (Browser $browser) {
            // Mengunjungi halaman '/login'
            $browser->visit('/login')
                    // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/login'
                    ->assertPathIs('/login')
                    // Menunggu elemen 'elemen terkait' muncul di layar (batas waktu standar detik)
                    ->waitFor('select[name="user_type"]', 30)
                    // Memilih opsi 'consumer' pada dropdown 'user_type'
                    ->select('user_type', 'consumer')
                    // Memverifikasi opsi dropdown 'user_type' yang terpilih adalah 'consumer'
                    ->assertSelected('user_type', 'consumer');
        });
    }
}

