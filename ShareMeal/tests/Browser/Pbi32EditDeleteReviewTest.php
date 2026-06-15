<?php

namespace Tests\Browser;

use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * PBI-32: Edit Delete Review
 * Pengujian otomatis berbasis browser menggunakan Laravel Dusk.
 * Berkas ini merepresentasikan skenario pengujian untuk membantu presentasi dan demo aplikasi.
 */
class Pbi32EditDeleteReviewTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_consumer_can_edit_their_review(): void
    {
        $email = 'consumer32_' . time() . '_' . rand(1000, 9999) . '@example.com';
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

        $review = Review::create([
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
                    // Menunggu elemen 'elemen terkait' muncul di layar (batas waktu standar detik)
                    ->waitFor('@edit-ulasan-btn')
                    // Mengeklik elemen '@edit-ulasan-btn' di halaman
                    ->click('@edit-ulasan-btn')
                    // Menunggu elemen 'elemen terkait' muncul di layar (batas waktu standar detik)
                    ->waitFor('textarea[name="comment"]')
                    // Mengeklik elemen '@rating-5-btn' di halaman
                    ->click('@rating-5-btn')
                    // Mengisi input 'comment' dengan nilai 'Sangat Bagus Sekali'
                    ->type('comment', 'Sangat Bagus Sekali')
                    // Mengeklik elemen '@submit-review-btn' di halaman
                    ->click('@submit-review-btn')
                    // Menunggu teks 'Ulasan Terkirim!' muncul di layar (batas waktu 15 detik)
                    ->waitForText('Ulasan Terkirim!', 15)
                    // Memastikan teks 'Ulasan Terkirim!' terlihat pada halaman browser
                    ->assertSee('Ulasan Terkirim!');
        });
    }

    public function test_consumer_cannot_edit_review_after_two_minutes(): void
    {
        $email = 'consumer32_lock_' . time() . '_' . rand(1000, 9999) . '@example.com';
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

        $review = Review::create([
            'order_id' => $order->id,
            'customer_id' => $consumer->id,
            'mitra_id' => $mitra->id,
            'rating' => 4,
            'comment' => 'Bagus',
        ]);
        $review->created_at = now()->subMinutes(3);
        $review->save();

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
                    // Check that the review modification elements are locked
                    // Menunggu teks 'TERKUNCI' muncul di layar (batas waktu 15 detik)
                    ->waitForText('TERKUNCI', 15)
                    // Memastikan teks 'TERKUNCI' terlihat pada halaman browser
                    ->assertSee('TERKUNCI')
                    // Memastikan teks 'UBAH' TIDAK muncul pada halaman browser
                    ->assertDontSee('UBAH')
                    // Memastikan teks 'HAPUS' TIDAK muncul pada halaman browser
                    ->assertDontSee('HAPUS');
        });
    }
}
