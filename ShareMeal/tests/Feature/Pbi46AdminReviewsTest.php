<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Models\Review;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pbi46AdminReviewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_reviews_page_with_stats(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $consumer = User::factory()->create(['role' => 'consumer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $mitra->profile()->create([
            'address' => 'Jl. Test Mitra',
            'business_hours' => '08:00 - 20:00'
        ]);

        $product = Product::create([
            'user_id' => $mitra->id,
            'name' => 'Roti Manis',
            'category' => 'Bakery',
            'price' => 15000,
            'stock' => 10,
            'expires_at' => now()->addDays(2),
            'pickup_start_time' => '08:00',
            'pickup_end_time' => '20:00'
        ]);

        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 15000,
            'status' => 'completed',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 15000,
        ]);

        Review::create([
            'order_id' => $order->id,
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'rating' => 5,
            'comment' => 'Sangat lezat dan memuaskan.',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reviews'));

        $response->assertStatus(200);
        $response->assertSee('Pemantauan Ulasan');
        $response->assertSee('Sangat lezat dan memuaskan.');
        $response->assertSee($consumer->name);
        $response->assertSee('Total Ulasan');
    }

    public function test_non_admin_cannot_view_reviews_page(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);

        $response = $this->actingAs($consumer)->get(route('admin.reviews'));

        // Middleware role redirects to their dashboard
        $response->assertRedirect(route('consumer.dashboard'));
    }
}
