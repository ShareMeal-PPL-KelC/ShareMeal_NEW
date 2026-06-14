<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Notifications\ProductExpiredNotification;
use App\Services\AutoDonationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProductExpiredNotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test notification is sent when a product's expires_at passes and markExpiredProducts is called.
     */
    public function test_notification_sent_when_product_expires(): void
    {
        Notification::fake();

        $mitra = User::factory()->create(['role' => 'mitra', 'is_verified' => true]);

        $product = Product::create([
            'user_id' => $mitra->id,
            'name' => 'Susu Segar Murni',
            'category' => 'Minuman',
            'price' => 8000,
            'stock' => 5,
            'expires_at' => now()->subMinutes(10), // expired 10 minutes ago
            'pickup_start_time' => '17:00',
            'pickup_end_time' => '19:00',
            'status' => 'normal',
        ]);

        // Run markExpiredProducts
        app(AutoDonationService::class)->markExpiredProducts($mitra->id);

        // Assert stock became 0 and status became expired
        $product = $product->fresh();
        $this->assertEquals(0, $product->stock);
        $this->assertEquals('expired', $product->status);

        // Assert notification was sent
        Notification::assertSentTo(
            $mitra,
            ProductExpiredNotification::class,
            function ($notification) use ($product) {
                $data = $notification->toArray($product->user);
                return $data['title'] === 'Produk Kedaluwarsa' &&
                       $data['type'] === 'error' &&
                       $data['status'] === 'expired' &&
                       $data['product_id'] === $product->id &&
                       str_contains($data['message'], 'Susu Segar Murni');
            }
        );
    }
}
