<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pbi36DeliveryStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_mitra_can_update_order_to_ready(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra']);
        $consumer = User::factory()->create(['role' => 'consumer']);
        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 50000,
            'status' => 'pending',
            'receiving_method' => 'delivery'
        ]);

        $response = $this->actingAs($mitra)->post(route('mitra.orders.update-status', $order->id), [
            'status' => 'ready'
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('ready', $order->fresh()->status);
    }

    public function test_mitra_can_update_delivery_order_to_shipping(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra']);
        $consumer = User::factory()->create(['role' => 'consumer']);
        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 50000,
            'status' => 'ready',
            'receiving_method' => 'delivery'
        ]);

        $response = $this->actingAs($mitra)->post(route('mitra.orders.update-status', $order->id), [
            'status' => 'shipping'
        ]);

        $this->assertEquals('shipping', $order->fresh()->status);
    }

    public function test_mitra_can_cancel_order(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra']);
        $consumer = User::factory()->create(['role' => 'consumer']);
        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 50000,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($mitra)->post(route('mitra.orders.update-status', $order->id), [
            'status' => 'cancelled'
        ]);

        $this->assertEquals('cancelled', $order->fresh()->status);
    }

    public function test_mitra_cannot_update_others_order(): void
    {
        $mitra1 = User::factory()->create(['role' => 'mitra']);
        $mitra2 = User::factory()->create(['role' => 'mitra']);
        $consumer = User::factory()->create(['role' => 'consumer']);
        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra1->id,
            'total_amount' => 50000,
            'status' => 'pending'
        ]);

        $response = $this->actingAs($mitra2)->post(route('mitra.orders.update-status', $order->id), [
            'status' => 'ready'
        ]);

        $response->assertStatus(404);
        $this->assertEquals('pending', $order->fresh()->status);
    }
}
