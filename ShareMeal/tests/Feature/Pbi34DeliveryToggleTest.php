<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pbi34DeliveryToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_mitra_can_toggle_delivery_service_and_set_fee(): void
    {
        $mitra = User::factory()->create([
            'role' => 'mitra',
            'status' => 'active',
            'is_verified' => true,
        ]);

        // 1. Activate Delivery
        $response = $this->actingAs($mitra)->post(route('mitra.profile.update'), [
            'business_name' => 'Roti Makmur',
            'business_type' => 'Bakery',
            'business_address' => 'Jl. Roti No. 10',
            'business_contact' => '081234567890',
            'opening_start' => '08:00',
            'opening_end' => '20:00',
            'business_description' => 'Menjual roti segar.',
            'can_delivery' => 1,
            'delivery_fee' => 15000,
        ]);

        $response->assertRedirect();
        $mitra->refresh();

        $this->assertTrue($mitra->profile->can_delivery);
        $this->assertEquals(15000, $mitra->profile->delivery_fee);

        // 2. Deactivate Delivery
        $response = $this->actingAs($mitra)->post(route('mitra.profile.update'), [
            'business_name' => 'Roti Makmur',
            'business_type' => 'Bakery',
            'business_address' => 'Jl. Roti No. 10',
            'business_contact' => '081234567890',
            'opening_start' => '08:00',
            'opening_end' => '20:00',
            'business_description' => 'Menjual roti segar.',
            'can_delivery' => 0,
        ]);

        $mitra->refresh();
        $this->assertFalse($mitra->profile->can_delivery);
    }

    public function test_delivery_fee_is_required_when_delivery_is_enabled(): void
    {
        $mitra = User::factory()->create([
            'role' => 'mitra',
            'status' => 'active',
            'is_verified' => true,
        ]);

        $response = $this->actingAs($mitra)->post(route('mitra.profile.update'), [
            'business_name' => 'Roti Makmur',
            'business_type' => 'Bakery',
            'business_address' => 'Jl. Roti No. 10',
            'business_contact' => '081234567890',
            'opening_start' => '08:00',
            'opening_end' => '20:00',
            'business_description' => 'Menjual roti segar.',
            'can_delivery' => 1,
            'delivery_fee' => '', // Should be required
        ]);

        $response->assertSessionHasErrors(['delivery_fee']);
    }
}
