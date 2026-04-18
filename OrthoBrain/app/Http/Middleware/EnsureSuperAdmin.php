<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards /admin/* routes. Requires authenticated admin with role=ADMIN.
 * Note: friend's users.role enum is ADMIN/DOCTOR (uppercase) — we match.
 */
class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user->role !== 'ADMIN' || ! $user->is_active) {
            Auth::logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Your account does not have admin access.']);
        }

        return $next($request);
    }
}
