<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FeedbackFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest cannot access feedback or admin pages
     */
    public function test_guest_cannot_access_feedback_pages(): void
    {
        $this->get(route('consumer.feedback'))->assertRedirect(route('login'));
        $this->post(route('consumer.feedback.store'))->assertRedirect(route('login'));
        
        $this->get(route('admin.feedbacks.index'))->assertRedirect(route('login'));
    }

    /**
     * Test consumer can view feedback form and submit feedback
     */
    public function test_consumer_can_submit_feedback(): void
    {
        Storage::fake('public');

        $consumer = User::factory()->create(['role' => 'consumer']);

        // Check view
        $response = $this->actingAs($consumer)->get(route('consumer.feedback'));
        $response->assertStatus(200);
        $response->assertSee('Kirim Masukan');

        // Submit form
        $response = $this->actingAs($consumer)->post(route('consumer.feedback.store'), [
            'category' => 'bug',
            'subject' => 'Tombol checkout lambat',
            'description' => 'Saat menekan tombol checkout, loading berjalan sangat lama.',
            'rating' => 3,
            'screenshots' => [
                UploadedFile::fake()->create('bug-checkout.png', 500, 'image/png'),
                UploadedFile::fake()->create('bug-checkout-2.png', 300, 'image/png')
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('feedback', [
            'user_id' => $consumer->id,
            'category' => 'bug',
            'subject' => 'Tombol checkout lambat',
            'description' => 'Saat menekan tombol checkout, loading berjalan sangat lama.',
            'rating' => 3,
        ]);

        $feedback = Feedback::first();
        $this->assertNotNull($feedback->screenshots);
        $this->assertCount(2, $feedback->screenshots);
        Storage::disk('public')->assertExists($feedback->screenshots[0]);
        Storage::disk('public')->assertExists($feedback->screenshots[1]);
    }

    /**
     * Test feedback upload rejects file exceeding 2MB
     */
    public function test_feedback_rejects_large_screenshot(): void
    {
        Storage::fake('public');

        $consumer = User::factory()->create(['role' => 'consumer']);

        // Submit with 3MB file (3072 KB)
        $response = $this->actingAs($consumer)->post(route('consumer.feedback.store'), [
            'category' => 'ui_ux',
            'subject' => 'Desain Kurang Responsif',
            'description' => 'Detail produk kurang pas di resolusi kecil.',
            'rating' => 4,
            'screenshots' => [
                UploadedFile::fake()->create('large.png', 3072, 'image/png')
            ],
        ]);

        $response->assertSessionHasErrors(['screenshots.0']);
        $this->assertDatabaseCount('feedback', 0);
    }

    /**
     * Test mitra can view and submit feedback
     */
    public function test_mitra_can_submit_feedback(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra', 'is_verified' => true]);
        // Complete mitra profile/store dependencies if any, check if middleware profile.complete requires it
        // Let's create user profile for mitra to pass profile.complete middleware
        $mitra->profile()->create([
            'address' => 'Jl. Mitra Test No. 1',
            'phone' => '081234567890',
            'phone_verified_at' => now(),
            'business_name' => 'Toko Mitra Test',
            'business_contact' => '081234567891',
            'business_contact_verified_at' => now(),
            'operating_hours_start' => '08:00',
            'operating_hours_end' => '17:00',
        ]);

        $response = $this->actingAs($mitra)->get(route('mitra.feedback'));
        $response->assertStatus(200);

        $response = $this->actingAs($mitra)->post(route('mitra.feedback.store'), [
            'category' => 'fitur',
            'subject' => 'Saran Dashboard',
            'description' => 'Akan lebih baik jika ada grafik bulanan terpisah.',
            'rating' => 5,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('feedback', [
            'user_id' => $mitra->id,
            'category' => 'fitur',
            'rating' => 5,
        ]);
    }

    /**
     * Test lembaga can view and submit feedback
     */
    public function test_lembaga_can_submit_feedback(): void
    {
        $lembaga = User::factory()->create(['role' => 'lembaga', 'is_verified' => true]);
        $lembaga->profile()->create([
            'address' => 'Jl. Lembaga Test No. 1',
            'phone' => '081234567892',
            'phone_verified_at' => now(),
        ]);

        $response = $this->actingAs($lembaga)->get(route('lembaga.feedback'));
        $response->assertStatus(200);

        $response = $this->actingAs($lembaga)->post(route('lembaga.feedback.store'), [
            'category' => 'other',
            'subject' => 'Pelayanan Platform',
            'description' => 'Sangat senang menggunakan website ShareMeal.',
            'rating' => 5,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('feedback', [
            'user_id' => $lembaga->id,
            'category' => 'other',
            'rating' => 5,
        ]);
    }

    /**
     * Test admin can view, filter, search, and delete feedbacks
     */
    public function test_admin_can_manage_feedbacks(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $consumer = User::factory()->create(['role' => 'consumer', 'name' => 'John Consumer']);
        $mitra = User::factory()->create(['role' => 'mitra', 'name' => 'Alice Mitra']);

        $feedback1 = Feedback::create([
            'user_id' => $consumer->id,
            'category' => 'bug',
            'subject' => 'Error Checkout',
            'description' => 'Aplikasi error di halaman pembayaran.',
            'rating' => 2,
            'status' => 'pending',
        ]);

        $feedback2 = Feedback::create([
            'user_id' => $mitra->id,
            'category' => 'fitur',
            'subject' => 'Grafik Mitra',
            'description' => 'Saran penambahan analisis visual pendapatan.',
            'rating' => 5,
            'status' => 'resolved',
        ]);

        // 1. Admin Index Access
        $response = $this->actingAs($admin)->get(route('admin.feedbacks.index'));
        $response->assertStatus(200);
        $response->assertSee('Error Checkout');
        $response->assertSee('Grafik Mitra');

        // 2. Filter Category
        $response = $this->actingAs($admin)->get(route('admin.feedbacks.index', ['category' => 'bug']));
        $response->assertSee('Error Checkout');
        $response->assertDontSee('Grafik Mitra');

        // 3. Filter Rating
        $response = $this->actingAs($admin)->get(route('admin.feedbacks.index', ['rating' => '5']));
        $response->assertDontSee('Error Checkout');
        $response->assertSee('Grafik Mitra');

        // 4. Search
        $response = $this->actingAs($admin)->get(route('admin.feedbacks.index', ['search' => 'Alice']));
        $response->assertDontSee('Error Checkout');
        $response->assertSee('Grafik Mitra');

        // 5. Filter Status
        $response = $this->actingAs($admin)->get(route('admin.feedbacks.index', ['status' => 'resolved']));
        $response->assertDontSee('Error Checkout');
        $response->assertSee('Grafik Mitra');

        $response = $this->actingAs($admin)->get(route('admin.feedbacks.index', ['status' => 'pending']));
        $response->assertSee('Error Checkout');
        $response->assertDontSee('Grafik Mitra');

        // 6. Toggle Status (Pending -> Resolved)
        $response = $this->actingAs($admin)->post(route('admin.feedbacks.toggle-status', $feedback1));
        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals('resolved', $feedback1->fresh()->status);

        // Toggle Status (Resolved -> Pending)
        $response = $this->actingAs($admin)->post(route('admin.feedbacks.toggle-status', $feedback1));
        $response->assertRedirect();
        $this->assertEquals('pending', $feedback1->fresh()->status);

        // 7. Delete Feedback
        $response = $this->actingAs($admin)->delete(route('admin.feedbacks.delete', $feedback1));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('feedback', ['id' => $feedback1->id]);
        $this->assertDatabaseHas('feedback', ['id' => $feedback2->id]);
    }
}
