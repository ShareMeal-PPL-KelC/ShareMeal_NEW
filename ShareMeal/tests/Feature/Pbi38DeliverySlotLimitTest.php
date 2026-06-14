<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pbi38DeliverySlotLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_mitra_can_set_delivery_slot_limit(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra']);
        $mitra->profile()->create([
            'business_name' => 'Toko Roti',
            'business_type' => 'Bakery',
            'business_address' => 'Jl. Mawar',
            'business_contact' => '081234567890',
            'business_opening_hours' => '08:00 - 20:00',
            'business_description' => 'Deskripsi usaha',
            'can_delivery' => true,
        ]);

        $response = $this->actingAs($mitra)->post(route('mitra.profile.update'), [
            'business_name' => 'Toko Roti Updated',
            'business_type' => 'Bakery',
            'business_address' => 'Jl. Mawar',
            'business_contact' => '081234567890',
            'opening_start' => '08:00',
            'opening_end' => '20:00',
            'business_description' => 'Deskripsi usaha',
            'can_delivery' => 1,
            'delivery_fee' => 5000,
            'delivery_slot_limit' => 5,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(5, $mitra->fresh()->profile->delivery_slot_limit);
    }

    public function test_consumer_cannot_book_when_slot_is_full(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $mitra->profile()->create([
            'can_delivery' => true,
            'delivery_fee' => 5000,
            'delivery_slot_limit' => 2, // Only 2 slots
        ]);

        $product = Product::factory()->create([
            'user_id' => $mitra->id,
            'status' => 'normal',
            'expires_at' => now()->addDay(),
            'pickup_start_time' => '18:00:00',
            'pickup_end_time' => '20:00:00',
        ]);

        $slotLabel = '18:00 - 18:30';

        // Fill up the slot
        for ($i = 0; $i < 2; $i++) {
            Order::create([
                'customer_id' => $consumer->id,
                'mitra_id' => $mitra->id,
                'total_amount' => 10000,
                'status' => 'pending',
                'pickup_code' => 'TEST-' . $i,
                'receiving_method' => 'delivery',
                'delivery_time_slot' => $slotLabel,
            ]);
        }

        // Try to book the 3rd one
        $response = $this->actingAs($consumer)->from(route('consumer.checkout', ['product_id' => $product->id]))->post(route('consumer.checkout.store'), [
            'product_id' => $product->id,
            'mitra_id' => $mitra->id,
            'quantity' => 1,
            'price' => 10000,
            'receiving_method' => 'delivery',
            'delivery_time_slot' => $slotLabel,
            'payment_method' => 'qris',
        ]);

        $response->assertSessionHasErrors(['delivery_time_slot']);
        $this->assertEquals(2, Order::where('delivery_time_slot', $slotLabel)->count());
    }

    public function test_checkout_page_shows_full_slots_as_disabled(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $mitra->profile()->create([
            'can_delivery' => true,
            'delivery_slot_limit' => 1,
        ]);

        $product = Product::factory()->create([
            'user_id' => $mitra->id,
            'status' => 'normal',
            'expires_at' => now()->addDay(),
            'pickup_start_time' => '18:00:00',
            'pickup_end_time' => '18:30:00', // Only 1 slot: 18:00 - 18:30
        ]);

        $slotLabel = '18:00 - 18:30';

        // Fill up the slot
        Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 10000,
            'status' => 'pending',
            'pickup_code' => 'TEST-1',
            'receiving_method' => 'delivery',
            'delivery_time_slot' => $slotLabel,
        ]);

        $response = $this->actingAs($consumer)->get(route('consumer.checkout', ['product_id' => $product->id]));

        $response->assertStatus(200);
        $response->assertSee('disabled');
        $response->assertSee('(Penuh)');
    }
}
