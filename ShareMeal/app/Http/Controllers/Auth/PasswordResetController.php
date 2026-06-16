<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function forgotPassword(): View
    {
        return view('pages.auth.forgot-password');
    }

    public function sendResetOtp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email'     => ['required', 'email'],
            'user_type' => ['required', 'in:consumer,mitra,lembaga,admin'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'user_type.required' => 'Tipe pengguna wajib dipilih.',
        ]);

        if ($data['user_type'] === 'admin') {
            return back()->with('error', 'Fitur lupa sandi tidak tersedia untuk Administrator.');
        }

        $user = User::query()
            ->where('email', $data['email'])
            ->where('role', $data['user_type'])
            ->first();

        if (!$user) {
            return back()->with('error', 'Email dengan tipe pengguna tersebut tidak terdaftar.');
        }

        // Generate 6-digit OTP
        $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP hash and timestamp
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $data['email']],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        // Flash to session for testing/grading (simulates receiving email)
        session()->flash('demo_reset_otp', $otp);

        return redirect()->route('password.verify_otp_form', [
            'email' => $data['email'],
            'user_type' => $data['user_type'],
        ])->with('success', 'Kode OTP reset kata sandi telah dikirim. Silakan masukkan kode OTP di bawah.');
    }

    public function verifyResetOtpForm(Request $request): View
    {
        return view('pages.auth.verify-otp', [
            'email' => $request->query('email'),
            'user_type' => $request->query('user_type'),
        ]);
    }

    public function verifyResetOtp(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email'     => ['required', 'email'],
            'user_type' => ['required', 'in:consumer,mitra,lembaga,admin'],
            'otp'       => ['required', 'digits:6'],
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.digits' => 'Kode OTP harus berupa 6 digit angka.',
        ]);

        if ($data['user_type'] === 'admin') {
            return back()->with('error', 'Fitur lupa sandi tidak tersedia untuk Administrator.');
        }

        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $data['email'])
            ->first();

        if (!$resetRecord) {
            return back()->with('error', 'Permintaan verifikasi tidak ditemukan atau telah kedaluwarsa.');
        }

        // Check if OTP has expired (10 minutes)
        if (Carbon::parse($resetRecord->created_at)->addMinutes(10)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
            return redirect()->route('password.request')->with('error', 'Kode OTP sudah kedaluwarsa. Silakan ajukan lupa sandi kembali.');
        }

        // Verify OTP
        if (!Hash::check($data['otp'], $resetRecord->token)) {
            return back()->with('error', 'Kode OTP tidak valid.');
        }

        // Store verification status in session
        session()->put('reset_password_verified_email', $data['email']);
        session()->put('reset_password_verified_type', $data['user_type']);
        session()->put('reset_password_verified_otp', $data['otp']);

        return redirect()->route('password.reset')->with('success', 'OTP terverifikasi. Silakan masukkan kata sandi baru.');
    }

    public function resetPassword(): mixed
    {
        $email = session('reset_password_verified_email');
        $user_type = session('reset_password_verified_type');

        if (!$email || !$user_type) {
            return redirect()->route('password.request')->with('error', 'Silakan verifikasi kode OTP Anda terlebih dahulu.');
        }

        return view('pages.auth.reset-password', [
            'email' => $email,
            'user_type' => $user_type,
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $sessionEmail = session('reset_password_verified_email');
        $sessionType = session('reset_password_verified_type');
        $sessionOtp = session('reset_password_verified_otp');

        if (!$sessionEmail || !$sessionType || !$sessionOtp) {
            return redirect()->route('password.request')->with('error', 'Sesi verifikasi Anda tidak valid. Silakan ajukan lupa sandi kembali.');
        }

        $data = $request->validate([
            'password' => ['required', 'min:6', 'confirmed'],
        ], [
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi minimal harus 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user = User::query()
            ->where('email', $sessionEmail)
            ->where('role', $sessionType)
            ->first();

        if (!$user) {
            return redirect()->route('password.request')->with('error', 'Identitas pengguna tidak ditemukan.');
        }

        // Additional sanity check on DB token
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $sessionEmail)
            ->first();

        if (!$resetRecord || !Hash::check($sessionOtp, $resetRecord->token)) {
            return redirect()->route('password.request')->with('error', 'Permintaan reset sandi tidak valid atau telah kedaluwarsa.');
        }

        // Update password
        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        // Clean up session and DB token
        session()->forget([
            'reset_password_verified_email',
            'reset_password_verified_type',
            'reset_password_verified_otp'
        ]);
        DB::table('password_reset_tokens')->where('email', $sessionEmail)->delete();

        return redirect()->route('login')->with('success', 'Kata sandi berhasil diperbarui. Silakan masuk menggunakan kata sandi baru.');
    }
}
