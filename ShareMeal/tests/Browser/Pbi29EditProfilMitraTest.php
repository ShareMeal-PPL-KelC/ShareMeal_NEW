<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class Pbi29EditProfilMitraTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_positive_update_mitra_profile(): void
    {
        $email = 'mitrapbi29_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $password = 'password123';

        $user = User::factory()->create([
            'role' => 'mitra',
            'name' => 'Mitra PBI 29',
            'email' => $email,
            'password' => Hash::make($password),
            'is_verified' => true,
        ]);

        UserProfile::create([
            'user_id' => $user->id,
            'business_name' => 'Resto Berkah',
            'business_type' => 'Restoran',
            'business_address' => 'Jl. Berkah No. 12',
            'business_contact' => '081234567890',
            'business_opening_hours' => '08:00 - 20:00',
            'opening_hours' => '08:00 - 20:00',
            'business_description' => 'Toko makanan berkah.',
            'description' => 'Toko makanan berkah.',
        ]);

        $this->browse(function (Browser $browser) use ($email, $password) {
            $browser->driver->manage()->deleteAllCookies();

            // Login
            $browser->visit('/login')
                    ->assertPathIs('/login')
                    ->select('user_type', 'mitra')
                    ->type('email', $email)
                    ->type('password', $password)
                    ->click('button[type="submit"]')
                    ->waitForLocation('/mitra', 15);

            // Go to profile-usaha
            $browser->visit('/mitra/profile-usaha')
                    ->assertPathIs('/mitra/profile-usaha')
                    ->assertSee('Profil Usaha')
                    ->assertValue('#business_contact', '081234567890') // Verify contact info is displayed clearly
                    ->type('business_name', 'Resto Berkah Baru')
                    ->type('business_type', 'Bakery Premium')
                    ->type('opening_start', '09:00')
                    ->type('opening_end', '21:00')
                    ->type('business_address', 'Jl. Berkah No. 12 Baru')
                    ->type('business_description', 'Toko makanan berkah premium.')
                    ->click('form[action*="profile"] button[type="submit"]')
                    ->waitForText('Profil usaha berhasil diperbarui.', 15)
                    ->assertSee('Profil usaha berhasil diperbarui.');

            $browser->blank();
        });

        // Verify changes in database
        $profile = UserProfile::where('user_id', $user->id)->first();
        $this->assertEquals('Resto Berkah Baru', $profile->business_name);
        $this->assertEquals('Bakery Premium', $profile->business_type);
        $this->assertEquals('09:00 - 21:00', $profile->business_opening_hours);
        $this->assertEquals('Jl. Berkah No. 12 Baru', $profile->business_address);
        $this->assertEquals('Toko makanan berkah premium.', $profile->business_description);
    }

    public function test_negative_update_mitra_profile_invalid_hours(): void
    {
        $email = 'mitrapbi29_neg_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $password = 'password123';

        $user = User::factory()->create([
            'role' => 'mitra',
            'name' => 'Mitra PBI 29 Neg',
            'email' => $email,
            'password' => Hash::make($password),
            'is_verified' => true,
        ]);

        UserProfile::create([
            'user_id' => $user->id,
            'business_name' => 'Resto Berkah Neg',
            'business_type' => 'Restoran',
            'business_address' => 'Jl. Berkah No. 12',
            'business_contact' => '081234567890',
            'business_opening_hours' => '08:00 - 20:00',
            'opening_hours' => '08:00 - 20:00',
            'business_description' => 'Toko makanan berkah.',
            'description' => 'Toko makanan berkah.',
        ]);

        $this->browse(function (Browser $browser) use ($email, $password) {
            $browser->driver->manage()->deleteAllCookies();

            // Login
            $browser->visit('/login')
                    ->select('user_type', 'mitra')
                    ->type('email', $email)
                    ->type('password', $password)
                    ->click('button[type="submit"]')
                    ->waitForLocation('/mitra', 15);

            // Go to profile-usaha
            $browser->visit('/mitra/profile-usaha')
                    ->assertPathIs('/mitra/profile-usaha')
                    // Input invalid hours (Close time 08:00, Open time 10:00)
                    ->type('opening_start', '10:00')
                    ->type('opening_end', '08:00')
                    ->click('form[action*="profile"] button[type="submit"]')
                    ->waitForText('Jam tutup harus lebih akhir dari jam buka.', 15)
                    ->assertSee('Jam tutup harus lebih akhir dari jam buka.');

            $browser->blank();
        });
    }
}
