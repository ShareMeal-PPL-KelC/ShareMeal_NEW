<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ProblemReport;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pbi47Pbi48AdminModerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_problem_reports(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $consumer = User::factory()->create(['role' => 'consumer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        
        ProblemReport::create([
            'reporter_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'issue_type' => 'bad_quality',
            'description' => 'Makanan basi',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.problem-reports.index'));

        $response->assertStatus(200);
        $response->assertSee('Makanan basi');
        $response->assertSee($mitra->name);
    }

    public function test_admin_can_warn_mitra_via_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mitra = User::factory()->create(['role' => 'mitra', 'warnings_count' => 0]);
        $consumer = User::factory()->create(['role' => 'consumer']);
        
        $report = ProblemReport::create([
            'reporter_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'issue_type' => 'bad_quality',
            'description' => 'Basi parah',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.problem-reports.warn', $report->id));

        $response->assertRedirect();
        $mitra->refresh();
        $this->assertEquals(1, $mitra->warnings_count);
        $this->assertEquals('warned', $mitra->status);
        $this->assertEquals('resolved', $report->fresh()->status);
    }

    public function test_admin_can_block_mitra_via_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $consumer = User::factory()->create(['role' => 'consumer']);
        
        $report = ProblemReport::create([
            'reporter_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'issue_type' => 'expired',
            'description' => 'Sangat berbahaya',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.problem-reports.block', $report->id));

        $response->assertRedirect();
        $mitra->refresh();
        $this->assertEquals('blocked', $mitra->status);
        $this->assertNotNull($mitra->blocked_at);
        $this->assertEquals('resolved', $report->fresh()->status);
    }

    public function test_admin_can_dismiss_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $consumer = User::factory()->create(['role' => 'consumer']);
        
        $report = ProblemReport::create([
            'reporter_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'issue_type' => 'other',
            'description' => 'Salah lapor',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.problem-reports.dismiss', $report->id));

        $response->assertRedirect();
        $this->assertEquals('dismissed', $report->fresh()->status);
    }
}
