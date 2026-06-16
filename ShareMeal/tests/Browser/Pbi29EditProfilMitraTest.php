<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * PBI-29: Edit Profil Mitra
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
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
            // Mengunjungi halaman '/login'
            $browser->visit('/login')
                    // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/login'
                    ->assertPathIs('/login')
                    // Memilih opsi 'mitra' pada dropdown 'user_type'
                    ->select('user_type', 'mitra')
                    // Mengisi input field 'email'
                    ->type('email', $email)
                    // Mengisi input field 'password'
                    ->type('password', $password)
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('button[type="submit"]')
                    // Menunggu halaman berpindah ke rute '/mitra' (batas waktu 15 detik)
                    ->waitForLocation('/mitra', 15);

            // Go to profile-usaha
            // Mengunjungi halaman '/mitra/profile-usaha'
            $browser->visit('/mitra/profile-usaha')
                    // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/mitra/profile-usaha'
                    ->assertPathIs('/mitra/profile-usaha')
                    // Memastikan teks 'Profil Usaha' terlihat pada halaman browser
                    ->assertSee('Profil Usaha')
                    ->assertValue('#business_contact', '081234567890') // Verify contact info is displayed clearly
                    // Mengisi input 'business_name' dengan nilai 'Resto Berkah Baru'
                    ->type('business_name', 'Resto Berkah Baru')
                    // Mengisi input 'business_type' dengan nilai 'Bakery Premium'
                    ->type('business_type', 'Bakery Premium')
                    // Mengisi input 'opening_start' dengan nilai '09:00'
                    ->type('opening_start', '09:00')
                    // Mengisi input 'opening_end' dengan nilai '21:00'
                    ->type('opening_end', '21:00')
                    // Mengisi input 'business_address' dengan nilai 'Jl. Berkah No. 12 Baru'
                    ->type('business_address', 'Jl. Berkah No. 12 Baru')
                    // Mengisi input 'business_description' dengan nilai 'Toko makanan berkah premium.'
                    ->type('business_description', 'Toko makanan berkah premium.')
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('form[action*="profile"] button[type="submit"]')
                    // Menunggu teks 'Profil usaha berhasil diperbarui.' muncul di layar (batas waktu 15 detik)
                    ->waitForText('Profil usaha berhasil diperbarui.', 15)
                    // Memastikan teks 'Profil usaha berhasil diperbarui.' terlihat pada halaman browser
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
            // Mengunjungi halaman '/login'
            $browser->visit('/login')
                    // Memilih opsi 'mitra' pada dropdown 'user_type'
                    ->select('user_type', 'mitra')
                    // Mengisi input field 'email'
                    ->type('email', $email)
                    // Mengisi input field 'password'
                    ->type('password', $password)
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('button[type="submit"]')
                    // Menunggu halaman berpindah ke rute '/mitra' (batas waktu 15 detik)
                    ->waitForLocation('/mitra', 15);

            // Go to profile-usaha
            // Mengunjungi halaman '/mitra/profile-usaha'
            $browser->visit('/mitra/profile-usaha')
                    // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/mitra/profile-usaha'
                    ->assertPathIs('/mitra/profile-usaha')
                    // Input invalid hours (Close time 08:00, Open time 10:00)
                    // Mengisi input 'opening_start' dengan nilai '10:00'
                    ->type('opening_start', '10:00')
                    // Mengisi input 'opening_end' dengan nilai '08:00'
                    ->type('opening_end', '08:00')
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('form[action*="profile"] button[type="submit"]')
                    // Menunggu teks 'Jam tutup harus lebih akhir dari jam buka.' muncul di layar (batas waktu 15 detik)
                    ->waitForText('Jam tutup harus lebih akhir dari jam buka.', 15)
                    // Memastikan teks 'Jam tutup harus lebih akhir dari jam buka.' terlihat pada halaman browser
                    ->assertSee('Jam tutup harus lebih akhir dari jam buka.');

            $browser->blank();
        });
    }
}
