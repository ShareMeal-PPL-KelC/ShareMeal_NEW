<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Notifications\PickupDeadlineReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PickupDeadlineReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_notification_when_pickup_deadline_is_55_minutes_away(): void
    {
        Notification::fake();
        \Illuminate\Support\Carbon::setTestNow('2026-06-03 12:00:00');

        $consumer = User::factory()->create(['role' => 'consumer']);
        $mitra = User::factory()->create(['role' => 'mitra']);

        // Create an order that expires in exactly 55 minutes (which is 5 minutes elapsed from a 60 minutes order)
        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 50000,
            'status' => 'ready',
            'receiving_method' => 'pickup',
            'pickup_end_time' => '12:55:00',
        ]);

        $this->artisan('sharemeal:send-pickup-reminders')
            ->expectsOutput('Berhasil mengirim 1 notifikasi peringatan batas ambil.')
            ->assertExitCode(0);

        Notification::assertSentTo($consumer, PickupDeadlineReminderNotification::class);
        
        \Illuminate\Support\Carbon::setTestNow(); // Reset test time
    }

    public function test_does_not_send_notification_when_pickup_deadline_is_not_55_minutes_away(): void
    {
        Notification::fake();
        \Illuminate\Support\Carbon::setTestNow('2026-06-03 12:00:00');

        $consumer = User::factory()->create(['role' => 'consumer']);
        $mitra = User::factory()->create(['role' => 'mitra']);

        // Create an order that expires in 60 minutes (0 minutes elapsed)
        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 50000,
            'status' => 'ready',
            'receiving_method' => 'pickup',
            'pickup_end_time' => '13:00:00',
        ]);

        $this->artisan('sharemeal:send-pickup-reminders')
            ->expectsOutput('Berhasil mengirim 0 notifikasi peringatan batas ambil.')
            ->assertExitCode(0);

        Notification::assertNotSentTo($consumer, PickupDeadlineReminderNotification::class);
        
        \Illuminate\Support\Carbon::setTestNow(); // Reset test time
    }
}
