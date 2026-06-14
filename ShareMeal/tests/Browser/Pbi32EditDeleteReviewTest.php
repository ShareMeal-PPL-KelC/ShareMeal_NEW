<?php

namespace Tests\Browser;

use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class Pbi32EditDeleteReviewTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_consumer_can_edit_their_review(): void
    {
        $email = 'consumer32_' . time() . '_' . rand(1000, 9999) . '@example.com';
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

        $review = Review::create([
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
                    ->waitFor('@edit-ulasan-btn')
                    ->click('@edit-ulasan-btn')
                    ->waitFor('textarea[name="comment"]')
                    ->click('@rating-5-btn')
                    ->type('comment', 'Sangat Bagus Sekali')
                    ->click('@submit-review-btn')
                    ->waitForText('Ulasan Terkirim!', 15)
                    ->assertSee('Ulasan Terkirim!');
        });
    }

    public function test_consumer_cannot_edit_review_after_two_minutes(): void
    {
        $email = 'consumer32_lock_' . time() . '_' . rand(1000, 9999) . '@example.com';
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

        $review = Review::create([
            'order_id' => $order->id,
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'rating' => 4,
            'comment' => 'Bagus',
        ]);
        $review->created_at = now()->subMinutes(3);
        $review->save();

        $this->browse(function (Browser $browser) use ($email, $password) {
            $browser->driver->manage()->deleteAllCookies();

            $browser->visit('/login')
                    ->select('user_type', 'consumer')
                    ->type('email', $email)
                    ->type('password', $password)
                    ->click('button[type="submit"]')
                    ->waitForLocation('/consumer', 15)
                    ->visit('/consumer/history')
                    // Check that the review modification elements are locked
                    ->waitForText('TERKUNCI', 15)
                    ->assertSee('TERKUNCI')
                    ->assertDontSee('UBAH')
                    ->assertDontSee('HAPUS');
        });
    }
}
