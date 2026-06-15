<?php

namespace Tests\Browser;

use App\Models\Order;
use App\Models\User;
use App\Models\Review;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * PBI-31: Rating Review
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
class Pbi31RatingReviewTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_consumer_can_submit_review_for_order(): void
    {
        $email = 'consumer31_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $password = 'password123';

        $consumer = User::factory()->create([
            'role' => 'consumer',
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $mitra = User::factory()->create(['role' => 'mitra']);
        
        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 50000,
            'status' => 'completed',
            'pickup_code' => 'TEST-123',
            'confirmed_by_consumer' => true,
        ]);

        $this->browse(function (Browser $browser) use ($email, $password) {
            $browser->driver->manage()->deleteAllCookies();

            // Mengunjungi halaman '/login'
            $browser->visit('/login')
                    // Memilih opsi 'consumer' pada dropdown 'user_type'
                    ->select('user_type', 'consumer')
                    // Mengisi input field 'email'
                    ->type('email', $email)
                    // Mengisi input field 'password'
                    ->type('password', $password)
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('button[type="submit"]')
                    // Menunggu halaman berpindah ke rute '/consumer' (batas waktu 15 detik)
                    ->waitForLocation('/consumer', 15)
                    // Mengunjungi halaman '/consumer/history'
                    ->visit('/consumer/history')
                    // Menunggu elemen 'elemen terkait' muncul di layar (batas waktu standar detik)
                    ->waitFor('@tulis-ulasan-btn')
                    // Mengeklik elemen '@tulis-ulasan-btn' di halaman
                    ->click('@tulis-ulasan-btn')
                    // Menunggu elemen 'elemen terkait' muncul di layar (batas waktu standar detik)
                    ->waitFor('textarea[name="comment"]')
                    // Mengeklik elemen '@rating-5-btn' di halaman
                    ->click('@rating-5-btn')
                    // Mengisi input 'comment' dengan nilai 'Makanannya sangat enak dan masih hangat!'
                    ->type('comment', 'Makanannya sangat enak dan masih hangat!')
                    // Mengeklik elemen '@submit-review-btn' di halaman
                    ->click('@submit-review-btn')
                    // Menunggu teks 'Ulasan Terkirim!' muncul di layar (batas waktu 15 detik)
                    ->waitForText('Ulasan Terkirim!', 15)
                    // Memastikan teks 'Ulasan Terkirim!' terlihat pada halaman browser
                    ->assertSee('Ulasan Terkirim!');
        });
    }

    public function test_consumer_cannot_review_same_order_twice(): void
    {
        $email = 'consumer31_twice_' . time() . '_' . rand(1000, 9999) . '@example.com';
        $password = 'password123';

        $consumer = User::factory()->create([
            'role' => 'consumer',
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $mitra = User::factory()->create(['role' => 'mitra']);
        
        $order = Order::create([
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'total_amount' => 50000,
            'status' => 'completed',
            'confirmed_by_consumer' => true,
        ]);

        Review::create([
            'order_id' => $order->id,
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'rating' => 4,
            'comment' => 'Bagus',
        ]);

        $this->browse(function (Browser $browser) use ($email, $password) {
            $browser->driver->manage()->deleteAllCookies();

            // Mengunjungi halaman '/login'
            $browser->visit('/login')
                    // Memilih opsi 'consumer' pada dropdown 'user_type'
                    ->select('user_type', 'consumer')
                    // Mengisi input field 'email'
                    ->type('email', $email)
                    // Mengisi input field 'password'
                    ->type('password', $password)
                    // Mengeklik elemen 'elemen terkait' di halaman
                    ->click('button[type="submit"]')
                    // Menunggu halaman berpindah ke rute '/consumer' (batas waktu 15 detik)
                    ->waitForLocation('/consumer', 15)
                    // Mengunjungi halaman '/consumer/history'
                    ->visit('/consumer/history')
                    // Since it has already been reviewed, the "tulis-ulasan-btn" should not be visible.
                    // Instead, we should see the text "PENILAIAN & ULASAN ANDA"
                    // Menunggu teks 'PENILAIAN & ULASAN ANDA' muncul di layar (batas waktu 15 detik)
                    ->waitForText('PENILAIAN & ULASAN ANDA', 15)
                    // Memastikan teks 'Berikan Penilaian & Ulasan' TIDAK muncul pada halaman browser
                    ->assertDontSee('Berikan Penilaian & Ulasan');
        });
    }
}
