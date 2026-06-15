<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ShareMealState;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function landing(): View
    {
        Auth::logout();
        ShareMealState::logout();

        return view('pages.landing');
    }

    public function login(): View
    {
        return view('pages.auth.login');
    }

    public function doLogin(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email'     => ['required', 'email'],
            'password'  => ['required'],
            'user_type' => ['required', 'in:consumer,mitra,lembaga,admin'],
        ]);

        $user = User::query()
            ->where('email', $data['email'])
            ->where('role', $data['user_type'])
            ->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return back()->with('error', 'Email, password, atau tipe pengguna tidak sesuai.');
        }

        Auth::login($user, $request->boolean('remember'));
        ShareMealState::login($user->id);

        $request->session()->regenerate();

        if ($data['user_type'] === 'mitra') {
            return redirect()->route('mitra.dashboard');
        }

        return redirect()->route($data['user_type'] . '.dashboard');
    }

    public function register(): View
    {
        return view('pages.auth.register');
    }

    public function doRegister(Request $request): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:6', 'confirmed'],
            'user_type' => ['required', 'in:consumer,mitra,lembaga'],
            'terms' => ['accepted'],
        ];

        if ($request->user_type === 'mitra') {
            $rules['organization_name'] = ['required', 'string', 'regex:/^[a-zA-Z0-9\s]+$/'];
            $rules['document_ktp_mitra'] = ['required', 'file', 'mimes:jpg,png,pdf', 'max:2048'];
            $rules['document_siup_mitra'] = ['required', 'file', 'mimes:jpg,png,pdf', 'max:2048'];
            $rules['document_nib_mitra'] = ['required', 'file', 'mimes:jpg,png,pdf', 'max:2048'];
            $rules['document_halal_mitra'] = ['nullable', 'file', 'mimes:jpg,png,pdf', 'max:2048'];
        } elseif ($request->user_type === 'lembaga') {
            $rules['organization_name'] = ['required', 'string', 'regex:/^[a-zA-Z0-9\s]+$/'];
            $rules['document_legalitas_lembaga'] = ['required', 'file', 'mimes:jpg,png,pdf', 'max:2048'];
            $rules['document_izin_lembaga'] = ['required', 'file', 'mimes:jpg,png,pdf', 'max:2048'];
            $rules['document_identitas_lembaga'] = ['required', 'file', 'mimes:jpg,png,pdf', 'max:2048'];
        }

        $data = $request->validate($rules, [
            'name.regex' => 'Nama hanya boleh berisi huruf dan spasi.',
            'organization_name.required' => 'Nama mitra atau nama lembaga wajib diisi.',
            'organization_name.regex' => 'Nama mitra atau nama lembaga hanya boleh berisi huruf, angka, dan spasi.',
        ]);

        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['user_type'],
            'status' => 'active',
            'phone' => null,
            'organization_name' => in_array($data['user_type'], ['mitra', 'lembaga'], true) ? $data['organization_name'] : null,
            'joined_at' => now()->toDateString(),
            'transactions_count' => 0,
            'warnings_count' => 0,
            'is_verified' => $data['user_type'] === 'consumer',
        ];

        // Process file uploads
        if ($data['user_type'] === 'mitra') {
            $userData['document_ktp'] = $request->file('document_ktp_mitra')->store('documents', 'public');
            $userData['document_siup'] = $request->file('document_siup_mitra')->store('documents', 'public');
            $userData['document_nib'] = $request->file('document_nib_mitra')->store('documents', 'public');
            if ($request->hasFile('document_halal_mitra')) {
                $userData['document_halal'] = $request->file('document_halal_mitra')->store('documents', 'public');
            }
        } elseif ($data['user_type'] === 'lembaga') {
            $userData['document_legalitas'] = $request->file('document_legalitas_lembaga')->store('documents', 'public');
            $userData['document_izin'] = $request->file('document_izin_lembaga')->store('documents', 'public');
            $userData['document_identitas'] = $request->file('document_identitas_lembaga')->store('documents', 'public');
        }

        $user = User::query()->create($userData);

        if (in_array($user->role, ['mitra', 'lembaga'], true)) {
            $admins = User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\NewVerificationApplicationNotification($user));
            }
        }

        $successMessage = $data['user_type'] === 'consumer' 
            ? 'Registrasi berhasil. Silakan masuk menggunakan akun Anda.' 
            : 'Registrasi berhasil. Akun Anda sedang dalam proses verifikasi oleh admin.';

        return redirect()->route('login')->with('success', $successMessage);
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        ShareMealState::logout();
        return redirect()->route('login')->with('success', 'Anda telah keluar.');
    }
}
