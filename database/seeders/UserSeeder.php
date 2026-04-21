<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Data Admin
        User::create([
            'name' => 'Admin ShareMeal',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Data Mitra
        User::create([
            'name' => 'Restoran Enak (Mitra)',
            'email' => 'mitra@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'mitra',
        ]);

        // Data Konsumen
        User::create([
            'name' => 'Budi Sudarsono (Konsumen)',
            'email' => 'konsumen@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'konsumen',
        ]);

        // Data Lembaga
        User::create([
            'name' => 'Lembaga Berbagi (Lembaga)',
            'email' => 'lembaga@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'lembaga',
        ]);
    }
}
