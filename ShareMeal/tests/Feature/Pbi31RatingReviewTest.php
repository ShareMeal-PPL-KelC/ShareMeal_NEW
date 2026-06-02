<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pbi31RatingReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_consumer_can_submit_review_for_order(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        
        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 50000,
            'status' => 'completed',
            'pickup_code' => 'TEST-123',
        ]);

        $response = $this->actingAs($consumer)->post(route('consumer.review.submit'), [
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Makanannya sangat enak dan masih hangat!',
        ]);

        $response->assertSessionHas('success', 'Terima kasih atas ulasan Anda!');
        
        $this->assertDatabaseHas('reviews', [
            'order_id' => $order->id,
            'customer_id' => $consumer->id,
            'rating' => 5,
            'comment' => 'Makanannya sangat enak dan masih hangat!',
        ]);
    }

    public function test_consumer_cannot_review_same_order_twice(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        
        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 50000,
            'status' => 'completed',
        ]);

        Review::create([
            'order_id' => $order->id,
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'rating' => 4,
        ]);

        $response = $this->actingAs($consumer)->post(route('consumer.review.submit'), [
            'order_id' => $order->id,
            'rating' => 5,
        ]);

        $response->assertSessionHas('error', 'Anda sudah memberikan ulasan untuk pesanan ini.');
        $this->assertEquals(1, Review::count());
    }
}
