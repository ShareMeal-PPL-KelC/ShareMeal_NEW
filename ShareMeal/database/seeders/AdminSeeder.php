<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@sharemeal.id'],
            [
                'name' => 'Admin ShareMeal',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'status' => 'active',
                'is_verified' => true,
                'joined_at' => now(),
            ]
        );
    }
}
