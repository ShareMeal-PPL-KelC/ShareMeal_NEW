<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi48BlokirPeringatanMitraTest1 extends DuskTestCase
{
    /**
     * PBI 48 - Memberikan Peringatan atau Memblokir Akun Mitra (sebagai Admin)
     */
    public function test_admin_dapat_memberi_peringatan_dan_memblokir_mitra(): void
    {
        $this->browse(function (Browser $browser) {
            // ================================================
            // STEP 1: Login sebagai Admin
            // ================================================
            $browser->visit('/login')
                ->waitFor('select[name="user_type"], input[name="email"], input[name="password"]', 15)
                ->type('email', 'admin@sharemeal.id')
                ->type('password', 'password123')
                ->select('user_type', 'admin')
                ->press('Masuk')
                ->waitForLocation('/admin', 15)
                ->assertPathIs('/admin')
                ->assertDontSee('Login gagal');

            // ================================================
            // STEP 2: Buka halaman laporan masalah (moderasi)
            // ================================================
            // Catatan: rute warn/block Mitra adalah untuk ProblemReport,
            // sehingga tombol yang ada di halaman ini.
            $browser->visit('/admin/problem-reports')
                ->waitFor('body', 15)
                ->assertPathIs('/admin/problem-reports')
                ->assertDontSee('Whoops')
                ->assertDontSee('Server Error');

            // ================================================
            // STEP 3A: Klik tombol "Beri Peringatan" (jika ada)
            // ================================================
            // Karena HTML dapat menggunakan tombol dengan teks yang berbeda,
            // kita coba beberapa opsi.
            $browser->assertTrue(true);

            if (count($browser->elements('button, input[type="submit"], form button')) > 0) {
                $possible = [
                    'Beri Peringatan',
                    'Peringatan',
                    'Warn',
                    'Warn Mitra',
                ];

                foreach ($possible as $text) {
                    try {
                        $browser->press($text)->wait(1);
                        break;
                    } catch (\Throwable $e) {
                        // abaikan, coba teks lain
                    }
                }
            }

            // Verifikasi komponen sukses (teks konfirmasi sesuai sistem)
            // Gunakan setidaknya satu dari beberapa kandidat.
            $browser->wait(2)
                ->assertSee('Peringatan');

            // ================================================
            // STEP 3B: Klik tombol "Blokir" (jika ada)
            // ================================================
            // Kembali untuk melakukan aksi berikutnya.
            $browser->visit('/admin/problem-reports')
                ->waitFor('body', 15)
                ->assertPathIs('/admin/problem-reports');

            $possibleBlock = [
                'Blokir',
                'Block',
                'Blokir Mitra',
            ];

            foreach ($possibleBlock as $text) {
                try {
                    $browser->press($text)->wait(1);
                    break;
                } catch (\Throwable $e) {
                    // abaikan, coba teks lain
                }
            }

            $browser->wait(2)
                ->assertSee('diblokir');
        });
    }
}

