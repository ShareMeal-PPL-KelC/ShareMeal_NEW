<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pbi32EditDeleteReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_consumer_can_edit_their_review(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        
        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 50000,
            'status' => 'completed',
        ]);

        $review = Review::create([
            'order_id' => $order->id,
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'rating' => 4,
            'comment' => 'Bagus',
        ]);

        $response = $this->actingAs($consumer)->put(route('consumer.review.update', $review->id), [
            'rating' => 5,
            'comment' => 'Sangat Bagus Sekali',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Ulasan Anda berhasil diperbarui.');

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 5,
            'comment' => 'Sangat Bagus Sekali',
        ]);
    }

    public function test_consumer_can_delete_their_review(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        
        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 50000,
            'status' => 'completed',
        ]);

        $review = Review::create([
            'order_id' => $order->id,
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'rating' => 4,
            'comment' => 'Bagus',
        ]);

        $response = $this->actingAs($consumer)->delete(route('consumer.review.delete', $review->id));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Ulasan Anda telah dihapus.');

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_consumer_cannot_edit_others_review(): void
    {
        $consumer1 = User::factory()->create(['role' => 'consumer']);
        $consumer2 = User::factory()->create(['role' => 'consumer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        
        $order = Order::create([
            'customer_id' => $consumer1->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 50000,
            'status' => 'completed',
        ]);

        $review = Review::create([
            'order_id' => $order->id,
            'customer_id' => $consumer1->id,
            'mitra_id' => $mitra->id,
            'rating' => 4,
            'comment' => 'Bagus',
        ]);

        $response = $this->actingAs($consumer2)->put(route('consumer.review.update', $review->id), [
            'rating' => 1,
            'comment' => 'Hacked',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 4,
        ]);
    }

    public function test_consumer_cannot_edit_review_after_two_minutes(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        
        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 50000,
            'status' => 'completed',
        ]);

        $review = Review::create([
            'order_id' => $order->id,
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'rating' => 4,
            'comment' => 'Bagus',
        ]);

        // Manually adjust the timestamp to 3 minutes ago
        $review->created_at = now()->subMinutes(3);
        $review->save();

        $response = $this->actingAs($consumer)->put(route('consumer.review.update', $review->id), [
            'rating' => 5,
            'comment' => 'Ubah ulasan kedaluwarsa',
        ]);

        $response->assertStatus(403);
    }

    public function test_consumer_cannot_delete_review_after_two_minutes(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        
        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 50000,
            'status' => 'completed',
        ]);

        $review = Review::create([
            'order_id' => $order->id,
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'rating' => 4,
            'comment' => 'Bagus',
        ]);

        // Manually adjust the timestamp to 3 minutes ago
        $review->created_at = now()->subMinutes(3);
        $review->save();

        $response = $this->actingAs($consumer)->delete(route('consumer.review.delete', $review->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }
}
