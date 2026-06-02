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
        User::updateOrCreate(
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
    }
}
