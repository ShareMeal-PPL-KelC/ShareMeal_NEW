<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\Order;
use App\Models\User;
use App\Models\ProblemReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Pbi44ProblemReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_consumer_can_report_order_problem(): void
    {
        Storage::fake('public');

        $consumer = User::factory()->create(['role' => 'consumer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        
        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 50000,
            'status' => 'completed',
            'pickup_code' => 'TEST-123',
        ]);

        $response = $this->actingAs($consumer)->post(route('consumer.report.submit'), [
            'order_id' => $order->id,
            'issue_type' => 'bad_quality',
            'description' => 'Makanan sudah berbau asam saat diterima.',
            'evidence_image' => UploadedFile::fake()->create('evidence.jpg', 100, 'image/jpeg'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('problem_reports', [
            'reporter_id' => $consumer->id,
            'order_id' => $order->id,
            'issue_type' => 'bad_quality',
            'status' => 'pending',
        ]);

        $report = ProblemReport::first();
        $this->assertNotNull($report->evidence_image);
        Storage::disk('public')->assertExists($report->evidence_image);
    }

    public function test_lembaga_can_report_donation_problem(): void
    {
        $lembaga = User::factory()->create(['role' => 'lembaga']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        
        $donation = Donation::create([
            'mitra_id' => $mitra->id,
            'lembaga_id' => $lembaga->id,
            'title' => 'Nasi Kotak',
            'quantity' => 10,
            'unit' => 'box',
            'status' => 'completed',
        ]);

        $response = $this->actingAs($lembaga)->post(route('lembaga.report.submit'), [
            'donation_id' => $donation->id,
            'issue_type' => 'expired',
            'description' => 'Masa berlaku makanan sudah lewat 1 hari.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('problem_reports', [
            'reporter_id' => $lembaga->id,
            'donation_id' => $donation->id,
            'issue_type' => 'expired',
        ]);
    }
}
