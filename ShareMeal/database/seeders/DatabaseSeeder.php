<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserProfile;
use App\Models\Review;
use App\Models\ProblemReport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Dummy Consumer
        $consumer = User::updateOrCreate(
            ['email' => 'budi@example.com'],
            [
                'name' => 'Budi Santoso',
                'password' => bcrypt('password'),
                'role' => 'consumer',
                'phone' => '081234567890',
                'is_verified' => true,
            ]
        );
        UserProfile::updateOrCreate(
            ['user_id' => $consumer->id],
            [
                'phone' => '081234567890',
                'address' => 'Kost Orange, Jl. Telekomunikasi No. 12, Sukabirus, Bandung',
                'latitude' => -6.974128,
                'longitude' => 107.630928,
                'is_verified' => true,
                'avatar' => 'images/profile',
            ]
        );

        // 2. Mitra 1: Toko Roti Makmur (Bakery)
        $mitra1 = User::updateOrCreate(
            ['email' => 'mitra@example.com'],
            [
                'name' => 'Hendra Wijaya',
                'organization_name' => 'Toko Roti Makmur',
                'password' => bcrypt('password'),
                'role' => 'mitra',
                'phone' => '089876543210',
                'is_verified' => true,
            ]
        );
        UserProfile::updateOrCreate(
            ['user_id' => $mitra1->id],
            [
                'phone' => '089876543210',
                'address' => 'Jl. Sukabirus No. 45, Dayeuhkolot, Bandung',
                'latitude' => -6.974028,
                'longitude' => 107.630528,
                'business_type' => 'Bakery',
                'business_name' => 'Toko Roti Makmur',
                'business_address' => 'Jl. Sukabirus No. 45, Dayeuhkolot, Bandung',
                'business_contact' => '089876543210',
                'business_opening_hours' => '08:00 - 20:00',
                'business_description' => 'Menyediakan aneka roti tawar dan pastry lezat sisa produksi hari ini yang masih sangat layak konsumsi.',
                'rating' => 4.8,
                'opening_hours' => '08:00 - 20:00',
                'is_verified' => true,
                'can_delivery' => true,
                'delivery_fee' => 5000,
                'delivery_slot_limit' => 10,
            ]
        );

        // 3. Mitra 2: Warmindo Barokah (Indonesian Meals)
        $mitra2 = User::updateOrCreate(
            ['email' => 'warmindo@example.com'],
            [
                'name' => 'Ahmad Barokah',
                'organization_name' => 'Warmindo Barokah',
                'password' => bcrypt('password'),
                'role' => 'mitra',
                'phone' => '082123456789',
                'is_verified' => true,
            ]
        );
        UserProfile::updateOrCreate(
            ['user_id' => $mitra2->id],
            [
                'phone' => '082123456789',
                'address' => 'Jl. Telekomunikasi No. 20, Terusan Buah Batu, Bandung',
                'latitude' => -6.975500,
                'longitude' => 107.632000,
                'business_type' => 'Meals',
                'business_name' => 'Warmindo Barokah',
                'business_address' => 'Jl. Telekomunikasi No. 20, Terusan Buah Batu, Bandung',
                'business_contact' => '082123456789',
                'business_opening_hours' => '00:00 - 23:59',
                'business_description' => 'Warung makan khas mie instan, nasi goreng, dan aneka orak-arik dengan cita rasa nusantara.',
                'rating' => 4.5,
                'opening_hours' => '00:00 - 23:59',
                'is_verified' => true,
                'can_delivery' => true,
                'delivery_fee' => 3000,
                'delivery_slot_limit' => 15,
            ]
        );

        // 4. Mitra 3: Dapur Bunda Siska (Traditional Meals)
        $mitra3 = User::updateOrCreate(
            ['email' => 'dapur@example.com'],
            [
                'name' => 'Bunda Siska',
                'organization_name' => 'Dapur Bunda Siska',
                'password' => bcrypt('password'),
                'role' => 'mitra',
                'phone' => '085311223344',
                'is_verified' => true,
            ]
        );
        UserProfile::updateOrCreate(
            ['user_id' => $mitra3->id],
            [
                'phone' => '085311223344',
                'address' => 'Jl. PGA No. 8, Lengkong, Bandung',
                'latitude' => -6.973000,
                'longitude' => 107.629000,
                'business_type' => 'Meals',
                'business_name' => 'Dapur Bunda Siska',
                'business_address' => 'Jl. PGA No. 8, Lengkong, Bandung',
                'business_contact' => '085311223344',
                'business_opening_hours' => '09:00 - 18:00',
                'business_description' => 'Masakan rumahan khas sunda, ayam geprek, rendang, dan sayur mayur sehat berkualitas tinggi.',
                'rating' => 4.9,
                'opening_hours' => '09:00 - 18:00',
                'is_verified' => true,
                'can_delivery' => false,
                'delivery_fee' => 0,
                'delivery_slot_limit' => 0,
            ]
        );

        // 5. Mitra 4: Healthy Salad Bar (Healthy Food)
        $mitra4 = User::updateOrCreate(
            ['email' => 'salad@example.com'],
            [
                'name' => 'Rudi Hartono',
                'organization_name' => 'Healthy Salad Bar',
                'password' => bcrypt('password'),
                'role' => 'mitra',
                'phone' => '081988776655',
                'is_verified' => true,
            ]
        );
        UserProfile::updateOrCreate(
            ['user_id' => $mitra4->id],
            [
                'phone' => '081988776655',
                'address' => 'Gg. PGA No. 2, Sukabirus, Bandung',
                'latitude' => -6.972000,
                'longitude' => 107.633000,
                'business_type' => 'Healthy',
                'business_name' => 'Healthy Salad Bar',
                'business_address' => 'Gg. PGA No. 2, Sukabirus, Bandung',
                'business_contact' => '081988776655',
                'business_opening_hours' => '10:00 - 21:00',
                'business_description' => 'Menyediakan aneka salad buah segar, salad sayur organik, dan jus sehat penunjang diet.',
                'rating' => 4.7,
                'opening_hours' => '10:00 - 21:00',
                'is_verified' => true,
                'can_delivery' => true,
                'delivery_fee' => 6000,
                'delivery_slot_limit' => 8,
            ]
        );

        // 6. Dummy Products
        // Toko Roti Makmur
        $p1 = Product::create([
            'user_id' => $mitra1->id,
            'name' => 'Roti Gandum Spesial',
            'category' => 'Bakery',
            'price' => 20000,
            'discount_price' => 14000,
            'stock' => 10,
            'expires_at' => now()->addHours(5),
            'status' => 'flash-sale',
            'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=500&h=300&fit=crop',
        ]);
        $p2 = Product::create([
            'user_id' => $mitra1->id,
            'name' => 'Susu Kurma Segar',
            'category' => 'Healthy',
            'price' => 15000,
            'discount_price' => 0,
            'stock' => 25,
            'expires_at' => now()->addDays(2),
            'status' => 'normal',
            'image' => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=500&h=300&fit=crop',
        ]);
        $p3 = Product::create([
            'user_id' => $mitra1->id,
            'name' => 'Croissant Cokelat Per Box',
            'category' => 'Bakery',
            'price' => 25000,
            'discount_price' => 17500,
            'stock' => 5,
            'expires_at' => now()->addHours(3),
            'status' => 'flash-sale',
            'image' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=500&h=300&fit=crop',
        ]);

        // Warmindo Barokah
        $p4 = Product::create([
            'user_id' => $mitra2->id,
            'name' => 'Nasi Goreng Spesial',
            'category' => 'Meals',
            'price' => 18000,
            'discount_price' => 12600,
            'stock' => 8,
            'expires_at' => now()->addHours(4),
            'status' => 'flash-sale',
            'image' => 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=500&h=300&fit=crop',
        ]);
        $p5 = Product::create([
            'user_id' => $mitra2->id,
            'name' => 'Indomie Orak Arik Nyemek',
            'category' => 'Meals',
            'price' => 12000,
            'discount_price' => 8400,
            'stock' => 4,
            'expires_at' => now()->addHours(2),
            'status' => 'flash-sale',
            'image' => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=500&h=300&fit=crop',
        ]);

        // Dapur Bunda Siska
        $p6 = Product::create([
            'user_id' => $mitra3->id,
            'name' => 'Ayam Geprek Sambal Korek',
            'category' => 'Meals',
            'price' => 22000,
            'discount_price' => 15400,
            'stock' => 12,
            'expires_at' => now()->addHours(6),
            'status' => 'flash-sale',
            'image' => 'https://images.unsplash.com/photo-1626082927389-6cd097cdc6ec?w=500&h=300&fit=crop',
        ]);

        // Healthy Salad Bar
        $p7 = Product::create([
            'user_id' => $mitra4->id,
            'name' => 'Fruit Salad Yogurt Jumbo',
            'category' => 'Healthy',
            'price' => 30000,
            'discount_price' => 21000,
            'stock' => 6,
            'expires_at' => now()->addHours(8),
            'status' => 'flash-sale',
            'image' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=500&h=300&fit=crop',
        ]);

        // 7. Dummy Orders & Items

        // Order 1: Pending (mitra1) - Pickup
        $order1 = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra1->id,
            'total_amount' => 28000,
            'status' => 'pending',
            'pickup_code' => 'XYZ123',
            'pickup_time' => now()->addHours(2),
            'receiving_method' => 'pickup',
            'payment_method' => 'GoPay',
        ]);
        OrderItem::create([
            'order_id' => $order1->id,
            'product_id' => $p1->id,
            'quantity' => 2,
            'price' => 14000,
        ]);

        // Order 9: Pending (mitra1) - Delivery
        $order9 = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra1->id,
            'total_amount' => 15000,
            'status' => 'pending',
            'pickup_code' => 'DEL999',
            'pickup_time' => now()->addHours(3),
            'receiving_method' => 'delivery',
            'delivery_fee' => 5000,
            'delivery_time_slot' => '14:00 - 15:00',
            'payment_method' => 'OVO',
        ]);
        OrderItem::create([
            'order_id' => $order9->id,
            'product_id' => $p2->id,
            'quantity' => 1,
            'price' => 15000,
        ]);

        // Order 2: Processing (mitra2) - Sedang dibuat (Pickup)
        $order2 = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra2->id,
            'total_amount' => 12000,
            'status' => 'processing',
            'pickup_code' => 'WRM456',
            'pickup_time' => now()->addHours(1),
            'receiving_method' => 'pickup',
            'payment_method' => 'QRIS',
        ]);
        OrderItem::create([
            'order_id' => $order2->id,
            'product_id' => $p4->id,
            'quantity' => 1,
            'price' => 12000,
        ]);

        // Order 10: Processing (mitra1) - Sedang dibuat (Pickup, Normal)
        $order10 = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra1->id,
            'total_amount' => 14000,
            'status' => 'processing',
            'pickup_code' => 'PRC101',
            'pickup_time' => now()->addHours(1),
            'receiving_method' => 'pickup',
            'payment_method' => 'QRIS',
        ]);
        OrderItem::create([
            'order_id' => $order10->id,
            'product_id' => $p1->id,
            'quantity' => 1,
            'price' => 14000,
        ]);

        // Order 11: Processing (mitra1) - Sedang dibuat (Delivery, Delayed)
        $order11 = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra1->id,
            'total_amount' => 15000,
            'status' => 'processing',
            'is_delayed' => true,
            'delayed_at' => now()->subMinutes(10),
            'pickup_code' => 'PRC102',
            'pickup_time' => now()->addMinutes(45),
            'receiving_method' => 'delivery',
            'delivery_fee' => 5000,
            'delivery_time_slot' => '13:00 - 14:00',
            'payment_method' => 'QRIS',
        ]);
        OrderItem::create([
            'order_id' => $order11->id,
            'product_id' => $p3->id,
            'quantity' => 1,
            'price' => 15000,
        ]);

        // Order 3: Ready (mitra3) - Siap Diambil
        $order3 = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra3->id,
            'total_amount' => 15000,
            'status' => 'ready',
            'pickup_code' => 'DPB789',
            'pickup_time' => now()->addHours(3),
            'receiving_method' => 'pickup',
            'payment_method' => 'OVO',
        ]);
        OrderItem::create([
            'order_id' => $order3->id,
            'product_id' => $p6->id,
            'quantity' => 1,
            'price' => 15000,
        ]);

        // Order 12: Ready (mitra1) - Siap Diambil
        $order12 = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra1->id,
            'total_amount' => 30000,
            'status' => 'ready',
            'pickup_code' => 'RDY103',
            'pickup_time' => now()->addHours(2),
            'receiving_method' => 'pickup',
            'payment_method' => 'Gopay',
        ]);
        OrderItem::create([
            'order_id' => $order12->id,
            'product_id' => $p3->id,
            'quantity' => 2,
            'price' => 15000,
        ]);

        // Order 13: Ready (mitra1) - Siap Dikirim (Delivery)
        $order13 = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra1->id,
            'total_amount' => 28000,
            'status' => 'ready',
            'pickup_code' => 'RDY104',
            'pickup_time' => now()->addHours(3),
            'receiving_method' => 'delivery',
            'delivery_fee' => 5000,
            'delivery_time_slot' => '15:00 - 16:00',
            'payment_method' => 'DANA',
        ]);
        OrderItem::create([
            'order_id' => $order13->id,
            'product_id' => $p1->id,
            'quantity' => 2,
            'price' => 14000,
        ]);

        // Order 4: Shipping (mitra1) - Dalam Perjalanan
        $order4 = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra1->id,
            'total_amount' => 35000,
            'status' => 'shipping',
            'pickup_code' => 'SHP321',
            'pickup_time' => now()->addHours(1),
            'receiving_method' => 'delivery',
            'delivery_fee' => 5000,
            'delivery_time_slot' => '12:00 - 13:00',
            'payment_method' => 'OVO',
        ]);
        OrderItem::create([
            'order_id' => $order4->id,
            'product_id' => $p3->id,
            'quantity' => 2,
            'price' => 15000,
        ]);

        // Order 5: Completed with Review (mitra1) - Selesai & Dinilai (Pickup)
        $order5 = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra1->id,
            'total_amount' => 15000,
            'status' => 'completed',
            'confirmed_by_consumer' => true,
            'pickup_code' => 'ABC987',
            'pickup_time' => now()->subHours(5),
            'receiving_method' => 'pickup',
            'payment_method' => 'GoPay',
        ]);
        OrderItem::create([
            'order_id' => $order5->id,
            'product_id' => $p2->id,
            'quantity' => 1,
            'price' => 15000,
        ]);
        Review::create([
            'order_id' => $order5->id,
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra1->id,
            'rating' => 5,
            'comment' => 'Kualitas susu kurma sangat baik, segar dan manisnya pas. Toko Roti Makmur pelayanannya ramah.',
        ]);

        // Order 14: Completed with Review (mitra1) - Selesai & Dinilai (Delivery)
        $order14 = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra1->id,
            'total_amount' => 44000,
            'status' => 'completed',
            'confirmed_by_consumer' => true,
            'pickup_code' => 'CMP105',
            'pickup_time' => now()->subHours(8),
            'receiving_method' => 'delivery',
            'delivery_fee' => 5000,
            'delivery_time_slot' => '10:00 - 11:00',
            'payment_method' => 'QRIS',
        ]);
        OrderItem::create([
            'order_id' => $order14->id,
            'product_id' => $p1->id,
            'quantity' => 2,
            'price' => 14000,
        ]);
        OrderItem::create([
            'order_id' => $order14->id,
            'product_id' => $p2->id,
            'quantity' => 1,
            'price' => 15000,
        ]);
        Review::create([
            'order_id' => $order14->id,
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra1->id,
            'rating' => 4,
            'comment' => 'Rotinya lembut dan pengiriman tepat waktu. Terima kasih!',
        ]);

        // Order 15: Completed without Review (mitra1) - Selesai & Belum Dinilai (Pickup)
        $order15 = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra1->id,
            'total_amount' => 15000,
            'status' => 'completed',
            'confirmed_by_consumer' => true,
            'pickup_code' => 'CMP106',
            'pickup_time' => now()->subHours(12),
            'receiving_method' => 'pickup',
            'payment_method' => 'OVO',
        ]);
        OrderItem::create([
            'order_id' => $order15->id,
            'product_id' => $p3->id,
            'quantity' => 1,
            'price' => 15000,
        ]);

        // Order 16: Completed without Review (mitra1) - Selesai & Belum Dinilai (Delivery)
        $order16 = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra1->id,
            'total_amount' => 20000,
            'status' => 'completed',
            'confirmed_by_consumer' => true,
            'pickup_code' => 'CMP107',
            'pickup_time' => now()->subHours(24),
            'receiving_method' => 'delivery',
            'delivery_fee' => 5000,
            'delivery_time_slot' => '09:00 - 10:00',
            'payment_method' => 'QRIS',
        ]);
        OrderItem::create([
            'order_id' => $order16->id,
            'product_id' => $p2->id,
            'quantity' => 1,
            'price' => 15000,
        ]);

        // Order 6: Completed pending Review (mitra4) - Selesai & Belum Dinilai
        $order6 = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra4->id,
            'total_amount' => 26000,
            'status' => 'completed',
            'confirmed_by_consumer' => false, // Perlu konfirmasi selesai dari konsumen
            'pickup_code' => 'SLD654',
            'pickup_time' => now()->subHours(1),
            'receiving_method' => 'delivery',
            'delivery_fee' => 6000,
            'delivery_time_slot' => '13:00 - 14:00',
            'payment_method' => 'QRIS',
        ]);
        OrderItem::create([
            'order_id' => $order6->id,
            'product_id' => $p7->id,
            'quantity' => 1,
            'price' => 20000,
        ]);

        // Order 7: Cancelled (mitra1)
        $order7 = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra1->id,
            'total_amount' => 14000,
            'status' => 'cancelled',
            'pickup_code' => 'CAN111',
            'pickup_time' => now()->subHours(2),
            'receiving_method' => 'pickup',
            'payment_method' => 'DANA',
            'cancel_reason' => 'Stok gandum hari ini habis total karena salah input inventaris.',
        ]);
        OrderItem::create([
            'order_id' => $order7->id,
            'product_id' => $p1->id,
            'quantity' => 1,
            'price' => 14000,
        ]);

        // Order 8: Delayed (mitra4) - Pesanan terlambat
        $order8 = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra4->id,
            'total_amount' => 20000,
            'status' => 'processing',
            'is_delayed' => true,
            'delayed_at' => now()->subMinutes(10),
            'pickup_code' => 'DLY888',
            'pickup_time' => now()->addMinutes(30),
            'receiving_method' => 'delivery',
            'delivery_fee' => 6000,
            'delivery_time_slot' => '14:00 - 15:00',
            'payment_method' => 'Gopay',
        ]);
        OrderItem::create([
            'order_id' => $order8->id,
            'product_id' => $p7->id,
            'quantity' => 1,
            'price' => 20000,
        ]);

        // 8. Admin Problem Reports
        ProblemReport::create([
            'order_id' => $order7->id,
            'reporter_id' => $consumer->id,
            'mitra_id' => $mitra1->id,
            'issue_type' => 'other',
            'description' => 'Mitra membatalkan pesanan secara sepihak dengan alasan stok habis padahal di sistem tertera masih ada 10 stok.',
            'status' => 'pending',
        ]);

        // 9. Load Additional Seeders
        $this->call([
            AdminSeeder::class,
            LembagaSeeder::class,
            DonationDummySeeder::class,
            ArticleSeeder::class,
            FeedbackSeeder::class,
        ]);
    }
}
