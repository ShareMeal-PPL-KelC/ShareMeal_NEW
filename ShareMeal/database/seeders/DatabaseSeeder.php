<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Dummy Consumer
        $consumer = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => bcrypt('password'),
            'role' => 'consumer',
            'phone' => '081234567890',
            'is_verified' => true,
        ]);

        // Dummy Mitra
        $mitra = User::create([
            'name' => 'Toko Roti Makmur',
            'email' => 'mitra@example.com',
            'password' => bcrypt('password'),
            'role' => 'mitra',
            'phone' => '089876543210',
            'is_verified' => true,
        ]);

        // Dummy Products
        $product1 = \App\Models\Product::create([
            'user_id' => $mitra->id,
            'name' => 'Roti Tawar Gandum',
            'category' => 'Bakery',
            'price' => 20000,
            'discount_price' => 14000,
            'stock' => 10,
            'expires_at' => now()->addHours(5),
            'status' => 'flash-sale',
            'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=500&h=300&fit=crop',
        ]);

        $product2 = \App\Models\Product::create([
            'user_id' => $mitra->id,
            'name' => 'Susu Kurma Segar',
            'category' => 'Healthy',
            'price' => 15000,
            'discount_price' => 0,
            'stock' => 25,
            'expires_at' => now()->addDays(2),
            'status' => 'normal',
            'image' => 'https://images.unsplash.com/photo-1596805445214-722a550fa6d2?w=500&h=300&fit=crop',
        ]);

        // Dummy Order 1 (Pending)
        $order1 = \App\Models\Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 28000, // 2x product1
            'status' => 'pending',
            'pickup_code' => 'XYZ123',
            'pickup_time' => now()->addHours(2),
        ]);
        \App\Models\OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'price' => 14000,
        ]);

        // Dummy Order 2 (Completed)
        $order2 = \App\Models\Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 15000, // 1x product2
            'status' => 'completed',
            'pickup_code' => 'ABC987',
            'pickup_time' => now()->subHours(1),
        ]);
        \App\Models\OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'price' => 15000,
        ]);

        $this->call([
            AdminSeeder::class,
        ]);
    }
}
