<?php

namespace Tests\Browser;

use App\Models\Order;
use App\Models\User;
use App\Models\Review;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class Pbi31RatingReviewTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_consumer_can_submit_review_for_order(): void
    {
        $email = 'consumer31_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $password = 'password123';

        $consumer = User::factory()->create([
            'role' => 'consumer',
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $mitra = User::factory()->create(['role' => 'mitra']);
        
        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 50000,
            'status' => 'completed',
            'pickup_code' => 'TEST-123',
            'confirmed_by_consumer' => true,
        ]);

        $this->browse(function (Browser $browser) use ($email, $password) {
            $browser->driver->manage()->deleteAllCookies();

            $browser->visit('/login')
                    ->select('user_type', 'consumer')
                    ->type('email', $email)
                    ->type('password', $password)
                    ->click('button[type="submit"]')
                    ->waitForLocation('/consumer', 15)
                    ->visit('/consumer/history')
                    ->waitFor('@tulis-ulasan-btn')
                    ->click('@tulis-ulasan-btn')
                    ->waitFor('textarea[name="comment"]')
                    ->click('@rating-5-btn')
                    ->type('comment', 'Makanannya sangat enak dan masih hangat!')
                    ->click('@submit-review-btn')
                    ->waitForText('Ulasan Terkirim!', 15)
                    ->assertSee('Ulasan Terkirim!');
        });
    }

    public function test_consumer_cannot_review_same_order_twice(): void
    {
        $email = 'consumer31_twice_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $password = 'password123';

        $consumer = User::factory()->create([
            'role' => 'consumer',
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $mitra = User::factory()->create(['role' => 'mitra']);
        
        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 50000,
            'status' => 'completed',
            'confirmed_by_consumer' => true,
        ]);

        Review::create([
            'order_id' => $order->id,
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'rating' => 4,
            'comment' => 'Bagus',
        ]);

        $this->browse(function (Browser $browser) use ($email, $password) {
            $browser->driver->manage()->deleteAllCookies();

            $browser->visit('/login')
                    ->select('user_type', 'consumer')
                    ->type('email', $email)
                    ->type('password', $password)
                    ->click('button[type="submit"]')
                    ->waitForLocation('/consumer', 15)
                    ->visit('/consumer/history')
                    // Since it has already been reviewed, the "tulis-ulasan-btn" should not be visible.
                    // Instead, we should see the text "PENILAIAN & ULASAN ANDA"
                    ->waitForText('PENILAIAN & ULASAN ANDA', 15)
                    ->assertDontSee('Berikan Penilaian & Ulasan');
        });
    }
}
