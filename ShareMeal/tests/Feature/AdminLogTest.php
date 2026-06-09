<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AdminLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_logs_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        
        // Create a log
        AdminLog::create([
            'admin_id' => $admin->id,
            'action' => 'verify_approve',
            'details' => 'Menyetujui verifikasi berkas akun: Warung Makmur',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.logs'));

        $response->assertStatus(200);
        $response->assertSee('Menyetujui verifikasi berkas akun: Warung Makmur');
        $response->assertSee('Verifikasi Disetujui');
    }

    public function test_non_admin_cannot_view_logs_page(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);

        $response = $this->actingAs($consumer)->get(route('admin.logs'));

        $response->assertRedirect(route('consumer.dashboard'));
    }

    public function test_admin_actions_creates_log_entry(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $consumer = User::factory()->create(['role' => 'consumer']);

        // Warn user to test log creation
        $response = $this->actingAs($admin)->post(route('admin.users.warn', $consumer->id), [
            'reason' => 'Melanggar ketentuan SOP ShareMeal',
        ]);

        $response->assertRedirect();
        
        // Assert log was created
        $this->assertDatabaseHas('admin_logs', [
            'admin_id' => $admin->id,
            'action' => 'warn_user',
            'details' => 'Mengirim peringatan resmi kepada ' . $consumer->name . '. Alasan: Melanggar ketentuan SOP ShareMeal',
        ]);
    }
}
