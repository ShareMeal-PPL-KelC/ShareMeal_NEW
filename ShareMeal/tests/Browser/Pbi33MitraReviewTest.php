<?php

namespace Tests\Browser;

use App\Models\Order;
use App\Models\User;
use App\Models\Review;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * PBI-33: Mitra Review
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
class Pbi33MitraReviewTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Helper method untuk membuat Mitra dengan profil lengkap.
     */
    private function createMitraWithProfile(string $email, string $password): User
    {
        $mitra = User::factory()->create([
            'role' => 'mitra',
            'status' => 'active',
            'email' => $email,
            'password' => Hash::make($password),
            'is_verified' => true,
        ]);

        $mitra->profile()->create([
            'business_name' => 'Resto Ulasan',
            'business_type' => 'Bakery',
            'business_address' => 'Jl. Pahlawan No. 45',
            'business_contact' => '081234567890',
            'business_opening_hours' => '08:00 - 20:00',
            'opening_hours' => '08:00 - 20:00',
            'description' => 'Menyediakan kue dan roti segar setiap hari.',
            'business_description' => 'Menyediakan kue dan roti segar setiap hari.',
            'is_verified' => true,
        ]);

        return $mitra;
    }

    public function test_mitra_can_access_reviews_page(): void
    {
        $email = 'mitra33_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $password = 'password123';
        $mitra = $this->createMitraWithProfile($email, $password);

        $consumer = User::factory()->create(['role' => 'consumer']);

        // Create some reviews to test stats
        $order1 = Order::create(['customer_id' => $consumer->id, 'mitra_id' => $mitra->id, 'total_amount' => 10000, 'status' => 'completed', 'confirmed_by_consumer' => true]);
        $order2 = Order::create(['customer_id' => $consumer->id, 'mitra_id' => $mitra->id, 'total_amount' => 10000, 'status' => 'completed', 'confirmed_by_consumer' => true]);

        Review::create(['order_id' => $order1->id, 'customer_id' => $consumer->id, 'mitra_id' => $mitra->id, 'rating' => 5, 'comment' => 'Perfect!']);
        Review::create(['order_id' => $order2->id, 'customer_id' => $consumer->id, 'mitra_id' => $mitra->id, 'rating' => 4, 'comment' => 'Good!']);

        $this->browse(function (Browser $browser) use ($email, $password) {
            $browser->driver->manage()->deleteAllCookies();

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
                    ->waitForLocation('/mitra', 15)
                    // Mengunjungi halaman '/mitra/reviews'
                    ->visit('/mitra/reviews')
                    // Menunggu teks 'Ulasan Konsumen' muncul di layar (batas waktu 15 detik)
                    ->waitForText('Ulasan Konsumen', 15)
                    // Memastikan teks '4.5' terlihat pada halaman browser
                    ->assertSee('4.5') // Average of 5 and 4
                    // Memastikan teks '2' terlihat pada halaman browser
                    ->assertSee('2')   // Total reviews
                    // Memastikan teks 'Perfect!' terlihat pada halaman browser
                    ->assertSee('Perfect!')
                    // Memastikan teks 'Good!' terlihat pada halaman browser
                    ->assertSee('Good!');
        });
    }

    public function test_non_mitra_cannot_access_mitra_reviews_page(): void
    {
        $email = 'consumer33_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $password = 'password123';

        $consumer = User::factory()->create([
            'role' => 'consumer',
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->browse(function (Browser $browser) use ($email, $password) {
            $browser->driver->manage()->deleteAllCookies();

            // Mengunjungi halaman '/login'
            $browser->visit('/login')
                    // Memilih opsi 'consumer' pada dropdown 'user_type'
                    ->select('user_type', 'consumer')
                    // Mengisi input field 'email'
                    ->type('email', $email)
                    // Mengisi input field 'password'
                    ->type('password', $password)
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('button[type="submit"]')
                    // Menunggu halaman berpindah ke rute '/consumer' (batas waktu 15 detik)
                    ->waitForLocation('/consumer', 15)
                    // Mengunjungi halaman '/mitra/reviews'
                    ->visit('/mitra/reviews')
                    // Assuming RoleMiddleware redirects unauthorized users to their dashboard
                    // Menunggu halaman berpindah ke rute '/consumer' (batas waktu 15 detik)
                    ->waitForLocation('/consumer', 15)
                    // Memastikan sistem berhasil mengarahkan pengguna ke halaman '/consumer'
                    ->assertPathIs('/consumer');
        });
    }
}
