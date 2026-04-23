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
            // activePractices() already filters to practices.status = ACTIVE,
            // so a session pointer at a now-deactivated practice is dropped.
            $link = $doctor->activePractices()->where('practices.id', $id)->first();
            if ($link) {
                return $link;
            }
            Session::forget(self::SESSION_KEY);
        }

        $primary = $doctor->primaryPractice()->first();
        if ($primary) {
            Session::put(self::SESSION_KEY, $primary->id);
            return $primary;
        }

        $any = $doctor->activePractices()->first();
        if ($any) {
            Session::put(self::SESSION_KEY, $any->id);
            return $any;
        }

        return null;
    }

    public static function set(int $practiceId): bool
    {
        $doctor = Auth::user()?->doctor;
        if (! $doctor) {
            return false;
        }

        // Only switch to a practice that is both linked AND globally ACTIVE.
        $allowed = $doctor->activePractices()
            ->where('practices.id', $practiceId)
            ->exists();

        if (! $allowed) {
            return false;
        }

        Session::put(self::SESSION_KEY, $practiceId);
        return true;
    }

    public static function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }
}
