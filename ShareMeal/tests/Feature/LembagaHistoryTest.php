<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LembagaHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_lembaga_history(): void
    {
        $response = $this->get(route('lembaga.history'));
        $response->assertRedirect('/login');
    }

    public function test_consumer_cannot_access_lembaga_history(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);
        $response = $this->actingAs($consumer)->get(route('lembaga.history'));
        $response->assertRedirect(route('consumer.dashboard'));
    }

    public function test_lembaga_can_access_history_and_see_completed_donations(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra']);
        $lembaga = User::factory()->create(['role' => 'lembaga']);

        // Completed donation for this lembaga
        $donationCompleted = Donation::create([
            'mitra_id' => $mitra->id,
            'lembaga_id' => $lembaga->id,
            'title' => 'Nasi Bungkus Enak',
            'quantity' => 10,
            'unit' => 'pcs',
            'status' => 'completed',
            'claimed_at' => now(),
            'delivered_at' => now(),
        ]);

        // Claimed (not completed) donation for this lembaga
        $donationClaimed = Donation::create([
            'mitra_id' => $mitra->id,
            'lembaga_id' => $lembaga->id,
            'title' => 'Roti Manis',
            'quantity' => 5,
            'unit' => 'pcs',
            'status' => 'claimed',
            'claimed_at' => now(),
        ]);

        // Completed donation for another lembaga
        $anotherLembaga = User::factory()->create(['role' => 'lembaga']);
        $donationOther = Donation::create([
            'mitra_id' => $mitra->id,
            'lembaga_id' => $anotherLembaga->id,
            'title' => 'Donasi Lain',
            'quantity' => 8,
            'unit' => 'pcs',
            'status' => 'completed',
            'claimed_at' => now(),
            'delivered_at' => now(),
        ]);

        $response = $this->actingAs($lembaga)
            ->withSession(['sharemeal.current_user_id' => $lembaga->id])
            ->get(route('lembaga.history'));

        $response->assertStatus(200);
        $response->assertSee('Riwayat Penerimaan Donasi');
        $response->assertSee('Nasi Bungkus Enak');
        $response->assertDontSee('Roti Manis');
        $response->assertDontSee('Donasi Lain');
    }
}
