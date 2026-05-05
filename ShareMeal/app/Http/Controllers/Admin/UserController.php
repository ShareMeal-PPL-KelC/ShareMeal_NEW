<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Filter Pencarian
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Filter Role
        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        $allUsers = User::all();
        $users = $query->latest()->get();

        return view('admin.users.index', compact('users', 'allUsers'));
    }

    public function warn(User $user)
    {
        $user->increment('warnings_count');
        $user->update(['status' => 'warned']);
        return redirect()->back()->with('success', 'Peringatan berhasil dikirim!');
    }

    public function block(User $user)
    {
        $user->update(['status' => 'blocked']);
        return redirect()->back()->with('success', 'Akun telah diblokir!');
    }

    public function unblock(User $user)
    {
        $user->update(['status' => 'active', 'warnings_count' => 0]);
        return redirect()->back()->with('success', 'Blokir akun telah dibuka.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->back()->with('success', 'Pengguna berhasil dihapus!');
    }
}
