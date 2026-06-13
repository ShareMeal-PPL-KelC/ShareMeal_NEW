<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Pbi5MitraInventoryTest extends TestCase
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
     * 1. Menguji validasi tambah produk flash sale dengan data kosong (Negative Test).
     */
    public function test_mitra_gagal_tambah_produk_karena_form_kosong(): void
    {
        $mitra = $this->createMitraWithProfile();

        $response = $this->actingAs($mitra)->post(route('mitra.inventory.store'), [
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
     * 2. Menguji fungsionalitas menambahkan produk flash sale (Positive Test).
     */
    public function test_mitra_berhasil_menambahkan_produk_flash_sale(): void
    {
        $mitra = $this->createMitraWithProfile();

        // Step 1: Tambah produk baru (status 'normal')
        $response1 = $this->actingAs($mitra)->post(route('mitra.inventory.store'), [
            'name' => 'Roti Cokelat Spesial',
            'category' => 'Bakery',
            'price' => 15000,
            'stock' => 10,
            'expires_at' => '2026-06-30T12:00',
            'pickup_start_time' => '09:00',
            'pickup_end_time' => '18:00',
            'status' => 'normal',
        ]);

        $response1->assertRedirect();
        $response1->assertSessionHas('success', 'Produk berhasil ditambahkan.');

        $this->assertDatabaseHas('products', [
            'user_id' => $mitra->id,
            'name' => 'Roti Cokelat Spesial',
            'status' => 'normal',
        ]);

        $product = Product::where('name', 'Roti Cokelat Spesial')->firstOrFail();

        // Step 2: Aktifkan flash sale untuk produk tersebut
        $response2 = $this->actingAs($mitra)->post(route('mitra.inventory.flash-sale', $product->id));

        $response2->assertRedirect();
        $response2->assertSessionHas('success', 'Flash sale diaktifkan.');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'flash-sale',
        ]);
    }
}
