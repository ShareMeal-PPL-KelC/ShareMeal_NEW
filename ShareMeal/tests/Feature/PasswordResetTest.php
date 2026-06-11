<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_is_accessible(): void
    {
        $response = $this->get(route('password.request'));
        $response->assertStatus(200);
        $response->assertSee('Lupa Sandi?');
    }

    public function test_forgot_password_requires_existing_user_and_type(): void
    {
        $response = $this->post(route('password.email'), [
            'email' => 'doesnotexist@sharemeal.com',
            'user_type' => 'consumer',
        ]);

        $response->assertSessionHas('error', 'Email dengan tipe pengguna tersebut tidak terdaftar.');
    }

    public function test_forgot_password_generates_otp_and_redirects_to_verify_otp_page(): void
    {
        $user = User::factory()->create([
            'email' => 'consumer@sharemeal.com',
            'role' => 'consumer',
        ]);

        $response = $this->post(route('password.email'), [
            'email' => 'consumer@sharemeal.com',
            'user_type' => 'consumer',
        ]);

        $response->assertRedirect(route('password.verify_otp_form', [
            'email' => 'consumer@sharemeal.com',
            'user_type' => 'consumer',
        ]));

        $response->assertSessionHas('demo_reset_otp');
        $otp = session('demo_reset_otp');
        $this->assertEquals(6, strlen($otp));

        // Assert DB has hash token
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', 'consumer@sharemeal.com')
            ->first();

        $this->assertNotNull($resetRecord);
        $this->assertTrue(Hash::check($otp, $resetRecord->token));
    }

    public function test_verify_otp_page_prefills_correctly(): void
    {
        $response = $this->get(route('password.verify_otp_form', [
            'email' => 'test@sharemeal.com',
            'user_type' => 'consumer',
        ]));

        $response->assertStatus(200);
        $response->assertSee('test@sharemeal.com');
    }

    public function test_verify_otp_fails_with_invalid_otp(): void
    {
        $user = User::factory()->create([
            'email' => 'consumer@sharemeal.com',
            'role' => 'consumer',
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => 'consumer@sharemeal.com',
            'token' => Hash::make('123456'),
            'created_at' => now(),
        ]);

        $response = $this->post(route('password.verify_otp'), [
            'email' => 'consumer@sharemeal.com',
            'user_type' => 'consumer',
            'otp' => '654321', // Wrong OTP
        ]);

        $response->assertSessionHas('error', 'Kode OTP tidak valid.');
    }

    public function test_verify_otp_fails_with_expired_otp(): void
    {
        $user = User::factory()->create([
            'email' => 'consumer@sharemeal.com',
            'role' => 'consumer',
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => 'consumer@sharemeal.com',
            'token' => Hash::make('123456'),
            'created_at' => now()->subMinutes(11),
        ]);

        $response = $this->post(route('password.verify_otp'), [
            'email' => 'consumer@sharemeal.com',
            'user_type' => 'consumer',
            'otp' => '123456',
        ]);

        $response->assertRedirect(route('password.request'));
        $response->assertSessionHas('error', 'Kode OTP sudah kedaluwarsa. Silakan ajukan lupa sandi kembali.');
    }

    public function test_reset_password_page_fails_without_otp_verification_session(): void
    {
        $response = $this->get(route('password.reset'));
        $response->assertRedirect(route('password.request'));
        $response->assertSessionHas('error', 'Silakan verifikasi kode OTP Anda terlebih dahulu.');
    }

    public function test_update_password_fails_without_otp_verification_session(): void
    {
        $response = $this->post(route('password.update'), [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('password.request'));
        $response->assertSessionHas('error', 'Sesi verifikasi Anda tidak valid. Silakan ajukan lupa sandi kembali.');
    }

    public function test_reset_flow_succeeds_and_allows_login(): void
    {
        $password = 'oldpassword123';
        $user = User::factory()->create([
            'email' => 'consumer@sharemeal.com',
            'role' => 'consumer',
            'password' => Hash::make($password),
        ]);

        // 1. Submit Forgot Password Form
        $this->post(route('password.email'), [
            'email' => 'consumer@sharemeal.com',
            'user_type' => 'consumer',
        ]);
        $otp = session('demo_reset_otp');

        // 2. Submit OTP Verify Form
        $verifyResponse = $this->post(route('password.verify_otp'), [
            'email' => 'consumer@sharemeal.com',
            'user_type' => 'consumer',
            'otp' => $otp,
        ]);
        $verifyResponse->assertRedirect(route('password.reset'));

        // 3. Access Reset Password Form (should pass session guard)
        $resetPageResponse = $this->get(route('password.reset'));
        $resetPageResponse->assertStatus(200);

        // 4. Submit New Password Form
        $updateResponse = $this->post(route('password.update'), [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);
        $updateResponse->assertRedirect(route('login'));
        $updateResponse->assertSessionHas('success', 'Kata sandi berhasil diperbarui. Silakan masuk menggunakan kata sandi baru.');

        // Verify old login fails
        $loginFailResponse = $this->post(route('login.submit'), [
            'email' => 'consumer@sharemeal.com',
            'password' => $password,
            'user_type' => 'consumer',
        ]);
        $loginFailResponse->assertSessionHas('error');

        // Verify new login succeeds
        $loginSuccessResponse = $this->post(route('login.submit'), [
            'email' => 'consumer@sharemeal.com',
            'password' => 'newpassword123',
            'user_type' => 'consumer',
        ]);
        $loginSuccessResponse->assertRedirect(route('consumer.dashboard'));
        $this->assertTrue(Auth::check());
    }

    public function test_administrator_cannot_request_reset_otp(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@sharemeal.com',
            'role' => 'admin',
        ]);

        $response = $this->post(route('password.email'), [
            'email' => 'admin@sharemeal.com',
            'user_type' => 'admin',
        ]);

        $response->assertSessionHas('error', 'Fitur lupa sandi tidak tersedia untuk Administrator.');
    }
}
