<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderDelayedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OrderDelayTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_delay_detection_marks_order_as_delayed(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra']);
        $consumer = User::factory()->create(['role' => 'consumer']);

        // 1. Order created in processing state
        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 50000,
            'status' => 'processing',
            'receiving_method' => 'delivery',
        ]);

        // Clear any notifications created during creation
        $consumer->notifications()->delete();

        // 2. Check within 5 minutes (should NOT trigger delay)
        Order::checkAndApplyDelays();
        $order->refresh();
        $this->assertFalse($order->is_delayed);
        $this->assertEquals(0, $consumer->notifications()->count());

        // 3. Set updated_at to 6 minutes ago
        $order->updated_at = now()->subMinutes(6);
        $order->saveQuietly();

        // 4. Run detection (should trigger delay)
        Order::checkAndApplyDelays();
        $order->refresh();

        $this->assertTrue($order->is_delayed);
        $this->assertNotNull($order->delayed_at);

        // 5. Verify notification was sent
        $notification = $consumer->unreadNotifications()->where('data->status', 'delayed')->first();
        $this->assertNotNull($notification);
        $this->assertEquals('Pesanan Terlambat (Delay)', $notification->data['title']);
        $this->assertStringContainsString('kemungkinan akan terlambat datang', $notification->data['message']);
    }

    public function test_manual_delay_by_mitra(): void
    {
        Notification::fake();

        $mitra = User::factory()->create(['role' => 'mitra']);
        $consumer = User::factory()->create(['role' => 'consumer']);

        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 50000,
            'status' => 'processing',
            'receiving_method' => 'pickup',
        ]);

        $response = $this->actingAs($mitra)->post(route('mitra.orders.delay', $order->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertTrue($order->is_delayed);
        $this->assertNotNull($order->delayed_at);

        Notification::assertSentTo($consumer, OrderDelayedNotification::class, function ($notification) use ($order) {
            $data = $notification->toArray($order->customer);
            return $data['status'] === 'delayed' &&
                   $data['title'] === 'Pesanan Terlambat (Delay)' &&
                   $data['message'] === 'Penyiapan pesanan Anda memerlukan waktu sedikit lebih lama karena antrean di toko sedang padat. Mohon kesediaannya untuk menunggu beberapa saat.';
        });
    }

    public function test_manual_delay_non_mitra_unauthorized(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra']);
        $consumer = User::factory()->create(['role' => 'consumer']);

        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 50000,
            'status' => 'processing',
        ]);

        // Try as consumer
        $response = $this->actingAs($consumer)->post(route('mitra.orders.delay', $order->id));
        $response->assertStatus(302); // Redirect back or to login due to middleware

        // Try as guest
        $this->post(route('mitra.orders.delay', $order->id))->assertRedirect();
    }

    public function test_manual_delay_wrong_status_failed(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra']);
        $consumer = User::factory()->create(['role' => 'consumer']);

        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 50000,
            'status' => 'pending', // Not processing
        ]);

        $response = $this->actingAs($mitra)->post(route('mitra.orders.delay', $order->id));
        $response->assertSessionHas('error');

        $order->refresh();
        $this->assertFalse($order->is_delayed);
    }
}
