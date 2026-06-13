<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pbi39PaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    public function test_consumer_can_checkout_with_payment_method(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra']);
        $consumer = User::factory()->create(['role' => 'consumer']);
        $product = Product::factory()->create([
            'user_id' => $mitra->id,
            'stock' => 10,
            'status' => 'normal',
            'expires_at' => now()->addDays(1)
        ]);

        $response = $this->actingAs($consumer)->post(route('consumer.checkout.store'), [
            'product_id' => $product->id,
            'mitra_id' => $mitra->id,
            'quantity' => 1,
            'price' => $product->price,
            'receiving_method' => 'pickup',
            'payment_method' => 'qris'
        ]);

        $response->assertRedirect(route('consumer.orders.active'));
        $this->assertDatabaseHas('orders', [
            'customer_id' => $consumer->id,
            'payment_method' => 'qris'
        ]);
    }

    public function test_consumer_checkout_returns_json_for_ajax(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra']);
        $consumer = User::factory()->create(['role' => 'consumer']);
        $product = Product::factory()->create([
            'user_id' => $mitra->id,
            'stock' => 10,
            'status' => 'normal',
            'expires_at' => now()->addDays(1)
        ]);

        $response = $this->actingAs($consumer)->postJson(route('consumer.checkout.store'), [
            'product_id' => $product->id,
            'mitra_id' => $mitra->id,
            'quantity' => 1,
            'price' => $product->price,
            'receiving_method' => 'pickup',
            'payment_method' => 'gopay'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'order_id',
            'order_number',
            'pickup_code',
            'redirect_url'
        ]);
        $this->assertEquals('gopay', Order::first()->payment_method);
    }
}