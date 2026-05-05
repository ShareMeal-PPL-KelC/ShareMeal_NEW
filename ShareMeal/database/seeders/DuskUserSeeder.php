<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DuskUserSeeder extends Seeder
{
    public function run(): void
    {
        // User for LoginTest
        User::updateOrCreate(
            ['email' => 'kya@gmail.com'],
            [
                'name' => 'Kya Test User',
                'password' => Hash::make('password'),
                'role' => 'consumer',
                'is_verified' => true,
            ]
        );

        // User for Konsumen tests
        $kina = User::updateOrCreate(
            ['email' => 'kina@gmail.com'],
            [
                'name' => 'kina',
                'password' => Hash::make('password'),
                'role' => 'consumer',
                'is_verified' => true,
            ]
        );

        // User for Mitra tests
        $ayamMitra = User::updateOrCreate(
            ['email' => 'ayam@gmail.com'],
            [
                'name' => 'ayam',
                'password' => Hash::make('ayam@gmail.com'),
                'role' => 'mitra',
                'is_verified' => true,
            ]
        );

        // Product for Mitra
        $product = Product::updateOrCreate(
            ['user_id' => $ayamMitra->id, 'name' => 'ayam'],
            [
                'category' => 'Food',
                'price' => 20000,
                'discount_price' => 10000,
                'stock' => 10,
                'expires_at' => now()->addHours(5),
                'status' => 'active',
            ]
        );

        // Order for MitraMenerimaPesananTest and KonsumenWaktuKelayakanKonsumsiTest
        $order = Order::updateOrCreate(
            [
                'customer_id' => $kina->id,
                'mitra_id' => $ayamMitra->id,
                'status' => 'pending'
            ],
            [
                'total_amount' => 10000,
                'pickup_code' => 'KINA123',
                'pickup_time' => now()->addHours(2),
                'created_at' => now(),
            ]
        );

        OrderItem::updateOrCreate(
            ['order_id' => $order->id, 'product_id' => $product->id],
            [
                'quantity' => 1,
                'price' => 10000,
            ]
        );
    }
}
