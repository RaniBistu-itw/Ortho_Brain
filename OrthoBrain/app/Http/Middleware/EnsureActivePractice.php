<?php

namespace App\Http\Middleware;

use App\Support\ActivePractice;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensures the logged-in doctor has an active practice context.
 * If they have no APPROVED links yet, send them to the "practices pending"
 * page so they don't land on a dashboard with nothing to show.
 */
class EnsureActivePractice
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || $user->role !== 'DOCTOR') {
            return $next($request);
        }

        if (ActivePractice::get()) {
            return $next($request);
        }

        $allowed = [
            'doctor.profile.index',
            'doctor.profile.update',
            'doctor.practices.pending',
            'logout',
        ];

        if (in_array($request->route()?->getName(), $allowed, true)) {
            return $next($request);
        }

        return redirect()->route('doctor.practices.pending');
    }
}
