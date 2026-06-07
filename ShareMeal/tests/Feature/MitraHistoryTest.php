<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MitraHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_mitra_history(): void
    {
        $response = $this->get(route('mitra.history'));
        $response->assertRedirect('/login');
    }

    public function test_consumer_cannot_access_mitra_history(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);
        $response = $this->actingAs($consumer)->get(route('mitra.history'));
        $response->assertRedirect(route('consumer.dashboard'));
    }

    public function test_mitra_can_access_history_and_see_completed_and_cancelled_orders(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra', 'is_verified' => true]);
        $consumer = User::factory()->create(['role' => 'consumer']);

        // Create product
        $product = Product::create([
            'user_id' => $mitra->id,
            'name' => 'Nasi Rames Surplus',
            'category' => 'Heavy Meal',
            'price' => 15000,
            'stock' => 10,
            'expires_at' => now()->addDays(1),
            'pickup_start_time' => '17:00',
            'pickup_end_time' => '19:00',
            'status' => 'normal',
        ]);

        // Completed order for this mitra
        $orderCompleted = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 30000,
            'status' => 'completed',
            'pickup_code' => 'MITRA-COMP',
            'receiving_method' => 'pickup',
            'pickup_start_time' => '17:00',
            'pickup_end_time' => '19:00',
        ]);
        OrderItem::create([
            'order_id' => $orderCompleted->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 15000,
        ]);

        // Cancelled order for this mitra
        $orderCancelled = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 15000,
            'status' => 'cancelled',
            'pickup_code' => 'MITRA-CANC',
            'receiving_method' => 'pickup',
            'pickup_start_time' => '17:00',
            'pickup_end_time' => '19:00',
        ]);
        OrderItem::create([
            'order_id' => $orderCancelled->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 15000,
        ]);

        // Active/pending order (should NOT be visible in history)
        $orderPending = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 45000,
            'status' => 'pending',
            'pickup_code' => 'MITRA-PEND',
            'receiving_method' => 'pickup',
        ]);

        $response = $this->actingAs($mitra)
            ->withSession(['sharemeal.current_user_id' => $mitra->id])
            ->get(route('mitra.history'));

        $response->assertStatus(200);
        $response->assertSee('Riwayat Transaksi');
        
        // Assert stats
        $response->assertSee('2 Pesanan'); // Total: 1 Completed + 1 Cancelled = 2
        $response->assertSee('1 Pesanan'); // 1 Completed & 1 Cancelled counts
        $response->assertSee('Rp 30.000'); // Completed revenue

        // Assert presence of completed and cancelled order identifiers or data
        $response->assertSee('MITRA-COMP');
        $response->assertSee('MITRA-CANC');
        $response->assertDontSee('MITRA-PEND');
    }

    public function test_mitra_cannot_see_orders_from_other_mitra(): void
    {
        $mitra1 = User::factory()->create(['role' => 'mitra', 'is_verified' => true]);
        $mitra2 = User::factory()->create(['role' => 'mitra', 'is_verified' => true]);
        $consumer = User::factory()->create(['role' => 'consumer']);

        $product1 = Product::create([
            'user_id' => $mitra1->id,
            'name' => 'Nasi Goreng Mitra 1',
            'category' => 'Heavy Meal',
            'price' => 12000,
            'stock' => 10,
            'expires_at' => now()->addDays(1),
            'pickup_start_time' => '17:00',
            'pickup_end_time' => '19:00',
            'status' => 'normal',
        ]);

        $product2 = Product::create([
            'user_id' => $mitra2->id,
            'name' => 'Bakso Mitra 2',
            'category' => 'Heavy Meal',
            'price' => 15000,
            'stock' => 10,
            'expires_at' => now()->addDays(1),
            'pickup_start_time' => '17:00',
            'pickup_end_time' => '19:00',
            'status' => 'normal',
        ]);

        $orderMitra1 = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra1->id,
            'total_amount' => 12000,
            'status' => 'completed',
            'pickup_code' => 'M1-CODE',
            'receiving_method' => 'pickup',
        ]);
        OrderItem::create([
            'order_id' => $orderMitra1->id,
            'product_id' => $product1->id,
            'quantity' => 1,
            'price' => 12000,
        ]);

        $orderMitra2 = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra2->id,
            'total_amount' => 15000,
            'status' => 'completed',
            'pickup_code' => 'M2-CODE',
            'receiving_method' => 'pickup',
        ]);
        OrderItem::create([
            'order_id' => $orderMitra2->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'price' => 15000,
        ]);

        $response = $this->actingAs($mitra1)
            ->withSession(['sharemeal.current_user_id' => $mitra1->id])
            ->get(route('mitra.history'));

        $response->assertStatus(200);
        $response->assertSee('M1-CODE');
        $response->assertDontSee('M2-CODE');
    }
}
