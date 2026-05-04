<?php

namespace Tests\Browser;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class MitraOrderTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test that a mitra can see incoming orders.
     */
    public function test_mitra_can_see_incoming_orders(): void
    {
        $mitra = User::factory()->create([
            'role' => 'mitra',
            'is_verified' => true,
        ]);

        $consumer = User::factory()->create([
            'name' => 'Budi Santoso',
            'role' => 'consumer',
        ]);

        $product = Product::create([
            'user_id' => $mitra->id,
            'name' => 'Roti Tawar Gandum',
            'category' => 'Bakery',
            'price' => 20000,
            'stock' => 10,
            'expires_at' => now()->addHours(5),
            'status' => 'normal',
        ]);

        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 20000,
            'status' => 'pending',
            'pickup_code' => 'XYZ123',
            'pickup_time' => now()->addHours(2),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 20000,
        ]);

        $this->browse(function (Browser $browser) use ($mitra) {
            $browser->loginAs($mitra)
                ->visit('/mitra')
                ->assertSee('Daftar Pesanan Masuk')
                ->assertSee('Menunggu Diambil')
                ->assertSee('Budi Santoso')
                ->assertSee('Roti Tawar Gandum');
        });
    }
}
