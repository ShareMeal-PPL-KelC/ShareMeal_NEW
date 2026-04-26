<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Str;

class TransactionSeeder extends Seeder
{
    public function run()
    {
        // 1. Ambil atau buat Mitra
        $mitra = User::create([
            'name' => 'Warung Nasi Berkah',
            'email' => 'mitra@berkah.id',
            'password' => bcrypt('password'),
            'role' => 'mitra',
        ]);

        // 2. Ambil atau buat Konsumen
        $consumer = User::create([
            'name' => 'Budi Pembeli',
            'email' => 'budi@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'consumer',
        ]);

        // 3. Buat Produk
        $product1 = Product::create([
            'user_id' => $mitra->id,
            'name' => 'Nasi Ayam Goreng',
            'category' => 'Makanan Utama',
            'price' => 12000,
            'stock' => 50,
            'expires_at' => now()->addDays(1),
        ]);

        $product2 = Product::create([
            'user_id' => $mitra->id,
            'name' => 'Es Teh Manis',
            'category' => 'Minuman',
            'price' => 3000,
            'stock' => 100,
            'expires_at' => now()->addDays(1),
        ]);

        // 4. Buat Order Pending (Bisa dicoba Detail & Batal)
        $order1 = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 15000,
            'status' => 'pending',
            'pickup_code' => 'SML-' . strtoupper(Str::random(5)),
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $product1->id,
            'quantity' => 1,
            'price' => 12000,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'price' => 3000,
        ]);

        // 5. Buat Order Selesai (Bisa dicoba Detail saja)
        $order2 = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 24000,
            'status' => 'completed',
            'pickup_code' => 'SML-' . strtoupper(Str::random(5)),
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'price' => 12000,
        ]);
    }
}
