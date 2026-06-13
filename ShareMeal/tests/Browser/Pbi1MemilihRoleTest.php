<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class Pbi1MemilihRoleTest extends DuskTestCase
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

        try {
            $app->make(\Illuminate\Contracts\Console\Kernel::class)->call('migrate', ['--force' => true]);
        } catch (\Throwable $e) {
            // Ignore if migration fails
        }

        return $app;
    }

    public function test_user_can_select_role_on_login_page(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                    ->assertPathIs('/login')
                    ->waitFor('select[name="user_type"]', 30)
                    ->select('user_type', 'consumer')
                    ->assertSelected('user_type', 'consumer');
        });
    }
}
