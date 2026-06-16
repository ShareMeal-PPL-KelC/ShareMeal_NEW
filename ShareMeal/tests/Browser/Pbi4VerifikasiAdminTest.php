<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * PBI-4: Verifikasi Admin
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
class Pbi4VerifikasiAdminTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        User::updateOrCreate(
            ['email' => 'admin@sharemeal.id'],
            [
                'name' => 'Admin ShareMeal',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'status' => 'active',
                'is_verified' => true,
                'joined_at' => now(),
            ]
        );
    }

    private function registerLembaga(Browser $browser, string $email): void
    {
        $legalitasFile = public_path('images/logo.png');
        $izinFile = public_path('images/logo2.png');
        $identitasFile = public_path('images/screen.png');

        // Mengunjungi halaman '/register'
        $browser->visit('/register')
                // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/register'
                ->assertPathIs('/register');
        
        // Use JavaScript click because the radio button is wrapped in a label with 'sr-only' class,
        // which causes ElementClickInterceptedException in Selenium when clicked directly.
        // Eksekusi skrip JavaScript kustom di browser untuk menyimulasikan interaksi kompleks
        $browser->script("document.querySelector('input[value=\"lembaga\"]').click();");

        // Menjeda eksekusi selama 500 milidetik agar proses render/transisi halaman selesai
        $browser->pause(500)
                // Mengunggah berkas dokumen ke input file 'document_legalitas_lembaga'
                ->attach('document_legalitas_lembaga', $legalitasFile)
                // Mengunggah berkas dokumen ke input file 'document_izin_lembaga'
                ->attach('document_izin_lembaga', $izinFile)
                // Mengunggah berkas dokumen ke input file 'document_identitas_lembaga'
                ->attach('document_identitas_lembaga', $identitasFile)
                // Mengisi input 'organization_name' dengan nilai 'Lembaga PBI Empat'
                ->type('organization_name', 'Lembaga PBI Empat')
                // Mengisi input 'name' dengan nilai 'Lembaga Pengurus'
                ->type('name', 'Lembaga Pengurus')
                // Mengisi input field 'email'
                ->type('email', $email)
                // Mengisi input 'password' dengan nilai 'password123'
                ->type('password', 'password123')
                // Mengisi input 'password_confirmation' dengan nilai 'password123'
                ->type('password_confirmation', 'password123')
                // Mencentang (check) checkbox 'terms'
                ->check('terms')
                // Mengeklik elemen 'elemen terkait' di halaman
                ->click('button[type="submit"]')
                // Menunggu halaman berpindah ke rute '/login' (batas waktu 15 detik)
                ->waitForLocation('/login', 15);
    }

    public function test_admin_can_approve_pending_lembaga(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->driver->manage()->deleteAllCookies();
            $email = 'lembaga_approve_' . time() . '_' . rand(1000, 9999) . '@example.com';
            
            // 1. Register a pending Lembaga
            $this->registerLembaga($browser, $email);

            // 2. Find the registered user to target their specific buttons
            $user = User::where('email', $email)->first();
            if (!$user) {
                try {
                    $user = User::on('sqlite_main')->where('email', $email)->first();
                } catch (\Throwable $e) {}
            }
            $userId = $user ? $user->id : null;

            $previewSelector = $userId ? "#btn-preview-{$userId}-legalitas" : 'button[id^="btn-preview-"]';
            $approveSelector = $userId ? "#btn-approve-{$userId}" : 'button[id^="btn-approve-"]';

            // 3. Login as Admin
            // Mengunjungi halaman '/login'
            $browser->visit('/login')
                    // Memilih opsi 'admin' pada dropdown 'user_type'
                    ->select('user_type', 'admin')
                    // Mengisi input 'email' dengan nilai 'admin@sharemeal.id'
                    ->type('email', 'admin@sharemeal.id')
                    // Mengisi input 'password' dengan nilai 'password123'
                    ->type('password', 'password123')
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('button[type="submit"]')
                    // Menunggu halaman berpindah ke rute '/admin' (batas waktu 15 detik)
                    ->waitForLocation('/admin', 15);

            // 4. Open Verifikasi menu
            $browser->clickLink('Verifikasi')
                    // Menunggu halaman berpindah ke rute '/admin/verification' (batas waktu 15 detik)
                    ->waitForLocation('/admin/verification', 15)
                    // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/admin/verification'
                    ->assertPathIs('/admin/verification');

            // 5. Click 'Detail/Lihat' (Preview Dokumen) for the specific user
            // Mengeklik elemen 'elemen terkait' di halaman
            $browser->click($previewSelector)
                    // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
                    ->pause(1000) // wait for preview modal animation
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('div[x-show="previewModalOpen"] button') // Close the preview modal
                    // Menjeda eksekusi selama 500 milidetik agar proses render/transisi halaman selesai
                    ->pause(500);

            // 6. Click Setujui Pendaftaran (Terima/Approve) for the specific user
            // Mengeklik elemen 'elemen terkait' di halaman
            $browser->click($approveSelector)
                    // Menunggu halaman berpindah ke rute '/admin/verification' (batas waktu 15 detik)
                    ->waitForLocation('/admin/verification', 15)
                    // Memastikan teks '' TIDAK muncul pada halaman browser
                    ->assertDontSee($email);

            // Verify the status in the database is verified (is_verified = 1)
            $verifiedUser = User::where('email', $email)->first();
            if (!$verifiedUser) {
                try {
                    $verifiedUser = User::on('sqlite_main')->where('email', $email)->first();
                } catch (\Throwable $e) {}
            }
            $this->assertNotNull($verifiedUser);
            $this->assertEquals(1, $verifiedUser->is_verified);
            $browser->blank();
        });
    }

    public function test_admin_can_reject_pending_lembaga(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->driver->manage()->deleteAllCookies();
            $email = 'lembaga_reject_' . time() . '_' . rand(1000, 9999) . '@example.com';
            
            // 1. Register another pending Lembaga
            $this->registerLembaga($browser, $email);

            // 2. Find the registered user to target their specific buttons
            $user = User::where('email', $email)->first();
            if (!$user) {
                try {
                    $user = User::on('sqlite_main')->where('email', $email)->first();
                } catch (\Throwable $e) {}
            }
            $userId = $user ? $user->id : null;

            $previewSelector = $userId ? "#btn-preview-{$userId}-legalitas" : 'button[id^="btn-preview-"]';
            $rejectSelector = $userId ? "#btn-reject-{$userId}" : 'button[id^="btn-reject-"]';

            // 3. Login as Admin
            // Mengunjungi halaman '/login'
            $browser->visit('/login')
                    // Memilih opsi 'admin' pada dropdown 'user_type'
                    ->select('user_type', 'admin')
                    // Mengisi input 'email' dengan nilai 'admin@sharemeal.id'
                    ->type('email', 'admin@sharemeal.id')
                    // Mengisi input 'password' dengan nilai 'password123'
                    ->type('password', 'password123')
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('button[type="submit"]')
                    // Menunggu halaman berpindah ke rute '/admin' (batas waktu 15 detik)
                    ->waitForLocation('/admin', 15);

            // 4. Open Verifikasi menu
            $browser->clickLink('Verifikasi')
                    // Menunggu halaman berpindah ke rute '/admin/verification' (batas waktu 15 detik)
                    ->waitForLocation('/admin/verification', 15)
                    // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/admin/verification'
                    ->assertPathIs('/admin/verification');

            // 5. Click 'Preview Dokumen' for the specific user
            // Mengeklik elemen 'elemen terkait' di halaman
            $browser->click($previewSelector)
                    // Menjeda eksekusi selama 1000 milidetik agar proses render/transisi halaman selesai
                    ->pause(1000)
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('div[x-show="previewModalOpen"] button') // Close the preview modal
                    // Menjeda eksekusi selama 500 milidetik agar proses render/transisi halaman selesai
                    ->pause(500);

            // 6. Click Tolak (Reject) for the specific user
            // Mengeklik elemen 'elemen terkait' di halaman
            $browser->click($rejectSelector)
                    // Menunggu elemen '#btn-confirm-reject' muncul di layar (batas waktu 5 detik)
                    ->waitFor('#btn-confirm-reject', 5) // wait for reject modal
                    // Mengisi input 'reason' dengan nilai 'Dokumen Legalitas tidak sesuai dengan identitas organisasi.'
                    ->type('reason', 'Dokumen Legalitas tidak sesuai dengan identitas organisasi.')
                    // Mengeklik elemen '#btn-confirm-reject' di halaman
                    ->click('#btn-confirm-reject')
                    // Menunggu halaman berpindah ke rute '/admin/verification' (batas waktu 15 detik)
                    ->waitForLocation('/admin/verification', 15)
                    // Memastikan teks '' TIDAK muncul pada halaman browser
                    ->assertDontSee($email);

            // Verify the status in the database is rejected (is_verified = 0 and has reason)
            $rejectedUser = User::where('email', $email)->first();
            if (!$rejectedUser) {
                try {
                    $rejectedUser = User::on('sqlite_main')->where('email', $email)->first();
                } catch (\Throwable $e) {}
            }
            $this->assertNotNull($rejectedUser);
            $this->assertEquals(0, $rejectedUser->is_verified);
            $this->assertEquals('Dokumen Legalitas tidak sesuai dengan identitas organisasi.', $rejectedUser->verification_rejection_reason);
            $browser->blank();
        });
    }
}
