<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        // Konsumen (Budi)
        $konsumen = User::where('email', 'konsumen@gmail.com')->first();
        if ($konsumen) {
            Transaction::create(['user_id' => $konsumen->id, 'type' => 'Pembelian', 'description' => 'Membeli Nasi Rames']);
            Transaction::create(['user_id' => $konsumen->id, 'type' => 'Pembelian', 'description' => 'Membeli Es Teh Manis']);
        }

        // Mitra (Restoran)
        $mitra = User::where('email', 'mitra@gmail.com')->first();
        if ($mitra) {
            Transaction::create(['user_id' => $mitra->id, 'type' => 'Penjualan', 'description' => 'Terjual 10 Box Nasi Ayam']);
            Transaction::create(['user_id' => $mitra->id, 'type' => 'Donasi', 'description' => 'Mendonasikan 5 Porsi Sayuran']);
        }

        // Lembaga
        $lembaga = User::where('email', 'lembaga@gmail.com')->first();
        if ($lembaga) {
            Transaction::create(['user_id' => $lembaga->id, 'type' => 'Penerimaan', 'description' => 'Menerima 20 Paket Sembako']);
            Transaction::create(['user_id' => $lembaga->id, 'type' => 'Penyaluran', 'description' => 'Menyalurkan Bantuan ke Panti Asuhan']);
        }
    }
}
