<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\CartItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartQuantityUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_update_cart_quantity_increment(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $product = Product::factory()->create([
            'user_id' => $mitra->id,
            'price' => 10000,
            'stock' => 8, // remaining stock
            'status' => 'normal',
            'expires_at' => now()->addMinutes(10),
        ]);

        $cartItem = CartItem::create([
            'user_id' => $consumer->id,
            'product_id' => $product->id,
            'quantity' => 2, // user has 2 in cart
            'expires_at' => now()->addMinutes(5),
        ]);

        // total stock is 10. Let's increment by 3 (new quantity 5)
        $response = $this->actingAs($consumer)->post(route('consumer.cart.update', $cartItem->id), [
            'quantity' => 5,
        ]);

        $response->assertRedirect(route('consumer.cart.index'));
        
        // Assert cart item is updated to 5
        $this->assertEquals(5, $cartItem->fresh()->quantity);
        // Assert product stock is decremented by 3 (8 - 3 = 5)
        $this->assertEquals(5, $product->fresh()->stock);
    }

    public function test_cannot_increment_beyond_total_stock(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $product = Product::factory()->create([
            'user_id' => $mitra->id,
            'price' => 10000,
            'stock' => 1, // remaining stock
            'status' => 'normal',
            'expires_at' => now()->addMinutes(10),
        ]);

        $cartItem = CartItem::create([
            'user_id' => $consumer->id,
            'product_id' => $product->id,
            'quantity' => 2, // user has 2 in cart. Total stock of product is 3
            'expires_at' => now()->addMinutes(5),
        ]);

        // Attempting to set quantity to 4 (exceeding total stock of 3)
        $response = $this->actingAs($consumer)->from(route('consumer.cart.index'))->post(route('consumer.cart.update', $cartItem->id), [
            'quantity' => 4,
        ]);

        $response->assertRedirect(route('consumer.cart.index'));
        $response->assertSessionHas('error');
        
        // Quantities should not change
        $this->assertEquals(2, $cartItem->fresh()->quantity);
        $this->assertEquals(1, $product->fresh()->stock);
    }

    public function test_can_update_cart_quantity_decrement(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $product = Product::factory()->create([
            'user_id' => $mitra->id,
            'price' => 10000,
            'stock' => 3,
            'status' => 'normal',
            'expires_at' => now()->addMinutes(10),
        ]);

        $cartItem = CartItem::create([
            'user_id' => $consumer->id,
            'product_id' => $product->id,
            'quantity' => 4,
            'expires_at' => now()->addMinutes(5),
        ]);

        // Decrement by 2 (new quantity 2)
        $response = $this->actingAs($consumer)->post(route('consumer.cart.update', $cartItem->id), [
            'quantity' => 2,
        ]);

        $response->assertRedirect(route('consumer.cart.index'));
        
        $this->assertEquals(2, $cartItem->fresh()->quantity);
        // Assert product stock is incremented by 2 (3 + 2 = 5)
        $this->assertEquals(5, $product->fresh()->stock);
    }

    public function test_setting_quantity_to_zero_removes_item(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $product = Product::factory()->create([
            'user_id' => $mitra->id,
            'price' => 10000,
            'stock' => 2,
            'status' => 'normal',
            'expires_at' => now()->addMinutes(10),
        ]);

        $cartItem = CartItem::create([
            'user_id' => $consumer->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'expires_at' => now()->addMinutes(5),
        ]);

        // Set quantity to 0
        $response = $this->actingAs($consumer)->post(route('consumer.cart.update', $cartItem->id), [
            'quantity' => 0,
        ]);

        $response->assertRedirect(route('consumer.cart.index'));
        
        // Assert cart item is deleted
        $this->assertNull(CartItem::find($cartItem->id));
        // Assert stock returned to product (2 + 3 = 5)
        $this->assertEquals(5, $product->fresh()->stock);
    }

    public function test_updating_expired_cart_item_redirects_with_error(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $product = Product::factory()->create([
            'user_id' => $mitra->id,
            'price' => 10000,
            'stock' => 5,
            'status' => 'normal',
            'expires_at' => now()->addMinutes(10),
        ]);

        $cartItem = CartItem::create([
            'user_id' => $consumer->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'expires_at' => now()->subMinutes(1), // already expired
        ]);

        $response = $this->actingAs($consumer)->post(route('consumer.cart.update', $cartItem->id), [
            'quantity' => 3,
        ]);

        $response->assertRedirect(route('consumer.cart.index'));
        $response->assertSessionHas('error', 'Batas waktu reservasi makanan ini telah berakhir.');
        
        // Assert cart item is deleted because it was expired
        $this->assertNull(CartItem::find($cartItem->id));
        // Assert stock is returned (5 + 2 = 7)
        $this->assertEquals(7, $product->fresh()->stock);
    }
}
