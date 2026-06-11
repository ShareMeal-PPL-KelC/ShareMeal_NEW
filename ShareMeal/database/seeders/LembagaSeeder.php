<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LembagaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lembaga = User::updateOrCreate(
            ['email' => 'lembaga@example.com'],
            [
                'name' => 'Hendra Setiawan',
                'password' => Hash::make('password'),
                'role' => 'lembaga',
                'status' => 'active',
                'is_verified' => true,
                'organization_name' => 'Yayasan Peduli Anak',
                'joined_at' => now(),
            ]
        );

        $lembaga->profile()->updateOrCreate(
            [],
            [
                'phone' => '081234567891',
                'address' => 'Jl. Buah Batu No. 100, Bandung',
                'latitude' => -6.950000,
                'longitude' => 107.630000,
                'is_verified' => true,
            ]
        );
    }
}
