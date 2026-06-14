<?php

namespace Tests\Feature;

use App\Models\ProblemReport;
use App\Models\User;
use App\Models\Order;
use App\Notifications\ProblemReportResolvedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminProblemReportNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $consumer;
    protected $mitra;
    protected $order;
    protected $report;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->consumer = User::factory()->create(['role' => 'consumer']);
        $this->mitra = User::factory()->create(['role' => 'mitra', 'name' => 'Kantin Sehat']);
        
        $this->order = Order::create([
            'customer_id' => $this->consumer->id,
            'mitra_id' => $this->mitra->id,
            'total_amount' => 30000,
            'status' => 'completed',
        ]);

        $this->report = ProblemReport::create([
            'reporter_id' => $this->consumer->id,
            'mitra_id' => $this->mitra->id,
            'order_id' => $this->order->id,
            'issue_type' => 'bad_quality',
            'description' => 'Makanan sudah basi.',
            'status' => 'pending',
        ]);
    }

    /**
     * Test notification is sent to the reporter when admin dismisses the report.
     */
    public function test_reporter_notified_when_admin_dismisses_report(): void
    {
        Notification::fake();

        $response = $this->actingAs($this->admin)->post(route('admin.problem-reports.dismiss', $this->report->id));

        $response->assertRedirect();
        $this->assertEquals('dismissed', $this->report->fresh()->status);

        Notification::assertSentTo(
            $this->consumer,
            ProblemReportResolvedNotification::class,
            function ($notification) {
                $data = $notification->toArray($this->consumer);
                return $data['title'] === 'Laporan Masalah Ditindaklanjuti' &&
                       $data['type'] === 'info' &&
                       $data['status'] === 'dismissed' &&
                       $data['report_id'] === $this->report->id &&
                       str_contains($data['message'], 'Kantin Sehat') &&
                       str_contains($data['message'], 'diabaikan');
            }
        );
    }

    /**
     * Test notification is sent to the reporter when admin warns the Mitra.
     */
    public function test_reporter_notified_when_admin_warns_mitra(): void
    {
        Notification::fake();

        $response = $this->actingAs($this->admin)->post(route('admin.problem-reports.warn', $this->report->id), [
            'reason' => 'Melanggar kebersihan makanan.',
        ]);

        $response->assertRedirect();
        $this->assertEquals('resolved', $this->report->fresh()->status);

        Notification::assertSentTo(
            $this->consumer,
            ProblemReportResolvedNotification::class,
            function ($notification) {
                $data = $notification->toArray($this->consumer);
                return $data['title'] === 'Laporan Masalah Ditindaklanjuti' &&
                       $data['type'] === 'success' &&
                       $data['status'] === 'resolved' &&
                       $data['report_id'] === $this->report->id &&
                       str_contains($data['message'], 'Kantin Sehat') &&
                       str_contains($data['message'], 'sanksi peringatan');
            }
        );
    }

    /**
     * Test notification is sent to the reporter when admin blocks the Mitra.
     */
    public function test_reporter_notified_when_admin_blocks_mitra(): void
    {
        Notification::fake();

        $response = $this->actingAs($this->admin)->post(route('admin.problem-reports.block', $this->report->id), [
            'reason' => 'Penipuan makanan berat.',
        ]);

        $response->assertRedirect();
        $this->assertEquals('resolved', $this->report->fresh()->status);

        Notification::assertSentTo(
            $this->consumer,
            ProblemReportResolvedNotification::class,
            function ($notification) {
                $data = $notification->toArray($this->consumer);
                return $data['title'] === 'Laporan Masalah Ditindaklanjuti' &&
                       $data['type'] === 'success' &&
                       $data['status'] === 'resolved' &&
                       $data['report_id'] === $this->report->id &&
                       str_contains($data['message'], 'Kantin Sehat') &&
                       str_contains($data['message'], 'memblokir akun Mitra');
            }
        );
    }
}
