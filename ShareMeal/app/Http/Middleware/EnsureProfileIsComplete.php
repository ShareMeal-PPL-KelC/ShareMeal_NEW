<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureProfileIsComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->runningUnitTests() && !$request->headers->has('X-Test-Enforce-Profile-Complete')) {
            return $next($request);
        }

        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Only enforce for verified Mitra and Lembaga
        if ($user->is_verified) {
            $profile = $user->profile;

            if ($user->role === 'mitra') {
                $allowedRoutes = [
                    'mitra.profile',
                    'mitra.profile.update',
                    'mitra.profile.contact.verify',
                    'logout'
                ];

                $isComplete = $profile && 
                    !empty($profile->business_name) &&
                    !empty($profile->business_type) &&
                    !empty($profile->business_address) &&
                    !empty($profile->business_contact) &&
                    !empty($profile->business_opening_hours) &&
                    !empty($profile->business_description);

                if (!$isComplete && !in_array($request->route()?->getName(), $allowedRoutes, true)) {
                    return redirect()->route('mitra.profile')->with('error', 'Silakan lengkapi profil usaha Anda terlebih dahulu sebelum dapat mengakses halaman lain.');
                }
            }

            if ($user->role === 'lembaga') {
                $allowedRoutes = [
                    'profile.edit',
                    'profile.update',
                    'profile.phone.verify',
                    'logout'
                ];

                $isComplete = $profile && 
                    !empty($profile->phone) &&
                    !empty($profile->address);

                if (!$isComplete && !in_array($request->route()?->getName(), $allowedRoutes, true)) {
                    return redirect()->route('profile.edit')->with('error', 'Silakan lengkapi profil Anda terlebih dahulu sebelum dapat mengakses halaman lain.');
                }
            }
        }

        return $next($request);
    }
}
