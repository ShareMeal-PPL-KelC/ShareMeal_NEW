<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pbi35ReceivingMethodTest extends TestCase
{
    use RefreshDatabase;

    public function test_consumer_can_choose_pickup_method(): void
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
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($consumer)->post(route('consumer.checkout.store'), [
            'product_id' => $product->id,
            'mitra_id' => $mitra->id,
            'quantity' => 2,
            'price' => 20000,
            'receiving_method' => 'pickup',
            'payment_method' => 'qris',
        ]);

        $response->assertRedirect(route('consumer.orders.active'));
        $this->assertDatabaseHas('orders', [
            'customer_id' => $consumer->id,
            'receiving_method' => 'pickup',
            'delivery_fee' => 0,
            'total_amount' => 40000, // 20000 * 2
        ]);
    }

    public function test_consumer_can_choose_delivery_method(): void
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
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($consumer)->post(route('consumer.checkout.store'), [
            'product_id' => $product->id,
            'mitra_id' => $mitra->id,
            'quantity' => 2,
            'price' => 20000,
            'receiving_method' => 'delivery',
            'payment_method' => 'qris',
        ]);

        $response->assertRedirect(route('consumer.orders.active'));
        $this->assertDatabaseHas('orders', [
            'customer_id' => $consumer->id,
            'receiving_method' => 'delivery',
            'delivery_fee' => 15000,
            'total_amount' => 55000, // (20000 * 2) + 15000
        ]);
    }

    public function test_consumer_cannot_choose_delivery_if_mitra_not_available(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $mitra->profile()->create([
            'can_delivery' => false,
            'delivery_fee' => 0,
        ]);

        $product = Product::factory()->create([
            'user_id' => $mitra->id,
            'price' => 20000,
            'stock' => 10,
            'status' => 'normal',
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($consumer)->from(route('consumer.checkout', ['product_id' => $product->id]))->post(route('consumer.checkout.store'), [
            'product_id' => $product->id,
            'mitra_id' => $mitra->id,
            'quantity' => 2,
            'price' => 20000,
            'receiving_method' => 'delivery',
            'payment_method' => 'qris',
        ]);

        $response->assertSessionHasErrors(['receiving_method']);
        $this->assertDatabaseMissing('orders', [
            'customer_id' => $consumer->id,
            'receiving_method' => 'delivery',
        ]);
    }
}
