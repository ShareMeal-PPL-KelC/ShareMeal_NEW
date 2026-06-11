<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('consumer.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_consumer_cannot_access_mitra_dashboard(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);
        
        $response = $this->actingAs($consumer)->get(route('mitra.dashboard'));
        $response->assertRedirect(route('consumer.dashboard'));
    }

    public function test_mitra_cannot_access_admin_dashboard(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra']);
        
        $response = $this->actingAs($mitra)->get(route('admin.dashboard'));
        $response->assertRedirect(route('mitra.dashboard'));
    }

    public function test_lembaga_cannot_access_consumer_dashboard(): void
    {
        $lembaga = User::factory()->create(['role' => 'lembaga']);
        
        $response = $this->actingAs($lembaga)->get(route('consumer.dashboard'));
        $response->assertRedirect(route('lembaga.dashboard'));
    }
}
