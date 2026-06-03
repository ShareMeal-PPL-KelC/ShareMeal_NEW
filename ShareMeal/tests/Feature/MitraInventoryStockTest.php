<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MitraInventoryStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_mitra_cannot_update_out_of_stock_product(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra', 'is_verified' => true]);
        
        $product = Product::create([
            'user_id' => $mitra->id,
            'name' => 'Roti Habis',
            'category' => 'Bakery',
            'price' => 10000,
            'stock' => 0,
            'expires_at' => now()->addDays(1),
            'pickup_start_time' => '17:00',
            'pickup_end_time' => '19:00',
            'status' => 'normal',
        ]);

        $response = $this->actingAs($mitra)->post(route('mitra.inventory.update', $product->id), [
            'name' => 'Roti Diedit',
            'category' => 'Bakery',
            'price' => 12000,
            'stock' => 5,
            'expires_at' => now()->addDays(1)->format('Y-m-d\TH:i'),
            'pickup_start_time' => '17:00',
            'pickup_end_time' => '19:00',
            'status' => 'normal',
        ]);

        $response->assertSessionHas('error', 'Produk sudah habis atau kedaluwarsa dan tidak dapat diubah.');
        
        $product->refresh();
        $this->assertSame('Roti Habis', $product->name);
        $this->assertSame(0, $product->stock);
    }

    public function test_mitra_cannot_trigger_flash_sale_on_out_of_stock_product(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra', 'is_verified' => true]);

        $product = Product::create([
            'user_id' => $mitra->id,
            'name' => 'Roti Habis FS',
            'category' => 'Bakery',
            'price' => 10000,
            'stock' => 0,
            'expires_at' => now()->addDays(1),
            'pickup_start_time' => '17:00',
            'pickup_end_time' => '19:00',
            'status' => 'normal',
        ]);

        $response = $this->actingAs($mitra)->post(route('mitra.inventory.flash-sale', $product->id));

        $response->assertSessionHas('error', 'Produk sudah habis atau kedaluwarsa.');
        
        $product->refresh();
        $this->assertSame('normal', $product->status);
    }

    public function test_mitra_cannot_toggle_donation_on_out_of_stock_product(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra', 'is_verified' => true]);

        $product = Product::create([
            'user_id' => $mitra->id,
            'name' => 'Roti Habis Donasi',
            'category' => 'Bakery',
            'price' => 10000,
            'stock' => 0,
            'expires_at' => now()->addDays(1),
            'pickup_start_time' => '17:00',
            'pickup_end_time' => '19:00',
            'status' => 'normal',
            'donatable' => false,
        ]);

        $response = $this->actingAs($mitra)->post(route('mitra.inventory.toggle-donation', $product->id));

        $response->assertSessionHas('error', 'Produk sudah habis atau kedaluwarsa.');
        
        $product->refresh();
        $this->assertEquals(0, $product->donatable);
    }
}
