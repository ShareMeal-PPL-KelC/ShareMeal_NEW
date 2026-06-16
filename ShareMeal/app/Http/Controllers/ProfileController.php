<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\ShareMealState;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function editProfile(): View|RedirectResponse
    {
        $user = Auth::user()?->load('profile');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login untuk mengelola profil.');
        }

        if ($user->role === 'consumer' && !$user->is_verified) {
            $user->update(['is_verified' => true]);
        }

        return view('pages.profile.edit', [
            'user' => $user,
            'profile' => $user->profile,
        ]);
    }

    protected function normalizePhone(?string $phone): ?string
    {
        return $phone === null ? null : preg_replace('/\D+/', '', $phone);
    }

    protected function profilePhoneOtpSessionKey(int $userId): string
    {
        return 'profile_phone_otp.' . $userId;
    }

    protected function businessContactOtpSessionKey(int $userId): string
    {
        return 'business_contact_otp.' . $userId;
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user()?->load('profile');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login untuk mengelola profil.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s]+$/u'],
            'phone' => ['required', 'string', 'regex:/^(08|62)\d{8,13}$/'],
            'address' => ['nullable', 'string', 'max:1000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ], [
            'name.regex' => 'Nama hanya boleh berisi huruf dan spasi.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.regex' => 'Nomor telepon harus berupa angka valid dengan awalan 08 atau 62 dan panjang 10-15 digit.',
            'avatar.image' => 'Foto profil harus berupa gambar.',
            'avatar.mimes' => 'Foto profil harus berformat JPG, JPEG, atau PNG.',
            'avatar.max' => 'Ukuran foto profil maksimal 2 MB.',
        ]);

        $phone = $this->normalizePhone($data['phone'] ?? null);
        $profile = $user->profile ?: $user->profile()->create([]);
        $currentPhone = $this->normalizePhone($profile->phone ?? $user->phone);
        $phoneChanged = $phone !== $currentPhone;

        if ($phoneChanged && $profile->phone_change_available_at && $profile->phone_change_available_at->isFuture()) {
            return back()
                ->withErrors(['phone' => 'Nomor telepon baru bisa diganti lagi pada ' . $profile->phone_change_available_at->format('H:i:s') . '.'])
                ->withInput();
        }

        $profileData = [
            'address' => $data['address'] ?? null,
        ];

        if ($request->hasFile('avatar')) {
            $oldAvatar = $user->profile?->avatar;
            $profileData['avatar'] = $request->file('avatar')->store('avatars', 'public');

            if ($oldAvatar && !str_starts_with($oldAvatar, 'http://') && !str_starts_with($oldAvatar, 'https://')) {
                Storage::disk('public')->delete($oldAvatar);
            }
        }

        if ($phoneChanged) {
            $otp = (string) random_int(100000, 999999);
            $profileData['pending_phone'] = $phone;
            $profileData['phone_otp_hash'] = Hash::make($otp);
            $profileData['phone_otp_expires_at'] = now()->addMinutes(5);
            $request->session()->put($this->profilePhoneOtpSessionKey($user->id), $otp);
        }

        $user->update(['name' => $data['name']]);
        $profile->update($profileData);

        ShareMealState::login($user->id);

        if ($phoneChanged) {
            return back()
                ->with('success', 'Profil berhasil diperbarui. Masukkan kode OTP untuk memverifikasi nomor telepon baru.');
        }

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function verifyProfilePhone(Request $request): RedirectResponse
    {
        $user = Auth::user()?->load('profile');

        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login untuk mengelola profil.');
        }

        $data = $request->validate([
            'otp' => ['required', 'digits:6'],
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.digits' => 'Kode OTP harus 6 digit angka.',
        ]);

        $profile = $user->profile;

        if (!$profile || !$profile->pending_phone || !$profile->phone_otp_hash) {
            return back()->with('error', 'Tidak ada nomor telepon yang menunggu verifikasi.');
        }

        if (!$profile->phone_otp_expires_at || $profile->phone_otp_expires_at->isPast()) {
            $request->session()->forget($this->profilePhoneOtpSessionKey($user->id));
            return back()->with('error', 'Kode OTP sudah kedaluwarsa. Simpan ulang profil untuk meminta kode baru.');
        }

        if (!Hash::check($data['otp'], $profile->phone_otp_hash)) {
            return back()->withErrors(['otp' => 'Kode OTP tidak sesuai.']);
        }

        $phone = $profile->pending_phone;

        $user->update(['phone' => $phone]);
        $profile->update([
            'phone' => $phone,
            'pending_phone' => null,
            'phone_otp_hash' => null,
            'phone_otp_expires_at' => null,
            'phone_verified_at' => now(),
            'phone_change_available_at' => now()->addMinute(),
        ]);

        if ($user->role === 'consumer' && !$user->is_verified) {
            $user->update(['is_verified' => true]);
        }

        $request->session()->forget($this->profilePhoneOtpSessionKey($user->id));
        ShareMealState::login($user->id);

        return back()->with('success', 'Nomor telepon berhasil diverifikasi.');
    }

    public function uploadBusinessDocument(Request $request): RedirectResponse
    {
        $request->validate([
            'document_ktp' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'document_siup' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'document_nib' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'document_halal' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
        ]);

        $userId = \Illuminate\Support\Facades\Session::get('sharemeal.current_user_id');
        $user = User::query()->find($userId);

        if (!$user) {
            return back()->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        $updates = [];
        if ($user->role === 'lembaga') {
            if ($request->hasFile('document_ktp')) {
                $updates['document_legalitas'] = $request->file('document_ktp')->store('documents', 'public');
            }
            if ($request->hasFile('document_siup')) {
                $updates['document_izin'] = $request->file('document_siup')->store('documents', 'public');
            }
            if ($request->hasFile('document_nib')) {
                $updates['document_identitas'] = $request->file('document_nib')->store('documents', 'public');
            }
        } else {
            foreach (['document_ktp', 'document_siup', 'document_nib', 'document_halal'] as $field) {
                if ($request->hasFile($field)) {
                    $updates[$field] = $request->file($field)->store('documents', 'public');
                }
            }
        }

        if (!empty($updates)) {
            // Reset verification status when re-uploading
            $updates['is_verified'] = false;
            $updates['verification_rejection_reason'] = null;
            $updates['status'] = 'active';

            $user->update($updates);

            // Notify Admins of the re-submission
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\ReverificationApplicationNotification($user));
            }

            return back()->with('success', 'Semua dokumen berhasil diunggah dan sedang menunggu verifikasi ulang.');
        }

        return back()->with('error', 'Gagal mengunggah dokumen.');
    }
}
