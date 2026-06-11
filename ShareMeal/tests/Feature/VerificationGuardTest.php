<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Donation;
use App\Models\CartItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificationGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_mitra_products_are_hidden_from_dashboard(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);
        $unverifiedMitra = User::factory()->create([
            'role' => 'mitra',
            'is_verified' => false,
        ]);
        
        $product = Product::create([
            'user_id' => $unverifiedMitra->id,
            'name' => 'Roti Unverified',
            'price' => 5000,
            'stock' => 10,
            'status' => 'flash-sale',
            'expires_at' => now()->addHours(2),
            'category' => 'Bakery',
        ]);

        $response = $this->actingAs($consumer)->get(route('consumer.dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('Roti Unverified');
    }

    public function test_unverified_mitra_stores_are_hidden_from_search(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);
        $unverifiedMitra = User::factory()->create([
            'role' => 'mitra',
            'name' => 'Toko Roti Unverified',
            'is_verified' => false,
        ]);

        $response = $this->actingAs($consumer)->get(route('consumer.search'));

        $response->assertStatus(200);
        $response->assertDontSee('Toko Roti Unverified');
    }

    public function test_consumer_cannot_add_unverified_mitra_product_to_cart(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);
        $unverifiedMitra = User::factory()->create([
            'role' => 'mitra',
            'is_verified' => false,
        ]);

        $product = Product::create([
            'user_id' => $unverifiedMitra->id,
            'name' => 'Roti Unverified',
            'price' => 5000,
            'stock' => 10,
            'status' => 'flash-sale',
            'expires_at' => now()->addHours(2),
            'category' => 'Bakery',
        ]);

        $response = $this->actingAs($consumer)->post(route('consumer.cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Toko Mitra ini belum terverifikasi atau telah ditolak oleh admin.');
        
        // Assert cart is empty
        $this->assertEquals(0, CartItem::where('user_id', $consumer->id)->count());
    }

    public function test_consumer_cannot_checkout_unverified_mitra_products(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);
        $unverifiedMitra = User::factory()->create([
            'role' => 'mitra',
            'is_verified' => false,
        ]);

        $product = Product::create([
            'user_id' => $unverifiedMitra->id,
            'name' => 'Roti Unverified',
            'price' => 5000,
            'stock' => 10,
            'status' => 'flash-sale',
            'expires_at' => now()->addHours(2),
            'category' => 'Bakery',
        ]);

        // Put in cart (bypass addToCart controller guard directly via DB)
        CartItem::create([
            'user_id' => $consumer->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'expires_at' => now()->addMinutes(5),
        ]);

        $response = $this->actingAs($consumer)->post(route('consumer.checkout.store'), [
            'mitra_id' => $unverifiedMitra->id,
            'receiving_method' => 'pickup',
            'payment_method' => 'qris',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Mitra ini belum diverifikasi atau ditolak oleh admin.');
    }

    public function test_unverified_lembaga_cannot_claim_donation(): void
    {
        $unverifiedLembaga = User::factory()->create([
            'role' => 'lembaga',
            'is_verified' => false,
        ]);
        
        $mitra = User::factory()->create(['role' => 'mitra']);

        $donation = Donation::create([
            'mitra_id' => $mitra->id,
            'title' => 'Donasi Surplus Roti',
            'quantity' => 5,
            'unit' => 'box',
            'status' => 'pending',
            'expires_at' => now()->addHours(5),
        ]);

        $response = $this->actingAs($unverifiedLembaga)->post(route('lembaga.donations.claim', $donation->id), [
            'pickup_time' => '10:00',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Klaim donasi gagal. Akun Lembaga Anda belum terverifikasi atau telah ditolak oleh admin.');
        
        $donation->refresh();
        $this->assertEquals('pending', $donation->status);
    }

    public function test_unverified_mitra_can_login_and_access_dashboard(): void
    {
        $unverifiedMitra = User::factory()->create([
            'role' => 'mitra',
            'is_verified' => false,
        ]);

        $response = $this->actingAs($unverifiedMitra)->get(route('mitra.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Akun Sedang Diverifikasi Admin');
    }

    public function test_rejected_mitra_can_login_and_access_dashboard(): void
    {
        $rejectedMitra = User::factory()->create([
            'role' => 'mitra',
            'is_verified' => false,
            'verification_rejection_reason' => 'Dokumen SIUP buram',
        ]);

        $response = $this->actingAs($rejectedMitra)->get(route('mitra.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Pengajuan Verifikasi Toko Ditolak');
        $response->assertSee('Dokumen SIUP buram');
    }

    public function test_unverified_mitra_cannot_add_product_to_inventory(): void
    {
        $unverifiedMitra = User::factory()->create([
            'role' => 'mitra',
            'is_verified' => false,
        ]);

        $response = $this->actingAs($unverifiedMitra)->post(route('mitra.inventory.store'), [
            'name' => 'Roti Unverified Baru',
            'category' => 'Bakery',
            'price' => 5000,
            'stock' => 10,
            'expires_at' => now()->addHours(3)->format('Y-m-d\TH:i'),
            'pickup_start_time' => '08:00',
            'pickup_end_time' => '10:00',
            'status' => 'normal',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Akun Anda belum terverifikasi. Anda tidak dapat menambahkan produk ke inventaris.');
        
        // Assert no product was created
        $this->assertEquals(0, Product::where('user_id', $unverifiedMitra->id)->count());
    }

    public function test_unverified_mitra_cannot_add_donation(): void
    {
        $unverifiedMitra = User::factory()->create([
            'role' => 'mitra',
            'is_verified' => false,
        ]);

        $response = $this->actingAs($unverifiedMitra)->post(route('mitra.donations.store'), [
            'title' => 'Donasi Roti Unverified',
            'quantity' => 5,
            'unit' => 'box',
            'expires_at' => now()->addDays(2)->format('Y-m-d'),
            'pickup_start_time' => '08:00',
            'pickup_end_time' => '10:00',
            'description' => 'Donasi roti dari unverified mitra',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Akun Anda belum terverifikasi. Anda tidak dapat menambahkan donasi baru.');

        // Assert no donation was created
        $this->assertEquals(0, Donation::where('mitra_id', $unverifiedMitra->id)->count());
    }

    public function test_verified_mitra_with_incomplete_profile_is_redirected_to_profile_page(): void
    {
        $mitra = User::factory()->create([
            'role' => 'mitra',
            'is_verified' => true,
        ]);
        $mitra->profile()->create([
            'phone' => '081234567890',
        ]);

        $response = $this->actingAs($mitra)
            ->withHeader('X-Test-Enforce-Profile-Complete', '1')
            ->get(route('mitra.dashboard'));

        $response->assertRedirect(route('mitra.profile'));
        $response->assertSessionHas('error', 'Silakan lengkapi profil usaha Anda terlebih dahulu sebelum dapat mengakses halaman lain.');
    }

    public function test_verified_mitra_with_complete_profile_can_access_dashboard(): void
    {
        $mitra = User::factory()->create([
            'role' => 'mitra',
            'is_verified' => true,
        ]);
        $mitra->profile()->create([
            'phone' => '081234567890',
            'business_name' => 'Toko Roti Hendra',
            'business_type' => 'Bakery',
            'business_address' => 'Jl. Sukabirus No. 45',
            'business_contact' => '089876543210',
            'business_opening_hours' => '08:00 - 20:00',
            'business_description' => 'Toko roti terlezat',
        ]);

        $response = $this->actingAs($mitra)
            ->withHeader('X-Test-Enforce-Profile-Complete', '1')
            ->get(route('mitra.dashboard'));

        $response->assertStatus(200);
    }

    public function test_verified_lembaga_with_incomplete_profile_is_redirected_to_profile_page(): void
    {
        $lembaga = User::factory()->create([
            'role' => 'lembaga',
            'is_verified' => true,
        ]);

        $response = $this->actingAs($lembaga)
            ->withHeader('X-Test-Enforce-Profile-Complete', '1')
            ->get(route('lembaga.dashboard'));

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('error', 'Silakan lengkapi profil Anda terlebih dahulu sebelum dapat mengakses halaman lain.');
    }

    public function test_verified_lembaga_with_complete_profile_can_access_dashboard(): void
    {
        $lembaga = User::factory()->create([
            'role' => 'lembaga',
            'is_verified' => true,
        ]);
        $lembaga->profile()->create([
            'phone' => '081234567891',
            'address' => 'Jl. Buah Batu No. 100',
        ]);

        $response = $this->actingAs($lembaga)
            ->withHeader('X-Test-Enforce-Profile-Complete', '1')
            ->get(route('lembaga.dashboard'));

        $response->assertStatus(200);
    }

    public function test_consumer_with_incomplete_profile_cannot_add_to_cart(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);
        $consumer->profile()->create([]);

        $mitra = User::factory()->create(['role' => 'mitra', 'is_verified' => true]);
        $product = Product::create([
            'user_id' => $mitra->id,
            'name' => 'Roti Manis',
            'price' => 5000,
            'stock' => 10,
            'expires_at' => now()->addHours(3),
            'category' => 'Bakery',
        ]);

        $response = $this->actingAs($consumer)
            ->withHeader('X-Test-Enforce-Profile-Complete', '1')
            ->post(route('consumer.cart.add'), [
                'product_id' => $product->id,
                'quantity' => 1,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Silakan lengkapi profil (nomor telepon dan alamat) Anda terlebih dahulu sebelum dapat memesan makanan.');
    }

    public function test_consumer_with_incomplete_profile_cannot_access_checkout(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);
        $consumer->profile()->create([]);

        $response = $this->actingAs($consumer)
            ->withHeader('X-Test-Enforce-Profile-Complete', '1')
            ->get(route('consumer.checkout'));

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('error', 'Silakan lengkapi profil (nomor telepon dan alamat) Anda terlebih dahulu sebelum dapat memesan makanan.');
    }

    public function test_consumer_with_incomplete_profile_cannot_store_order(): void
    {
        $consumer = User::factory()->create(['role' => 'consumer']);
        $consumer->profile()->create([]);

        $response = $this->actingAs($consumer)
            ->withHeader('X-Test-Enforce-Profile-Complete', '1')
            ->post(route('consumer.checkout.store'), [
                'mitra_id' => 1,
                'receiving_method' => 'pickup',
                'payment_method' => 'qris',
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('error', 'Silakan lengkapi profil (nomor telepon dan alamat) Anda terlebih dahulu sebelum dapat memesan makanan.');
    }
}


