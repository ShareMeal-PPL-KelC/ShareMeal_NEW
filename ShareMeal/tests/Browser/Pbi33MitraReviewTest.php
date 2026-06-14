<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pbi33MitraReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_mitra_can_access_reviews_page(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra']);
        $consumer = User::factory()->create(['role' => 'consumer']);

        // Create some reviews to test stats
        $order1 = Order::create(['customer_id' => $consumer->id, 'mitra_id' => $mitra->id, 'total_amount' => 10000, 'status' => 'completed']);
        $order2 = Order::create(['customer_id' => $consumer->id, 'mitra_id' => $mitra->id, 'total_amount' => 10000, 'status' => 'completed']);

        Review::create(['order_id' => $order1->id, 'customer_id' => $consumer->id, 'mitra_id' => $mitra->id, 'rating' => 5, 'comment' => 'Perfect!']);
        Review::create(['order_id' => $order2->id, 'customer_id' => $consumer->id, 'mitra_id' => $mitra->id, 'rating' => 4, 'comment' => 'Good!']);

        $response = $this->actingAs($mitra)->get(route('mitra.reviews'));

        $response->assertStatus(200);
        $response->assertSee('Ulasan Konsumen');
        $response->assertSee('4.5'); // Average of 5 and 4
        $response->assertSee('2'); // Total reviews
        $response->assertSee('Perfect!');
        $response->assertSee('Good!');
    }

    public function test_non_mitra_cannot_access_mitra_reviews_page(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);

        $response = $this->actingAs($consumer)->get(route('mitra.reviews'));

        // Assuming RoleMiddleware redirects unauthorized users to their dashboard
        $response->assertRedirect(route('consumer.dashboard'));
    }
}
