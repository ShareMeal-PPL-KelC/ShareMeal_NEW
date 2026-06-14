<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pbi45CriticalNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_warned_user_sees_critical_banner(): void
    {
        $user = User::factory()->create([
            'role' => 'consumer',
            'status' => 'warned'
        ]);

        $response = $this->actingAs($user)->get(route('consumer.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Peringatan: Akun Anda mendapatkan peringatan');
    }

    public function test_blocked_user_sees_critical_banner(): void
    {
        $user = User::factory()->create([
            'role' => 'consumer',
            'status' => 'blocked'
        ]);

        $response = $this->actingAs($user)->get(route('consumer.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('AKSES DIBATASI: Akun Anda telah diblokir');
    }

    public function test_user_can_view_all_notifications_page(): void
    {
        $user = User::factory()->create(['role' => 'consumer']);
        
        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertStatus(200);
        $response->assertSee('Semua Notifikasi');
    }

    public function test_user_can_mark_single_notification_as_read(): void
    {
        $user = User::factory()->create(['role' => 'consumer']);
        
        // Create a manual notification record
        $notificationId = \Illuminate\Support\Str::uuid()->toString();
        \Illuminate\Support\Facades\DB::table('notifications')->insert([
            'id' => $notificationId,
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $user->id,
            'data' => json_encode(['title' => 'Test', 'message' => 'Test message']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertEquals(1, $user->unreadNotifications()->count());

        $response = $this->actingAs($user)->post(route('notifications.markSingleRead', $notificationId));

        $response->assertRedirect();
        $this->assertEquals(0, $user->fresh()->unreadNotifications()->count());
    }
}