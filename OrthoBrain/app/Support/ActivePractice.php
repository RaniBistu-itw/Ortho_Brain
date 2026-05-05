<?php

namespace App\Support;

use App\Models\Practice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Resolves the doctor's "active practice" for the current request.
 *
 * Source of truth: session('active_practice_id'). Falls back to the
 * doctor's primary approved link, then to any approved link.
 */
class ActivePractice
{
    public const SESSION_KEY = 'active_practice_id';

    public static function get(): ?Practice
    {
        $doctor = Auth::user()?->doctor;
        if (! $doctor) {
            return null;
        }

        $id = Session::get(self::SESSION_KEY);

        if ($id) {
            // Honour the doctor's explicit choice — including a paused (INACTIVE)
            // practice. The middleware/UI checks practice->status to decide
            // whether to allow case routes or surface the "paused" notice.
            $link = $doctor->practices()
                ->wherePivot('approval_status', 'APPROVED')
                ->where('practices.id', $id)
                ->first();
            if ($link) {
                return $link;
            }
            // Session pointer is stale (link revoked / rejected) — drop it.
            Session::forget(self::SESSION_KEY);
        }

        // No explicit session yet: pick from THE DOCTOR'S OWN approved practices.
        // Critically, we do not filter by practice.status here — if the doctor's
        // primary or only practice has been paused by the admin, we still land
        // them on it (so they see the right name everywhere), and the middleware
        // surfaces the "paused" notice. We never silently swap them onto an
        // unrelated practice.
        //
        // Preference order:
        //   1. Their primary pivot (active or paused).
        //   2. Any other approved pivot, prioritizing ACTIVE ones over paused.
        $primary = $doctor->practices()
            ->wherePivot('approval_status', 'APPROVED')
            ->wherePivot('is_primary', true)
            ->first();
        if ($primary) {
            Session::put(self::SESSION_KEY, $primary->id);
            return $primary;
        }

        // Tiebreaker: prefer an ACTIVE link if one exists, otherwise fall back
        // to the most-recent approved (paused) link. Either way, it's strictly
        // a practice the doctor is approved at.
        $activeFallback = $doctor->practices()
            ->where('practices.status', 'ACTIVE')
            ->wherePivot('approval_status', 'APPROVED')
            ->first();
        if ($activeFallback) {
            Session::put(self::SESSION_KEY, $activeFallback->id);
            return $activeFallback;
        }

        $pausedFallback = $doctor->practices()
            ->wherePivot('approval_status', 'APPROVED')
            ->orderByDesc('doctor_practice.approved_at')
            ->first();
        if ($pausedFallback) {
            Session::put(self::SESSION_KEY, $pausedFallback->id);
            return $pausedFallback;
        }

        return null;
    }

    public static function set(int $practiceId): bool
    {
        $doctor = Auth::user()?->doctor;
        if (! $doctor) {
            return false;
        }

        // Allow switching to any APPROVED-link practice — including paused ones —
        // so the doctor can revisit a temporarily-inactive practice and see the
        // "paused" notice. Status-aware routing happens in the middleware.
        $allowed = $doctor->practices()
            ->wherePivot('approval_status', 'APPROVED')
            ->where('practices.id', $practiceId)
            ->exists();

        if (! $allowed) {
            return false;
        }

        Session::put(self::SESSION_KEY, $practiceId);
        return true;
    }

    /**
     * Convenience: the active practice exists but is currently paused (INACTIVE).
     * Routes that require a usable practice context should treat this as "no access".
     */
    public static function isPaused(): bool
    {
        $p = self::get();
        return $p !== null && $p->status !== 'ACTIVE';
    }

    public static function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }
}
