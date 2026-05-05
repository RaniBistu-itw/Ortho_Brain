<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Practice;
use App\Support\ActivePractice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PracticeMembershipController extends Controller
{
    /**
     * Check if a practice with the same name+city already exists (case-insensitive).
     * If found, throw a validation exception with appropriate message.
     */
    private function checkPracticeDuplicate(string $name, int $cityId): void
    {
        $duplicate = Practice::whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->where('city_id', $cityId)
            ->whereNull('deleted_at')
            ->first();

        if ($duplicate) {
            if ($duplicate->status === 'ACTIVE') {
                throw ValidationException::withMessages([
                    'practice_name' => 'A practice with this name already exists in this city. Please search for it using the search bar and select it.',
                ]);
            } else {
                throw ValidationException::withMessages([
                    'practice_name' => 'A practice with this name is already pending approval in this city. Please wait for it to be approved, then join it.',
                ]);
            }
        }
    }

    /**
     * Doctor switches the active practice for this session.
     * Only practices they have an APPROVED link to are valid targets.
     */
    public function switch(Request $request)
    {
        $data = $request->validate([
            'practice_id' => 'required|integer|exists:practices,id',
        ]);

        if (! ActivePractice::set((int) $data['practice_id'])) {
            return back()->with('error', 'You do not have access to that practice.');
        }

        return back()->with('success', 'Switched practice.');
    }

    /**
     * Landing page for a doctor who has no usable active-practice context.
     * Covers three cases:
     *   1. Brand-new doctor with PENDING / REJECTED requests only.
     *   2. Doctor whose APPROVED practices are all paused (practices.status = INACTIVE).
     *   3. Mix of the above.
     */
    public function pending()
    {
        $doctor = Auth::user()?->doctor;
        abort_unless($doctor, 403);

        // APPROVED pivots whose practice is currently INACTIVE — the "on hold" set.
        $onHold = $doctor->practices()
            ->where('practices.status', 'INACTIVE')
            ->wherePivot('approval_status', 'APPROVED')
            ->get();

        // Other APPROVED links at ACTIVE practices the doctor could switch to.
        $switchable = $doctor->activePractices()->get();

        return view('doctor.practices.pending', [
            'pending'    => $doctor->pendingPractices()->get(),
            'rejected'   => $doctor->rejectedPractices()->get(),
            'onHold'     => $onHold,
            'switchable' => $switchable,
        ]);
    }

    /**
     * Doctor requests to join one or more practices post-registration.
     * Form posts `practices[i][mode]=existing|new` with per-row fields — mirrors
     * the registration block so the UX is the same.
     */
    public function request(Request $request)
    {
        $doctor = Auth::user()?->doctor;
        abort_unless($doctor, 403);

        // Drop rows the user visibly never filled in (existing with no practice_id,
        // new with no name). Prevents required_if firing on untouched rows.
        $rows = collect((array) $request->input('practices', []))
            ->filter(function ($row) {
                if (! is_array($row)) return false;
                $mode = $row['mode'] ?? 'existing';
                if ($mode === 'existing') {
                    return ! empty($row['practice_id']);
                }
                return isset($row['name']) && trim((string) $row['name']) !== '';
            })
            ->values()
            ->all();

        if (empty($rows)) {
            return back()->with('error', 'Fill in at least one practice to submit.');
        }

        $request->merge(['practices' => $rows]);

        $phoneCodes = Country::where('status', 'ACTIVE')
            ->select('phone_code')->distinct()->orderBy('phone_code')->pluck('phone_code')->all();

        $request->validate([
            'practices'                          => 'required|array|max:3',
            'practices.*.mode'                   => 'required|in:existing,new',
            'practices.*.practice_id'            => 'required_if:practices.*.mode,existing|nullable|integer|exists:practices,id',
            'practices.*.name'                   => 'required_if:practices.*.mode,new|nullable|string|max:200',
            'practices.*.website'                => 'required_if:practices.*.mode,new|nullable|string|max:500',
            'practices.*.phone_country_code'     => ['nullable', 'required_if:practices.*.mode,new', Rule::in($phoneCodes)],
            'practices.*.phone_number'           => 'required_if:practices.*.mode,new|nullable|string|regex:/^\d{10}$/',
            'practices.*.street_address_1'       => 'required_if:practices.*.mode,new|nullable|string|min:5|max:255',
            'practices.*.street_address_2'       => 'nullable|string|max:255',
            'practices.*.zip_id'                 => 'required_if:practices.*.mode,new|nullable|integer|exists:zipcodes,id',
            'practices.*.city_id'                => 'required_if:practices.*.mode,new|nullable|integer|exists:cities,id',
            'practices.*.state_id'               => 'required_if:practices.*.mode,new|nullable|integer|exists:states,id',
            'practices.*.country_id'             => 'required_if:practices.*.mode,new|nullable|integer|exists:countries,id',
        ], [
            'practices.*.phone_number.regex' => 'Phone must be exactly 10 digits.',
        ]);

        $created = 0;
        $duplicates = [];

        DB::transaction(function () use ($rows, $doctor, &$created, &$duplicates) {
            foreach ($rows as $row) {
                if (($row['mode'] ?? 'existing') === 'existing') {
                    $pid = (int) $row['practice_id'];

                    $already = DB::table('doctor_practice')
                        ->where('doctor_id', $doctor->id)
                        ->where('practice_id', $pid)
                        ->whereIn('approval_status', ['PENDING', 'APPROVED'])
                        ->exists();
                    if ($already) {
                        $duplicates[] = Practice::where('id', $pid)->value('name') ?? "#$pid";
                        continue;
                    }

                    $doctor->practices()->attach($pid, [
                        'approval_status' => 'PENDING',
                        'is_primary'      => false,
                        'requested_at'    => now(),
                    ]);
                    $created++;
                } else {
                    $this->checkPracticeDuplicate($row['name'], $row['city_id']);

                    $practice = Practice::create([
                        'owner_id'           => $doctor->id,
                        'name'               => $row['name'],
                        'website'            => $row['website'],
                        'phone_country_code' => $row['phone_country_code'],
                        'phone_number'       => $row['phone_number'],
                        'street_address_1'   => $row['street_address_1'],
                        'street_address_2'   => $row['street_address_2'] ?? null,
                        'zip_id'             => $row['zip_id'],
                        'city_id'            => $row['city_id'],
                        'state_id'           => $row['state_id'],
                        'country_id'         => $row['country_id'],
                        'status'             => 'INACTIVE',
                    ]);
                    $doctor->practices()->attach($practice->id, [
                        'approval_status' => 'PENDING',
                        'is_primary'      => false,
                        'requested_at'    => now(),
                    ]);
                    $created++;
                }
            }
        });

        $msg = $created === 1
            ? '1 practice request submitted — it will be reviewed shortly.'
            : "{$created} practice requests submitted — each will be reviewed separately.";
        if (! empty($duplicates)) {
            $msg .= ' Skipped (already linked): ' . implode(', ', $duplicates) . '.';
        }
        return back()->with('success', $msg);
    }

    /**
     * Doctor cancels a pending request of their own — only meaningful while
     * the practice is INACTIVE (admin hasn't activated it yet).
     */
    public function cancel(int $link)
    {
        $doctor = Auth::user()?->doctor;
        abort_unless($doctor, 403);

        $row = DB::table('doctor_practice')->where('id', $link)->first();
        abort_unless($row && (int) $row->doctor_id === $doctor->id, 404);

        $practiceStatus = DB::table('practices')->where('id', $row->practice_id)->value('status');
        if ($practiceStatus !== 'INACTIVE') {
            return back()->with('error', 'You can only cancel a link to a practice that is still pending admin approval.');
        }

        DB::table('doctor_practice')->where('id', $link)->update([
            'approval_status' => 'CANCELLED',
            'updated_at'      => now(),
        ]);

        return back()->with('success', 'Request cancelled.');
    }

    /**
     * Doctor leaves a practice they were active at — only meaningful while
     * the practice is ACTIVE (otherwise there's nothing to "leave").
     * Cannot leave their only ACTIVE practice (would orphan their session).
     */
    public function leave(int $link)
    {
        $doctor = Auth::user()?->doctor;
        abort_unless($doctor, 403);

        $row = DB::table('doctor_practice')->where('id', $link)->first();
        abort_unless($row && (int) $row->doctor_id === $doctor->id, 404);

        $practiceStatus = DB::table('practices')->where('id', $row->practice_id)->value('status');
        if ($practiceStatus !== 'ACTIVE') {
            return back()->with('error', 'You can only leave a practice that is currently active.');
        }

        $activeCount = $doctor->activePractices()->count();
        if ($activeCount <= 1) {
            return back()->with('error', 'You cannot leave your only active practice.');
        }

        DB::table('doctor_practice')->where('id', $link)->update([
            'approval_status' => 'LEFT',
            'left_at'         => now(),
            'is_primary'      => false,
            'updated_at'      => now(),
        ]);

        if (session(ActivePractice::SESSION_KEY) == $row->practice_id) {
            ActivePractice::clear();
        }

        return back()->with('success', 'You have left this practice.');
    }

    /**
     * Doctor marks a different active practice as their primary.
     */
    public function makePrimary(int $link)
    {
        $doctor = Auth::user()?->doctor;
        abort_unless($doctor, 403);

        $row = DB::table('doctor_practice')->where('id', $link)->first();
        abort_unless($row && (int) $row->doctor_id === $doctor->id, 404);

        $practiceStatus = DB::table('practices')->where('id', $row->practice_id)->value('status');
        if ($practiceStatus !== 'ACTIVE') {
            return back()->with('error', 'Only active practices can be set as primary.');
        }

        DB::transaction(function () use ($doctor, $link) {
            DB::table('doctor_practice')
                ->where('doctor_id', $doctor->id)
                ->update(['is_primary' => false, 'updated_at' => now()]);

            DB::table('doctor_practice')
                ->where('id', $link)
                ->update(['is_primary' => true, 'updated_at' => now()]);
        });

        return back()->with('success', 'Primary practice updated.');
    }
}
