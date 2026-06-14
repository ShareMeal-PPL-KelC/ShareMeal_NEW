<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pbi37DeliveryTimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_consumer_can_select_delivery_time_slot(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $mitra->profile()->create([
            'can_delivery' => true,
            'delivery_fee' => 15000,
        ]);

        $product = Product::factory()->create([
            'user_id' => $mitra->id,
            'price' => 20000,
            'stock' => 10,
            'status' => 'normal',
            'pickup_start_time' => '18:00:00',
            'pickup_end_time' => '20:00:00',
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($consumer)->post(route('consumer.checkout.store'), [
            'product_id' => $product->id,
            'mitra_id' => $mitra->id,
            'quantity' => 1,
            'price' => 20000,
            'receiving_method' => 'delivery',
            'delivery_time_slot' => '18:30 - 19:00',
            'payment_method' => 'qris',
        ]);

        $response->assertRedirect(route('consumer.orders.active'));
        $this->assertDatabaseHas('orders', [
            'customer_id' => $consumer->id,
            'receiving_method' => 'delivery',
            'delivery_time_slot' => '18:30 - 19:00',
        ]);
    }

    public function test_delivery_time_slot_is_required_for_delivery(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $mitra->profile()->create(['can_delivery' => true, 'delivery_fee' => 5000]);

        $product = Product::factory()->create(['user_id' => $mitra->id, 'status' => 'normal', 'expires_at' => now()->addDay()]);

        $response = $this->actingAs($consumer)->from(route('consumer.checkout', ['product_id' => $product->id]))->post(route('consumer.checkout.store'), [
            'product_id' => $product->id,
            'mitra_id' => $mitra->id,
            'quantity' => 1,
            'price' => 20000,
            'receiving_method' => 'delivery',
            'delivery_time_slot' => '', // Required
            'payment_method' => 'qris',
        ]);

        $response->assertSessionHasErrors(['delivery_time_slot']);
    }
}
