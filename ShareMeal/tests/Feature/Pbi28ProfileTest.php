<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Pbi28ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_personal_profile_data(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'name' => 'Nama Lama',
            'phone' => null,
            'role' => 'consumer',
            'status' => 'active',
            'is_verified' => true,
        ]);

        $avatar = UploadedFile::fake()->create('avatar.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($user)->post(route('profile.update'), [
            'name' => 'Nama Baru',
            'phone' => '081234567890',
            'address' => 'Jl. Contoh No. 10',
            'avatar' => $avatar,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Profil berhasil diperbarui. Masukkan kode OTP untuk memverifikasi nomor telepon baru.');
        $response->assertSessionHas('profile_phone_otp.' . $user->id);

        $user->refresh();

        $this->assertSame('Nama Baru', $user->name);
        $this->assertNull($user->phone);
        $this->assertNotNull($user->profile);
        $this->assertNull($user->profile->phone);
        $this->assertSame('081234567890', $user->profile->pending_phone);
        $this->assertSame('Jl. Contoh No. 10', $user->profile->address);
        Storage::disk('public')->assertExists($user->profile->avatar);

        $otp = session('profile_phone_otp.' . $user->id);

        $verifyResponse = $this->actingAs($user)->post(route('profile.phone.verify'), [
            'otp' => $otp,
        ]);

        $verifyResponse->assertRedirect();
        $verifyResponse->assertSessionHas('success', 'Nomor telepon berhasil diverifikasi.');

        $user->refresh();

        $this->assertSame('081234567890', $user->phone);
        $this->assertSame('081234567890', $user->profile->phone);
        $this->assertNull($user->profile->pending_phone);
        $this->assertNotNull($user->profile->phone_verified_at);
        $this->assertTrue($user->profile->phone_change_available_at->isFuture());
        $this->assertNull(session('profile_phone_otp.' . $user->id));
    }

    public function test_profile_validation_rejects_invalid_name_phone_and_avatar(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => 'consumer',
            'status' => 'active',
            'is_verified' => true,
        ]);

        $response = $this->actingAs($user)->post(route('profile.update'), [
            'name' => 'Budi 123',
            'phone' => '08abc',
            'address' => 'Jl. Contoh',
            'avatar' => UploadedFile::fake()->create('avatar.webp', 100, 'image/webp'),
        ]);

        $response->assertSessionHasErrors(['name', 'phone', 'avatar']);
    }

    public function test_user_cannot_change_phone_during_cooldown(): void
    {
        $user = User::factory()->create([
            'phone' => '081234567890',
            'role' => 'consumer',
            'status' => 'active',
            'is_verified' => true,
        ]);

        $user->profile()->create([
            'phone' => '081234567890',
            'phone_verified_at' => now(),
            'phone_change_available_at' => now()->addMinute(),
        ]);

        $response = $this->actingAs($user)->post(route('profile.update'), [
            'name' => 'Nama Baru',
            'phone' => '081111111111',
            'address' => 'Jl. Contoh',
        ]);

        $response->assertSessionHasErrors(['phone']);
    }

    public function test_wrong_otp_does_not_verify_pending_phone(): void
    {
        $user = User::factory()->create([
            'phone' => '081234567890',
            'role' => 'consumer',
            'status' => 'active',
            'is_verified' => true,
        ]);

        $user->profile()->create([
            'phone' => '081234567890',
            'pending_phone' => '081111111111',
            'phone_otp_hash' => bcrypt('123456'),
            'phone_otp_expires_at' => now()->addMinutes(5),
        ]);

        session(['profile_phone_otp.' . $user->id => '123456']);

        $response = $this->actingAs($user)->post(route('profile.phone.verify'), [
            'otp' => '654321',
        ]);

        $response->assertSessionHasErrors(['otp']);
        $response->assertSessionHas('profile_phone_otp.' . $user->id, '123456');

        $user->refresh();

        $this->assertSame('081234567890', $user->phone);
        $this->assertSame('081234567890', $user->profile->phone);
        $this->assertSame('081111111111', $user->profile->pending_phone);

        $verifyResponse = $this->actingAs($user)->post(route('profile.phone.verify'), [
            'otp' => '123456',
        ]);

        $verifyResponse->assertSessionHas('success', 'Nomor telepon berhasil diverifikasi.');

        $user->refresh();

        $this->assertSame('081111111111', $user->phone);
        $this->assertNull(session('profile_phone_otp.' . $user->id));
    }

    public function test_guest_is_redirected_when_opening_profile_page(): void
    {
        $this->get(route('profile.edit'))
            ->assertRedirect(route('login'));
    }
}
