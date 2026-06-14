<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

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

        $browser->visit('/register')
                ->assertPathIs('/register');
        
        // Use JavaScript click because the radio button is wrapped in a label with 'sr-only' class,
        // which causes ElementClickInterceptedException in Selenium when clicked directly.
        $browser->script("document.querySelector('input[value=\"lembaga\"]').click();");

        $browser->pause(500)
                ->attach('document_legalitas_lembaga', $legalitasFile)
                ->attach('document_izin_lembaga', $izinFile)
                ->attach('document_identitas_lembaga', $identitasFile)
                ->type('organization_name', 'Lembaga PBI Empat')
                ->type('name', 'Lembaga Pengurus')
                ->type('email', $email)
                ->type('password', 'password123')
                ->type('password_confirmation', 'password123')
                ->check('terms')
                ->click('button[type="submit"]')
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
            $browser->visit('/login')
                    ->select('user_type', 'admin')
                    ->type('email', 'admin@sharemeal.id')
                    ->type('password', 'password123')
                    ->click('button[type="submit"]')
                    ->waitForLocation('/admin', 15);

            // 4. Open Verifikasi menu
            $browser->clickLink('Verifikasi')
                    ->waitForLocation('/admin/verification', 15)
                    ->assertPathIs('/admin/verification');

            // 5. Click 'Detail/Lihat' (Preview Dokumen) for the specific user
            $browser->click($previewSelector)
                    ->pause(1000) // wait for preview modal animation
                    ->click('div[x-show="previewModalOpen"] button') // Close the preview modal
                    ->pause(500);

            // 6. Click Setujui Pendaftaran (Terima/Approve) for the specific user
            $browser->click($approveSelector)
                    ->waitForLocation('/admin/verification', 15)
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
            $browser->visit('/login')
                    ->select('user_type', 'admin')
                    ->type('email', 'admin@sharemeal.id')
                    ->type('password', 'password123')
                    ->click('button[type="submit"]')
                    ->waitForLocation('/admin', 15);

            // 4. Open Verifikasi menu
            $browser->clickLink('Verifikasi')
                    ->waitForLocation('/admin/verification', 15)
                    ->assertPathIs('/admin/verification');

            // 5. Click 'Preview Dokumen' for the specific user
            $browser->click($previewSelector)
                    ->pause(1000)
                    ->click('div[x-show="previewModalOpen"] button') // Close the preview modal
                    ->pause(500);

            // 6. Click Tolak (Reject) for the specific user
            $browser->click($rejectSelector)
                    ->waitFor('#btn-confirm-reject', 5) // wait for reject modal
                    ->type('reason', 'Dokumen Legalitas tidak sesuai dengan identitas organisasi.')
                    ->click('#btn-confirm-reject')
                    ->waitForLocation('/admin/verification', 15)
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
