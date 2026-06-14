<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LowStockNotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test notification is sent when stock drops below 5.
     */
    public function test_notification_sent_when_stock_drops_below_five(): void
    {
        Notification::fake();

        $mitra = User::factory()->create(['role' => 'mitra', 'is_verified' => true]);

        $product = Product::create([
            'user_id' => $mitra->id,
            'name' => 'Nasi Goreng Spesial',
            'category' => 'Makanan Utama',
            'price' => 15000,
            'stock' => 10,
            'expires_at' => now()->addDays(1),
            'pickup_start_time' => '17:00',
            'pickup_end_time' => '19:00',
            'status' => 'normal',
        ]);

        // Update stock to 3 (below 5)
        $product->update(['stock' => 3]);

        Notification::assertSentTo(
            $mitra,
            LowStockNotification::class,
            function ($notification) use ($product) {
                $data = $notification->toArray($product->user);
                return $data['title'] === 'Stok Makanan Menipis' &&
                       $data['type'] === 'warning' &&
                       $data['product_id'] === $product->id &&
                       str_contains($data['message'], 'Nasi Goreng Spesial') &&
                       str_contains($data['message'], 'tersisa 3');
            }
        );
    }

    /**
     * Test notification is saved to the database.
     */
    public function test_notification_saves_to_database(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra', 'is_verified' => true]);

        $product = Product::create([
            'user_id' => $mitra->id,
            'name' => 'Roti Bakar',
            'category' => 'Camilan',
            'price' => 12000,
            'stock' => 5,
            'expires_at' => now()->addDays(1),
            'pickup_start_time' => '17:00',
            'pickup_end_time' => '19:00',
            'status' => 'normal',
        ]);

        // Update stock to 2 (below 5)
        $product->update(['stock' => 2]);

        $notification = $mitra->unreadNotifications()
            ->where('type', LowStockNotification::class)
            ->first();

        $this->assertNotNull($notification);
        $this->assertEquals('Stok Makanan Menipis', $notification->data['title']);
        $this->assertStringContainsString('Roti Bakar', $notification->data['message']);
        $this->assertStringContainsString('tersisa 2', $notification->data['message']);
    }

    /**
     * Test notification is NOT sent when stock changes but remains above 5.
     */
    public function test_notification_not_sent_when_stock_remains_above_five(): void
    {
        Notification::fake();

        $mitra = User::factory()->create(['role' => 'mitra', 'is_verified' => true]);

        $product = Product::create([
            'user_id' => $mitra->id,
            'name' => 'Burger Cheese',
            'category' => 'Makanan Utama',
            'price' => 20000,
            'stock' => 10,
            'expires_at' => now()->addDays(1),
            'pickup_start_time' => '17:00',
            'pickup_end_time' => '19:00',
            'status' => 'normal',
        ]);

        // Update stock to 6 (still above 5)
        $product->update(['stock' => 6]);

        Notification::assertNotSentTo($mitra, LowStockNotification::class);
    }

    /**
     * Test notification is sent when consumer adds product to cart, dropping stock below 5.
     */
    public function test_notification_sent_when_consumer_adds_to_cart_drops_stock_below_five(): void
    {
        Notification::fake();

        $mitra = User::factory()->create(['role' => 'mitra', 'is_verified' => true]);
        $consumer = User::factory()->create(['role' => 'consumer']);

        $product = Product::create([
            'user_id' => $mitra->id,
            'name' => 'Kue Lumpur',
            'category' => 'Camilan',
            'price' => 5000,
            'stock' => 6,
            'expires_at' => now()->addDays(1),
            'pickup_start_time' => '17:00',
            'pickup_end_time' => '19:00',
            'status' => 'normal',
        ]);

        // Act as consumer and add 2 Kue Lumpur to cart, dropping stock to 4 (below 5)
        $response = $this->actingAs($consumer)->post(route('consumer.cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect();
        
        // Assert that the stock was reduced
        $this->assertEquals(4, $product->fresh()->stock);

        // Assert notification was sent to Mitra
        Notification::assertSentTo(
            $mitra,
            LowStockNotification::class,
            function ($notification) use ($product) {
                return $notification->toArray($product->user)['product_id'] === $product->id;
            }
        );
    }
}

