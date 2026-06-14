<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi4VerifikasiAdminTest extends DuskTestCase
{
    public function createApplication()
    {
        $basePath = dirname(__DIR__, 2);
        $sqliteDatabase = str_replace('\\', '/', $basePath . '/database/testing.sqlite');

        if (! file_exists($sqliteDatabase)) {
            @touch($sqliteDatabase);
        }

        // Dynamically override the .env file on disk. This is necessary because the web server
        // (started separately, e.g. via php artisan serve) reads the .env file on every request,
        // and we need it to use the SQLite configuration and file-based sessions so it does not
        // attempt to connect to a MySQL database that might not be running or accessible.
        $envPath = $basePath . '/.env';
        if (file_exists($envPath)) {
            $envContent = file_get_contents($envPath);
            
            $replacements = [
                'DB_CONNECTION' => 'sqlite',
                'DB_DATABASE' => '"' . $sqliteDatabase . '"',
                'SESSION_DRIVER' => 'file',
                'CACHE_STORE' => 'array',
                'DB_HOST' => '',
                'DB_PORT' => '',
                'DB_USERNAME' => '',
                'DB_PASSWORD' => '',
            ];

            foreach ($replacements as $key => $value) {
                if (preg_match("/^{$key}=/m", $envContent)) {
                    $envContent = preg_replace("/^{$key}=.*$/m", "{$key}={$value}", $envContent);
                } else {
                    $envContent .= "\n{$key}={$value}";
                }
            }
            
            file_put_contents($envPath, $envContent);
        }

        $envOverrides = [
            'APP_ENV' => 'testing',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => $sqliteDatabase,
            'CACHE_DRIVER' => 'array',
            'SESSION_DRIVER' => 'file',
            'QUEUE_CONNECTION' => 'sync',
        ];

        foreach ($envOverrides as $key => $value) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }

        $app = parent::createApplication();

        $app['config']->set('app.env', 'testing');
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', $sqliteDatabase);
        $app['config']->set('cache.default', 'array');
        $app['config']->set('session.driver', 'file');
        $app['config']->set('queue.default', 'sync');

        // Configure sqlite_main connection to point to database.sqlite
        $mainDbPath = $basePath . '/database/database.sqlite';
        $app['config']->set('database.connections.sqlite_main', [
            'driver' => 'sqlite',
            'database' => $mainDbPath,
            'prefix' => '',
        ]);

        try {
            // Run migrations on testing database
            $app->make(\Illuminate\Contracts\Console\Kernel::class)->call('migrate', ['--force' => true]);

            // Seed Admin User in testing database
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

            // Seed Admin User in main database.sqlite database
            User::on('sqlite_main')->updateOrCreate(
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
        } catch (\Throwable $e) {
            // Ignore if migration or seeding fails
        }

        return $app;
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
        });
    }

    public function test_admin_can_reject_pending_lembaga(): void
    {
        $this->browse(function (Browser $browser) {
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
        });
    }
}
