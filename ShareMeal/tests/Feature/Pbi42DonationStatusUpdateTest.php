<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pbi42DonationStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_mitra_can_complete_claimed_donation(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra']);
        $lembaga = User::factory()->create(['role' => 'lembaga']);
        
        $donation = Donation::create([
            'mitra_id' => $mitra->id,
            'lembaga_id' => $lembaga->id,
            'title' => 'Roti Donasi',
            'quantity' => 10,
            'unit' => 'pcs',
            'status' => 'claimed',
            'claimed_at' => now(),
            'pickup_time' => now()->addHour(),
        ]);

        $response = $this->actingAs($mitra)->post(route('mitra.donations.complete', $donation->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $donation->refresh();
        $this->assertEquals('completed', $donation->status);
        $this->assertNotNull($donation->delivered_at);
        $this->assertEquals('delivered', $donation->tracking_status);
    }

    public function test_mitra_can_cancel_pending_donation(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra']);
        
        $donation = Donation::create([
            'mitra_id' => $mitra->id,
            'title' => 'Roti Donasi',
            'quantity' => 5,
            'unit' => 'pcs',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($mitra)->post(route('mitra.donations.cancel', $donation->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('donations', ['id' => $donation->id]);
    }

    public function test_mitra_cannot_complete_unclaimed_donation(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra']);
        $donation = Donation::create([
            'mitra_id' => $mitra->id,
            'title' => 'Nasi Bungkus',
            'quantity' => 10,
            'unit' => 'pcs',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($mitra)->post(route('mitra.donations.complete', $donation->id));

        $response->assertSessionHas('error');
        $this->assertEquals('pending', $donation->fresh()->status);
    }

    public function test_lembaga_can_complete_donation(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra']);
        $lembaga = User::factory()->create(['role' => 'lembaga']);
        $donation = Donation::create([
            'mitra_id' => $mitra->id,
            'lembaga_id' => $lembaga->id,
            'title' => 'Nasi Bungkus',
            'quantity' => 10,
            'unit' => 'pcs',
            'status' => 'prepared',
        ]);

        $response = $this->actingAs($lembaga)->post(route('lembaga.donations.complete', $donation->id));

        $response->assertSessionHas('success');
        $this->assertEquals('completed', $donation->fresh()->status);
        $this->assertEquals('delivered', $donation->fresh()->tracking_status);
        $this->assertNotNull($donation->fresh()->delivered_at);
    }

    public function test_lembaga_cannot_complete_claimed_donation(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra']);
        $lembaga = User::factory()->create(['role' => 'lembaga']);
        $donation = Donation::create([
            'mitra_id' => $mitra->id,
            'lembaga_id' => $lembaga->id,
            'title' => 'Nasi Bungkus',
            'quantity' => 10,
            'unit' => 'pcs',
            'status' => 'claimed',
        ]);

        $response = $this->actingAs($lembaga)->post(route('lembaga.donations.complete', $donation->id));

        $response->assertSessionHas('error');
        $this->assertEquals('claimed', $donation->fresh()->status);
    }
}
