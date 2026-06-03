<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Pbi29BusinessProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_mitra_can_update_business_profile(): void
    {
        Storage::fake('public');

        $mitra = User::factory()->create([
            'role' => 'mitra',
            'status' => 'active',
            'is_verified' => true,
        ]);

        $response = $this->actingAs($mitra)->post(route('mitra.profile.update'), [
            'business_name' => 'Roti Makmur',
            'business_type' => 'Bakery',
            'business_address' => 'Jl. Roti No. 10',
            'business_contact' => '081234567890',
            'opening_start' => '08:00',
            'opening_end' => '20:00',
            'business_description' => 'Menjual roti segar dan paket surplus harian.',
            'store_image' => UploadedFile::fake()->create('store.jpg', 100, 'image/jpeg'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Profil usaha berhasil diperbarui. Masukkan kode OTP untuk memverifikasi kontak usaha baru.');
        $response->assertSessionHas('business_contact_otp.' . $mitra->id);

        $mitra->refresh();

        $this->assertSame('Roti Makmur', $mitra->organization_name);
        $this->assertSame('Roti Makmur', $mitra->profile->business_name);
        $this->assertSame('Bakery', $mitra->profile->business_type);
        $this->assertSame('Jl. Roti No. 10', $mitra->profile->business_address);
        $this->assertNull($mitra->profile->business_contact);
        $this->assertSame('081234567890', $mitra->profile->business_pending_contact);
        $this->assertSame('08:00 - 20:00', $mitra->profile->business_opening_hours);
        $this->assertSame('Menjual roti segar dan paket surplus harian.', $mitra->profile->business_description);
        Storage::disk('public')->assertExists($mitra->profile->avatar);

        $otp = session('business_contact_otp.' . $mitra->id);

        $verifyResponse = $this->actingAs($mitra)->post(route('mitra.profile.contact.verify'), [
            'otp' => $otp,
        ]);

        $verifyResponse->assertSessionHas('success', 'Kontak usaha berhasil diverifikasi.');

        $mitra->refresh();

        $this->assertSame('081234567890', $mitra->profile->business_contact);
        $this->assertNull($mitra->profile->business_pending_contact);
        $this->assertNotNull($mitra->profile->business_contact_verified_at);
        $this->assertTrue($mitra->profile->business_contact_change_available_at->isFuture());
    }

    public function test_business_profile_rejects_invalid_contact_and_hours(): void
    {
        $mitra = User::factory()->create([
            'role' => 'mitra',
            'status' => 'active',
            'is_verified' => true,
        ]);

        $response = $this->actingAs($mitra)->post(route('mitra.profile.update'), [
            'business_name' => 'Roti Makmur',
            'business_type' => 'Bakery',
            'business_address' => 'Jl. Roti No. 10',
            'business_contact' => 'abc',
            'opening_start' => '20:00',
            'opening_end' => '08:00',
            'business_description' => 'Menjual roti segar.',
        ]);

        $response->assertSessionHasErrors(['business_contact', 'opening_end']);
    }

    public function test_non_mitra_cannot_open_business_profile(): void
    {
        $consumer = User::factory()->create([
            'role' => 'consumer',
            'status' => 'active',
            'is_verified' => true,
        ]);

        $this->actingAs($consumer)
            ->get(route('mitra.profile'))
            ->assertRedirect(route('consumer.dashboard'));
    }

    public function test_mitra_account_dropdown_links_to_business_profile(): void
    {
        $mitra = User::factory()->create([
            'role' => 'mitra',
            'status' => 'active',
            'is_verified' => true,
        ]);

        $this->actingAs($mitra)
            ->get(route('mitra.dashboard'))
            ->assertOk()
            ->assertSee('Pengaturan Profil Usaha')
            ->assertSee(route('mitra.profile'), false);
    }

    public function test_wrong_business_contact_otp_does_not_verify_contact(): void
    {
        $mitra = User::factory()->create([
            'role' => 'mitra',
            'status' => 'active',
            'is_verified' => true,
        ]);

        $mitra->profile()->create([
            'business_contact' => '081234567890',
            'business_pending_contact' => '081111111111',
            'business_contact_otp_hash' => bcrypt('123456'),
            'business_contact_otp_expires_at' => now()->addMinutes(5),
        ]);

        session(['business_contact_otp.' . $mitra->id => '123456']);

        $response = $this->actingAs($mitra)->post(route('mitra.profile.contact.verify'), [
            'otp' => '654321',
        ]);

        $response->assertSessionHasErrors(['otp']);
        $response->assertSessionHas('business_contact_otp.' . $mitra->id, '123456');

        $mitra->refresh();

        $this->assertSame('081234567890', $mitra->profile->business_contact);
        $this->assertSame('081111111111', $mitra->profile->business_pending_contact);
    }

    public function test_mitra_cannot_change_business_contact_during_cooldown(): void
    {
        $mitra = User::factory()->create([
            'role' => 'mitra',
            'status' => 'active',
            'is_verified' => true,
        ]);

        $mitra->profile()->create([
            'business_contact' => '081234567890',
            'business_contact_verified_at' => now(),
            'business_contact_change_available_at' => now()->addMinute(),
        ]);

        $response = $this->actingAs($mitra)->post(route('mitra.profile.update'), [
            'business_name' => 'Roti Makmur',
            'business_type' => 'Bakery',
            'business_address' => 'Jl. Roti No. 10',
            'business_contact' => '081111111111',
            'opening_start' => '08:00',
            'opening_end' => '20:00',
            'business_description' => 'Menjual roti segar.',
        ]);

        $response->assertSessionHasErrors(['business_contact']);
    }
}
