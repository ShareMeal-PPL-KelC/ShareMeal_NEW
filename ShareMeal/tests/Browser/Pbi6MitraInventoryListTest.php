<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pbi6MitraInventoryListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper method untuk membuat Mitra dengan profil lengkap.
     */
    private function createMitraWithProfile(): User
    {
        $mitra = User::factory()->create([
            'role' => 'mitra',
            'status' => 'active',
            'is_verified' => true,
        ]);

        $mitra->profile()->create([
            'business_name' => 'Resto Flash Sale',
            'business_type' => 'Bakery',
            'business_address' => 'Jl. Pahlawan No. 45',
            'business_contact' => '081234567890',
            'business_opening_hours' => '08:00 - 20:00',
            'opening_hours' => '08:00 - 20:00',
            'description' => 'Menyediakan kue dan roti segar setiap hari.',
            'is_verified' => true,
        ]);

        return $mitra;
    }

    /**
     * 1. Menguji kondisi daftar produk ketika belum ada data (Negative / Empty State).
     */
    public function test_mitra_melihat_tampilan_kosong_saat_tidak_ada_produk(): void
    {
        $mitra = $this->createMitraWithProfile();

        $response = $this->actingAs($mitra)->get(route('mitra.inventory'));

        $response->assertStatus(200);
        $response->assertSee('Manajemen Inventaris Surplus');
        $response->assertDontSee('Roti Keju Spesial');
        
        $response->assertViewHas('products', function ($products) {
            return $products->isEmpty();
        });
    }

    /**
     * 2. Menguji tampilan daftar produk flash sale (Positive Test).
     */
    public function test_mitra_dapat_melihat_daftar_produk_flash_sale(): void
    {
        $mitra = $this->createMitraWithProfile();

        // Seeding produk
        Product::factory()->create([
            'user_id' => $mitra->id,
            'name' => 'Roti Keju Spesial',
            'category' => 'Bakery',
            'price' => 15000,
            'discount_price' => 10500,
            'stock' => 15,
            'status' => 'flash-sale',
            'expires_at' => now()->addDays(2),
            'pickup_start_time' => '09:00',
            'pickup_end_time' => '18:00',
        ]);

        $response = $this->actingAs($mitra)->get(route('mitra.inventory'));

        $response->assertStatus(200);
        $response->assertSee('Roti Keju Spesial');
        
        $response->assertViewHas('products', function ($products) {
            return $products->contains(fn ($p) => 
                $p->name === 'Roti Keju Spesial' && 
                $p->status === 'flash-sale' && 
                $p->stock === 15
            );
        });
    }
}
