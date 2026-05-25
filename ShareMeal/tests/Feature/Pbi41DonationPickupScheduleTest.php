<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pbi41DonationPickupScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_lembaga_can_claim_donation_with_pickup_time(): void
    {
        $lembaga = User::factory()->create(['role' => 'lembaga']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        
        $donation = Donation::create([
            'mitra_id' => $mitra->id,
            'title' => 'Nasi Bungkus Sisa Catering',
            'quantity' => 10,
            'unit' => 'bungkus',
            'status' => 'pending',
            'expires_at' => now()->addHours(5),
            'pickup_start_time' => '18:00:00',
            'pickup_end_time' => '20:00:00',
        ]);

        $response = $this->actingAs($lembaga)->post(route('lembaga.donations.claim', $donation->id), [
            'pickup_time' => '18:30',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        
        $donation->refresh();
        $this->assertEquals('claimed', $donation->status);
        $this->assertNotNull($donation->pickup_time);
        $this->assertEquals('18:30:00', $donation->pickup_time->toTimeString());
        $this->assertEquals($lembaga->id, $donation->lembaga_id);
    }

    public function test_pickup_time_is_required_to_claim_donation(): void
    {
        $lembaga = User::factory()->create(['role' => 'lembaga']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        
        $donation = Donation::create([
            'mitra_id' => $mitra->id,
            'title' => 'Roti Manis',
            'quantity' => 5,
            'unit' => 'pcs',
            'status' => 'pending',
            'expires_at' => now()->addHours(5),
        ]);

        $response = $this->actingAs($lembaga)->post(route('lembaga.donations.claim', $donation->id), [
            'pickup_time' => '', // Missing
        ]);

        $response->assertSessionHasErrors(['pickup_time']);
        $this->assertEquals('pending', $donation->fresh()->status);
    }
}
