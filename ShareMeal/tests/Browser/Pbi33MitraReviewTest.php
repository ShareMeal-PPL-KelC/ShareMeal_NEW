<?php

namespace Tests\Browser;

use App\Models\Order;
use App\Models\User;
use App\Models\Review;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

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

            $browser->visit('/login')
                    ->select('user_type', 'mitra')
                    ->type('email', $email)
                    ->type('password', $password)
                    ->click('button[type="submit"]')
                    ->waitForLocation('/mitra', 15)
                    ->visit('/mitra/reviews')
                    ->waitForText('Ulasan Konsumen', 15)
                    ->assertSee('4.5') // Average of 5 and 4
                    ->assertSee('2')   // Total reviews
                    ->assertSee('Perfect!')
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

            $browser->visit('/login')
                    ->select('user_type', 'consumer')
                    ->type('email', $email)
                    ->type('password', $password)
                    ->click('button[type="submit"]')
                    ->waitForLocation('/consumer', 15)
                    ->visit('/mitra/reviews')
                    // Assuming RoleMiddleware redirects unauthorized users to their dashboard
                    ->waitForLocation('/consumer', 15)
                    ->assertPathIs('/consumer');
        });
    }
}
