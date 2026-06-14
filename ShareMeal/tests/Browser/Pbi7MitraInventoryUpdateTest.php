<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pbi7MitraInventoryUpdateTest extends TestCase
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
     * 1. Menguji validasi update produk dengan data kosong (Negative Test).
     */
    public function test_mitra_gagal_update_produk_karena_form_kosong(): void
    {
        $mitra = $this->createMitraWithProfile();

        // Seed produk untuk diedit
        $product = Product::factory()->create([
            'user_id' => $mitra->id,
            'name' => 'Roti Cokelat Lama',
            'category' => 'Bakery',
            'price' => 15000,
            'discount_price' => 10500,
            'stock' => 10,
            'status' => 'flash-sale',
            'expires_at' => now()->addDays(2),
            'pickup_start_time' => '09:00',
            'pickup_end_time' => '18:00',
        ]);

        $response = $this->actingAs($mitra)->post(route('mitra.inventory.update', $product->id), [
            'name' => '',
            'category' => '',
            'price' => '',
            'stock' => '',
            'expires_at' => '',
            'pickup_start_time' => '',
            'pickup_end_time' => '',
            'status' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'category', 'price', 'stock', 'expires_at', 'pickup_start_time', 'pickup_end_time', 'status']);
    }

    /**
     * 2. Menguji fungsionalitas update informasi produk flash sale (Positive Test).
     */
    public function test_mitra_berhasil_update_informasi_produk_flash_sale(): void
    {
        $mitra = $this->createMitraWithProfile();

        // Seed produk untuk diedit
        $product = Product::factory()->create([
            'user_id' => $mitra->id,
            'name' => 'Roti Tawar Lama',
            'category' => 'Bakery',
            'price' => 12000,
            'discount_price' => 8400,
            'stock' => 10,
            'status' => 'flash-sale',
            'expires_at' => now()->addDays(2),
            'pickup_start_time' => '09:00',
            'pickup_end_time' => '18:00',
        ]);

        $response = $this->actingAs($mitra)->post(route('mitra.inventory.update', $product->id), [
            'name' => 'Roti Tawar Gandum Baru',
            'category' => 'Bakery',
            'price' => 15000,
            'discount_price' => 10500,
            'stock' => 25,
            'expires_at' => '2026-06-30T12:00',
            'pickup_start_time' => '09:00',
            'pickup_end_time' => '18:00',
            'status' => 'flash-sale',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Informasi produk berhasil diperbarui.');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Roti Tawar Gandum Baru',
            'stock' => 25,
            'price' => 15000,
            'status' => 'flash-sale',
        ]);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
            'name' => 'Roti Tawar Lama',
        ]);
    }
}
