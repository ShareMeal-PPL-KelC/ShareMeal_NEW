<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Donation;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pbi30PickupWindowTest extends TestCase
{
    use RefreshDatabase;

    public function test_mitra_can_store_product_with_pickup_times(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra', 'is_verified' => true]);

        $response = $this->actingAs($mitra)->post(route('mitra.inventory.store'), [
            'name' => 'Roti Cokelat',
            'category' => 'Bakery',
            'price' => 15000,
            'stock' => 10,
            'expires_at' => now()->addDays(1)->format('Y-m-d\TH:i'),
            'pickup_start_time' => '16:00',
            'pickup_end_time' => '18:00',
            'status' => 'normal',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('products', [
            'name' => 'Roti Cokelat',
            'pickup_start_time' => '16:00:00',
            'pickup_end_time' => '18:00:00',
        ]);
    }

    public function test_mitra_cannot_store_product_with_invalid_pickup_times(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra', 'is_verified' => true]);

        // End time before start time
        $response = $this->actingAs($mitra)->post(route('mitra.inventory.store'), [
            'name' => 'Roti Gagal',
            'category' => 'Bakery',
            'price' => 15000,
            'stock' => 10,
            'expires_at' => now()->addDays(1)->format('Y-m-d\TH:i'),
            'pickup_start_time' => '18:00',
            'pickup_end_time' => '16:00',
            'status' => 'normal',
        ]);

        $response->assertSessionHasErrors(['pickup_end_time']);
    }

    public function test_consumer_order_snapshots_pickup_times(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra', 'is_verified' => true]);
        $consumer = User::factory()->create(['role' => 'consumer']);

        $product = Product::create([
            'user_id' => $mitra->id,
            'name' => 'Roti Test',
            'category' => 'Bakery',
            'price' => 10000,
            'stock' => 10,
            'expires_at' => now()->addDays(1),
            'pickup_start_time' => '17:00',
            'pickup_end_time' => '19:00',
            'status' => 'normal',
        ]);

        $response = $this->actingAs($consumer)->post(route('consumer.checkout.store'), [
            'product_id' => $product->id,
            'mitra_id' => $mitra->id,
            'quantity' => 1,
            'price' => 10000,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'mitra_id' => $mitra->id,
            'pickup_start_time' => '17:00:00',
            'pickup_end_time' => '19:00:00',
        ]);
    }

    public function test_mitra_cannot_store_product_outside_operating_hours(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra', 'is_verified' => true]);
        $mitra->profile()->create([
            'business_opening_hours' => '08:00 - 20:00',
            'business_name' => 'Toko Test',
            'business_address' => 'Alamat Test',
            'business_contact' => '08123456789',
            'business_type' => 'Bakery',
            'business_description' => 'Deskripsi Test',
        ]);

        // Pickup start (21:00) is after closing (20:00)
        $response = $this->actingAs($mitra)->post(route('mitra.inventory.store'), [
            'name' => 'Roti Malam',
            'category' => 'Bakery',
            'price' => 15000,
            'stock' => 10,
            'expires_at' => now()->addDays(1)->format('Y-m-d\TH:i'),
            'pickup_start_time' => '21:00',
            'pickup_end_time' => '22:00',
            'status' => 'normal',
        ]);

        $response->assertSessionHasErrors(['pickup_start_time']);
    }

    public function test_display_name_prioritizes_business_name(): void
    {
        $mitra = User::factory()->create(['name' => 'User Biasa', 'role' => 'mitra']);
        $mitra->profile()->create(['business_name' => 'Roti Enak']);

        $this->assertEquals('Roti Enak', $mitra->displayName);
    }
}
