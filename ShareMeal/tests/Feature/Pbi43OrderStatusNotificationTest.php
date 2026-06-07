<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderStatusUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class Pbi43OrderStatusNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_status_updates_dispatches_notification_correctly(): void
    {
        Notification::fake();

        $mitra = User::factory()->create(['role' => 'mitra']);
        $consumer = User::factory()->create(['role' => 'consumer']);
        
        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 50000,
            'status' => 'pending',
        ]);

        $this->actingAs($mitra)->post(route('mitra.orders.update-status', $order->id), [
            'status' => 'ready',
        ]);

        Notification::assertSentTo($consumer, OrderStatusUpdated::class, function ($notification) use ($order) {
            $data = $notification->toArray($order->customer);
            return $data['status'] === 'ready' &&
                   $data['title'] === 'Update Status Pesanan' &&
                   $data['message'] === 'Pesanan Anda siap diambil!';
        });
    }

    public function test_notification_saves_to_database(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra']);
        $consumer = User::factory()->create(['role' => 'consumer']);
        
        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 50000,
            'status' => 'pending',
        ]);

        $this->actingAs($mitra)->post(route('mitra.orders.update-status', $order->id), [
            'status' => 'shipping',
        ]);

        $notification = $consumer->unreadNotifications()->where('data->status', 'shipping')->first();
        $this->assertNotNull($notification);
        $this->assertEquals('Update Status Pesanan', $notification->data['title']);
        $this->assertEquals('Pesanan Anda sedang dalam perjalanan oleh kurir mitra.', $notification->data['message']);
    }

    public function test_order_status_updates_dispatches_only_one_notification(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra']);
        $consumer = User::factory()->create(['role' => 'consumer']);
        
        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 50000,
            'status' => 'pending',
        ]);

        // Clean any notification created during Order::create
        $consumer->notifications()->delete();
        $this->assertEquals(0, $consumer->notifications()->count());

        $this->actingAs($mitra)->post(route('mitra.orders.update-status', $order->id), [
            'status' => 'ready',
        ]);

        // Assert exactly one notification is created for status change
        $this->assertEquals(1, $consumer->notifications()->count());
    }
}
