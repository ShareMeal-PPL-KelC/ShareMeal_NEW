<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $userRoles = explode('|', $role);

        if (!in_array(Auth::user()->role, $userRoles)) {
            // Redirect to appropriate dashboard based on user's actual role
            switch (Auth::user()->role) {
                case 'admin':
                    return redirect()->route('admin.dashboard');
                case 'mitra':
                    return redirect()->route('mitra.dashboard');
                case 'lembaga':
                    return redirect()->route('lembaga.dashboard');
                case 'consumer':
                default:
                    return redirect()->route('consumer.dashboard');
            }
        }

        return $next($request);
    }
}
