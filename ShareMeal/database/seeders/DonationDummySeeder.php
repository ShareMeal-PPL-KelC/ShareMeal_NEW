<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Donation;
use Illuminate\Database\Seeder;

class DonationDummySeeder extends Seeder
{
    public function run(): void
    {
        $mitra = User::where('email', 'mitra@example.com')->first();
        
        if (!$mitra) {
            $mitra = User::create([
                'name' => 'Toko Roti Makmur',
                'email' => 'mitra@example.com',
                'password' => bcrypt('password'),
                'role' => 'mitra',
                'phone' => '089876543210',
                'is_verified' => true,
            ]);
        }

        $lembaga = User::where('email', 'lembaga@example.com')->first();

        // Clear existing dummy donations to avoid duplicates during re-seeding
        Donation::where('mitra_id', $mitra->id)->delete();

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
            'lembaga_id' => $lembaga?->id,
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
            'lembaga_id' => $lembaga?->id,
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
