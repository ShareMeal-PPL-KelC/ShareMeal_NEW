<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\UserProfile;

class Pbi43NotifikasiPerubahanStatusPesananTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * [PBI 43] Notifikasi Perubahan Status Pesanan
     * Skenario: Status Ready (Siap Diambil)
     */
    public function test_positive_konsumen_menerima_notifikasi_perubahan_status_ready(): void
    {
        $this->browse(function (Browser $browser) {
            // --- 1. SETUP DATA ---
            $mitra = User::factory()->create(['role' => 'mitra', 'is_verified' => true]);
            $consumer = User::factory()->create(['role' => 'consumer', 'name' => 'Budi PBI 43']);

            $order = Order::create([
                'customer_id' => $consumer->id,
                'mitra_id' => $mitra->id,
                'total_amount' => 50000,
                'status' => 'pending',
                'receiving_method' => 'pickup',
                'pickup_start_time' => '08:00',
                'pickup_end_time' => '20:00'
            ]);

            // --- 2. TRIGGER NOTIFIKASI ---
            // Simulasikan perpindahan status di database agar trigger notifikasi aktif
            $order->update(['status' => 'ready']);

            // --- 3. KONSUMEN CEK NOTIFIKASI ---
            $browser->loginAs($consumer)
                    ->visitRoute('notifications.index')
                    ->waitForText('Update Status Pesanan', 15)
                    ->assertSee('Pesanan Anda sudah siap diambil! Mohon tunjukkan kode klaim kepada pelayan kami jika sudah sampai.');
        });
    }

    /**
     * Skenario: Status Shipping (Dikirim)
     */
    public function test_positive_konsumen_menerima_notifikasi_perubahan_status_shipping(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::factory()->create(['role' => 'mitra', 'is_verified' => true]);
            $consumer = User::factory()->create(['role' => 'consumer']);

            $order = Order::create([
                'customer_id' => $consumer->id,
                'mitra_id' => $mitra->id,
                'total_amount' => 60000,
                'status' => 'pending', 
                'receiving_method' => 'delivery',
                'pickup_start_time' => '08:00',
                'pickup_end_time' => '20:00'
            ]);

            // Trigger notifikasi shipping
            $order->update(['status' => 'shipping']);

            $browser->loginAs($consumer)
                    ->visitRoute('notifications.index')
                    ->waitForText('Update Status Pesanan', 15)
                    ->assertSee('Pesanan Anda sedang dalam perjalanan oleh kurir mitra.');
        });
    }

    /**
     * Skenario: Status Processing (Diproses)
     */
    public function test_positive_konsumen_menerima_notifikasi_perubahan_status_processing(): void
    {
        $this->browse(function (Browser $browser) {
            $mitra = User::factory()->create(['role' => 'mitra', 'is_verified' => true]);
            $consumer = User::factory()->create(['role' => 'consumer']);

            $order = Order::create([
                'customer_id' => $consumer->id,
                'mitra_id' => $mitra->id,
                'total_amount' => 45000,
                'status' => 'pending',
                'receiving_method' => 'pickup',
                'pickup_start_time' => '08:00',
                'pickup_end_time' => '20:00'
            ]);

            // Trigger notifikasi processing
            $order->update(['status' => 'processing']);

            $browser->loginAs($consumer)
                    ->visitRoute('notifications.index')
                    ->waitForText('Update Status Pesanan', 15)
                    ->assertSee('Pesanan Anda sedang diproses.');
        });
    }
}
