<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Donation;
use Illuminate\Database\Seeder;

class DonationDummySeeder extends Seeder
{
    public function run(): void
    {
        $mitra = User::firstOrCreate(
            ['email' => 'mitra_donasi@example.com'],
            [
                'name' => 'Resto Makanan Sehat',
                'password' => bcrypt('password'),
                'role' => 'mitra',
                'phone' => '08123456789',
                'is_verified' => true
            ]
        );

        Donation::create([
            'mitra_id' => $mitra->id,
            'title' => 'Nasi Kotak Ayam Bakar',
            'quantity' => 20,
            'unit' => 'box',
            'expires_at' => now()->addHours(5),
            'status' => 'pending'
        ]);

        Donation::create([
            'mitra_id' => $mitra->id,
            'title' => 'Roti Tawar Gandum',
            'quantity' => 15,
            'unit' => 'pcs',
            'expires_at' => now()->addHours(12),
            'status' => 'pending'
        ]);

        Donation::create([
            'mitra_id' => $mitra->id,
            'title' => 'Sayur Sop Sisa Etalase',
            'quantity' => 10,
            'unit' => 'porsi',
            'expires_at' => now()->subDay(),
            'status' => 'completed',
            'claimed_at' => now()->subDay(),
            'delivered_at' => now()->subDay(),
            'tracking_status' => 'delivered'
        ]);
        
        Donation::create([
            'mitra_id' => $mitra->id,
            'title' => 'Sayur Lodeh',
            'quantity' => 5,
            'unit' => 'porsi',
            'expires_at' => now()->addDay(),
            'status' => 'claimed',
            'claimed_at' => now()->subHour(),
            'tracking_status' => 'confirmed'
        ]);
    }
}
