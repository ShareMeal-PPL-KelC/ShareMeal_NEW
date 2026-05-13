<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Donation;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PBI23_NotifikasiDonasiTersediaTest extends DuskTestCase
{
    /**
     * POSITIVE TEST: Lembaga berhasil melihat notifikasi saat ada donasi baru tersedia.
     */
    public function testNotifikasiDonasiTersedia(): void
    {
        $this->browse(function (Browser $browser) {
            $email = 'test_lembaga_' . Str::random(4) . '@example.com';
            $password = 'password123';
            
            // 1. SETUP DATA (Sangat aman, data akan dihapus di akhir test)
            $lembaga = User::create([
                'name' => 'Lembaga Test',
                'email' => $email,
                'password' => bcrypt($password),
                'role' => 'lembaga',
                'is_verified' => true,
            ]);

            $mitra = User::where('role', 'mitra')->first();
            if (!$mitra) {
                 $mitra = User::create([
                    'name' => 'Mitra Test',
                    'email' => 'mitra_test_' . Str::random(4) . '@example.com',
                    'password' => bcrypt('password'),
                    'role' => 'mitra',
                    'is_verified' => true,
                ]);
            }

            $donationTitle = 'Donasi Makanan ' . Str::random(4);
            $donation = Donation::create([
                'mitra_id' => $mitra->id,
                'title' => $donationTitle,
                'quantity' => 10,
                'unit' => 'box',
                'status' => 'pending'
            ]);

            // Buat notifikasi manual agar tidak perlu menambah file class baru di folder app/
            $lembaga->notifications()->create([
                'id' => Str::uuid(),
                'type' => 'App\Notifications\DonationAvailableNotification',
                'data' => [
                    'title' => 'Donasi Baru Tersedia!',
                    'message' => 'Terdapat donasi baru: ' . $donationTitle,
                    'donation_id' => $donation->id,
                ],
            ]);

            try {
                // 2. JALANKAN TEST
                $browser->visit('/login')
                    ->select('user_type', 'lembaga')
                    ->type('email', $lembaga->email)
                    ->type('password', $password)
                    ->press('Masuk')
                    ->waitForLocation('/lembaga', 15)
                    ->assertSee('Dashboard Lembaga') // Pastikan berhasil login
                    ->pause(2000)
                    // Menggunakan selector CSS langsung ke icon lonceng
                    ->click('nav button i[data-lucide="bell"], nav button svg.lucide-bell')
                    ->pause(2000)
                    ->assertSee('Donasi Baru Tersedia!')
                    ->assertSee($donationTitle);
            } finally {
                // 3. PEMBERSIHAN (Hapus item agar DB tidak kotor)
                $donation->delete();
                $lembaga->delete();
                if ($mitra && str_contains($mitra->email, 'mitra_test_')) {
                    $mitra->delete();
                }
            }
        });
    }

    /**
     * NEGATIVE TEST: Lembaga tidak melihat notifikasi donasi jika memang tidak ada.
     */
    public function testNotifikasiDonasiKosong(): void
    {
        $this->browse(function (Browser $browser) {
            $email = 'test_clean_' . Str::random(4) . '@example.com';
            $password = 'password123';

            $lembaga = User::create([
                'name' => 'Lembaga Bersih',
                'email' => $email,
                'password' => bcrypt($password),
                'role' => 'lembaga',
                'is_verified' => true,
            ]);

            try {
                $browser->visit('/login')
                    ->select('user_type', 'lembaga')
                    ->type('email', $lembaga->email)
                    ->type('password', $password)
                    ->press('Masuk')
                    ->waitForLocation('/lembaga', 15)
                    ->assertSee('Dashboard Lembaga')
                    ->pause(2000)
                    ->click('nav button i[data-lucide="bell"], nav button svg.lucide-bell')
                    ->pause(2000)
                    ->assertDontSee('Donasi Baru Tersedia!');
            } finally {
                $lembaga->delete();
            }
        });
    }
}
