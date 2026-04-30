<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BuccalCorridorOption;
use App\Models\Country;
use App\Models\Doctor;
use App\Models\Modality;
use App\Models\Practice;
use App\Models\Specialty;
use App\Models\TreatmentModality;
use App\Models\User;
use App\Models\Zipcode;
use App\Rules\NotDisposableEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public function show()
    {
        return view('register', [
            'zipcodes'                => $this->activeZipcodes(),
            'modalitiesList'          => Modality::orderBy('id')->get(),
            'specialtiesList'         => Specialty::orderBy('id')->get(),
            'treatmentModalitiesList' => TreatmentModality::orderBy('id')->get(),
            'buccalCorridorsList'     => BuccalCorridorOption::orderBy('id')->get(),
            'phoneCodes'              => $this->activePhoneCodes(),
        ]);
    }

    public function store(Request $request)
    {
        // Drop "Other Practice" rows that are visibly empty BEFORE validation:
        //  - existing-mode: no practice_id picked
        //  - new-mode: no name typed
        // Otherwise required_if rules would fire on rows the user clearly never filled in.
        if ($request->has('additional_practices')) {
            $rows = collect((array) $request->input('additional_practices'))
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
            $request->merge(['additional_practices' => $rows]);
        }

        // Branch: existing practice picked from autocomplete (practice_id present)
        //         vs new practice (all practice + address fields expected)
        $isExisting = $request->filled('practice_id');

        // Allowed phone codes — driven by countries.phone_code (distinct).
        $phoneCodes = $this->activePhoneCodes();

        // Resurrection: allow re-registration if this email belongs to either
        //  (a) a previously-rejected doctor (existing behaviour), or
        //  (b) an unverified-stale user whose previous registration was abandoned
        //      mid-OTP (their Doctor row is currently soft-deleted; admin never
        //      saw them, so it's safe to wipe and start over).
        $resurrecting = null;
        if ($request->filled('email')) {
            $resurrecting = User::where('email', $request->email)
                ->where(function ($q) {
                    $q->whereHas('doctor', fn ($d) => $d->where('approval_status', 'REJECTED'))
                      ->orWhereNull('email_verified_at');
                })
                ->first();
        }

        $rules = [
            'email'                 => $resurrecting
                ? ['required', 'email', 'max:150', new NotDisposableEmail]
                : ['required', 'email', 'max:150', 'unique:users,email', new NotDisposableEmail],
            'first_name'            => 'required|string|max:100|regex:/^[A-Za-z\s\-]+$/',
            'last_name'             => 'required|string|max:100|regex:/^[A-Za-z\s\-]+$/',
            'password'              => 'required|string|min:8|confirmed:confirm_password|regex:/[A-Z]/|regex:/[a-z]/|regex:/\d/|regex:/[!@#$%^&*()\-_+={}\[\]:;<>,.?~\\\\\/]/',
            'confirm_password'      => 'required|string',

            'providing_ortho'       => 'nullable|in:yes,no',
            'contact_preference'    => 'nullable|in:doctor,employee,both',
            'terms_agreed'          => 'accepted',

            'modalities'            => 'nullable|array',
            'modalities.*'          => 'integer|exists:modalities,id',
            'specialties'           => 'nullable|array',
            'specialties.*'         => 'integer|exists:specialties,id',
            'treatment_modalities'  => 'nullable|array',
            'treatment_modalities.*' => 'integer|exists:treatment_modalities,id',
            'buccal_corridors'      => 'nullable|array',
            'buccal_corridors.*'    => 'integer|exists:buccal_corridor_options,id',

            // Contact information — all optional, but validated if filled
            'contact_doctor_email'            => 'nullable|email',
            'contact_doctor_phone'            => 'nullable|digits:10',
            'contact_doctor_other_emails'     => 'nullable|array',
            'contact_doctor_other_emails.*'   => 'nullable|email',
            'contact_emp_name'                => 'nullable|string|max:100',
            'contact_emp_title'               => 'nullable|string|max:100',
            'contact_emp_email'               => 'nullable|email',
            'contact_emp_phone'               => 'nullable|digits:10',
            'contact_emp_other_emails'        => 'nullable|array',
            'contact_emp_other_emails.*'      => 'nullable|email',

            // Doctor's primary practice address — Step 3 always submitted, regardless of whether
            // the primary practice is existing or new. The address values themselves come from
            // either a chosen practice (auto-filled by JS) or manual entry ("Other" mode).
            'street_address_1'              => 'required|string|min:5|max:255',
            'street_address_2'              => 'nullable|string|max:255',
            'zip_id'                        => 'required|integer|exists:zipcodes,id',
            'city_id'                       => 'required|integer|exists:cities,id',
            'state_id'                      => 'required|integer|exists:states,id',
            'country_id'                    => 'required|integer|exists:countries,id',

            // Step 3 dropdown — what populated the address fields above.
            //   PRACTICE = address came from one of the doctor's entered practices (ref points to which one)
            //   OTHER    = doctor entered address manually
            'primary_address_source'        => 'required|in:PRACTICE,OTHER',
            'primary_address_practice_ref'  => [
                'required_if:primary_address_source,PRACTICE',
                'nullable',
                'string',
                'max:30',
                'regex:/^(primary|extra-\d+)$/',
            ],

            // Other practices the doctor also works at — each row has a mode, then conditional fields.
            'additional_practices'                          => 'nullable|array|max:5',
            'additional_practices.*.mode'                   => 'required_with:additional_practices|in:existing,new',
            'additional_practices.*.practice_id'            => 'required_if:additional_practices.*.mode,existing|nullable|integer|exists:practices,id',
            'additional_practices.*.name'                   => 'required_if:additional_practices.*.mode,new|nullable|string|max:200',
            'additional_practices.*.website'                => 'required_if:additional_practices.*.mode,new|nullable|string|max:500',
            'additional_practices.*.phone_country_code'     => ['nullable', 'required_if:additional_practices.*.mode,new', Rule::in($phoneCodes)],
            'additional_practices.*.phone_number'           => 'required_if:additional_practices.*.mode,new|nullable|string|regex:/^\d{10}$/',
            'additional_practices.*.street_address_1'       => 'required_if:additional_practices.*.mode,new|nullable|string|min:5|max:255',
            'additional_practices.*.street_address_2'       => 'nullable|string|max:255',
            'additional_practices.*.zip_id'                 => 'required_if:additional_practices.*.mode,new|nullable|integer|exists:zipcodes,id',
            'additional_practices.*.city_id'                => 'required_if:additional_practices.*.mode,new|nullable|integer|exists:cities,id',
            'additional_practices.*.state_id'               => 'required_if:additional_practices.*.mode,new|nullable|integer|exists:states,id',
            'additional_practices.*.country_id'             => 'required_if:additional_practices.*.mode,new|nullable|integer|exists:countries,id',
        ];

        if ($isExisting) {
            $rules['practice_id'] = 'required|integer|exists:practices,id';
        } else {
            $rules = array_merge($rules, [
                'practice_name'               => 'required|string|max:200',
                'practice_phone_country_code' => ['required', Rule::in($phoneCodes)],
                'practice_phone_number'       => 'required|string|regex:/^\d{10}$/',
                'practice_website'            => 'required|string|max:500',
            ]);
        }

        // Friendly messages for the array-pathed additional_practices.* rules.
        // Laravel exposes :attribute as "additional_practices.0.phone_number" by default
        // which is unreadable; we override per-path to say "Other Practice #1".
        $extraMessages = [];
        foreach (($request->input('additional_practices') ?? []) as $i => $_row) {
            $label = 'Other Practice #' . ($i + 1);
            $extraMessages["additional_practices.$i.mode.required_with"]      = "$label: pick existing or create new.";
            $extraMessages["additional_practices.$i.practice_id.required_if"] = "$label: please pick a practice from the search.";
            $extraMessages["additional_practices.$i.name.required_if"]        = "$label: practice name is required.";
            $extraMessages["additional_practices.$i.website.required_if"]     = "$label: website is required.";
            $extraMessages["additional_practices.$i.phone_country_code.required_if"] = "$label: phone country code is required.";
            $extraMessages["additional_practices.$i.phone_number.required_if"]       = "$label: phone number is required.";
            $extraMessages["additional_practices.$i.phone_number.regex"]             = "$label: phone must be exactly 10 digits.";
            $extraMessages["additional_practices.$i.street_address_1.required_if"]   = "$label: street address is required.";
            $extraMessages["additional_practices.$i.zip_id.required_if"]             = "$label: please pick a zip code.";
            $extraMessages["additional_practices.$i.city_id.required_if"]            = "$label: city is required (select a zip code).";
            $extraMessages["additional_practices.$i.state_id.required_if"]           = "$label: state is required (select a zip code).";
            $extraMessages["additional_practices.$i.country_id.required_if"]         = "$label: country is required (select a zip code).";
        }

        $data = $request->validate($rules, array_merge([
            'password.regex'              => 'Password must include uppercase, lowercase, number & special character.',
            'practice_phone_number.regex' => 'Phone must be exactly 10 digits.',
            'terms_agreed.accepted'       => 'You must accept the Terms and Conditions to continue.',
        ], $extraMessages));

        $contactMap = [
            'doctor'   => 'DOCTOR_ONLY',
            'employee' => 'EMPLOYEE_OFFICE',
            'both'     => 'DOCTOR_AND_EMPLOYEE_OFFICE',
        ];

        $createdUser   = null;
        $createdDoctor = null;

        // Split additional rows into "pick existing" vs "create new" lists.
        // De-dupe existing IDs and exclude the primary practice if also picked.
        // Each entry tracks its original form index so we can resolve the
        // Step 3 dropdown's `primary_address_practice_ref` (e.g. "extra-2") to
        // a real practice_id after creation.
        $primaryExistingId = $isExisting ? (int) ($data['practice_id'] ?? 0) : 0;
        $existingIds = [];
        $newRows     = [];
        $seenExisting = $primaryExistingId ? [$primaryExistingId] : [];

        foreach (($data['additional_practices'] ?? []) as $i => $row) {
            $mode = $row['mode'] ?? 'existing';
            if ($mode === 'existing') {
                $pid = (int) ($row['practice_id'] ?? 0);
                if ($pid && ! in_array($pid, $seenExisting, true)) {
                    $existingIds[]  = ['form_idx' => $i, 'id' => $pid];
                    $seenExisting[] = $pid;
                }
            } else {
                $newRows[] = ['form_idx' => $i, 'data' => $row];
            }
        }

        DB::transaction(function () use ($data, $contactMap, $isExisting, $resurrecting, $existingIds, $newRows, &$createdUser, &$createdDoctor) {
            if ($resurrecting) {
                // Unverified-stale path: the previous registration left a
                // soft-deleted Doctor row + its pivot rows behind. Hard-delete
                // them so the Doctor::create() below can run cleanly. (For the
                // REJECTED-resurrection path the Doctor is not trashed and the
                // existing fill-in-place logic still kicks in.)
                $orphan = $resurrecting->doctor()->withTrashed()->first();
                if ($orphan && $orphan->trashed()) {
                    $orphan->practices()->detach();
                    $orphan->forceDelete();
                    $resurrecting->unsetRelation('doctor');
                }

                $user = $resurrecting;
                $user->password_hash     = Hash::make($data['password']);
                $user->is_active         = true;
                $user->email_verified_at = null;
                $user->save();
            } else {
                $user = User::create([
                    'email'         => $data['email'],
                    'password_hash' => Hash::make($data['password']),
                    'role'          => 'DOCTOR',
                    'is_active'     => true,
                ]);
            }
            $createdUser = $user;

            // Resolve practice: either pick existing or create new (owner stamped after doctor exists)
            $newPractice = null;
            if ($isExisting) {
                $practiceId = (int) $data['practice_id'];
            } else {
                $newPractice = Practice::create([
                    'owner_id'           => null,
                    'name'               => $data['practice_name'],
                    'website'            => $data['practice_website'],
                    'phone_country_code' => $data['practice_phone_country_code'],
                    'phone_number'       => $data['practice_phone_number'],
                    'street_address_1'   => $data['street_address_1'],
                    'street_address_2'   => $data['street_address_2'] ?? null,
                    'zip_id'             => $data['zip_id'],
                    'city_id'            => $data['city_id'],
                    'state_id'           => $data['state_id'],
                    'country_id'         => $data['country_id'],
                    // Doctor-submitted practices land as INACTIVE — admin must activate
                    // them in /admin/practices before any doctor can use them.
                    'status'             => 'INACTIVE',
                ]);
                $practiceId = $newPractice->id;
            }

            $doctorData = [
                'user_id'                            => $user->id,
                'practice_id'                        => $practiceId,
                'first_name'                         => $data['first_name'],
                'last_name'                          => $data['last_name'],
                'preferred_language'                 => 'English',
                'currently_providing_ortho_services' => ($data['providing_ortho'] ?? 'no') === 'yes',
                'preferred_contact_mode'             => $contactMap[$data['contact_preference'] ?? 'doctor'] ?? 'DOCTOR_ONLY',
                'doctor_contact_email'               => $data['email'],
                'preferred_tooth_numbering_system'   => 'UNIVERSAL',
                'smile_arc_pref'                     => 'DEFER',
                'small_lateral_incisors_pref'        => 'DEFER',
                'mixed_dentition_pref'               => 'DEFER',
                'orthodontic_extractions_pref'       => 'DEFER',
                'ipr_protocol_pref'                  => 'DEFER',
                'elastics_bonded_buttons_pref'       => 'NO',
                'extractions_if_suggested_pref'      => 'NO',
                'attachment_stage_pref'              => 'AT_STEP_1',
                'approval_status'                    => 'PENDING',
                'approved_at'                        => null,
                'approved_by_admin_id'               => null,
                'rejection_reason'                   => null,
                // Step 3 primary practice address — copied from whichever source the doctor
                // chose in the dropdown (an existing/new practice, or "Other"). Stored as a
                // snapshot so a single doctor read returns the address without joining.
                'primary_address_source'             => $data['primary_address_source'],
                // primary_address_practice_id resolved below once all practices exist.
                'street_address_1'                   => $data['street_address_1'],
                'street_address_2'                   => $data['street_address_2'] ?? null,
                'zip_id'                             => $data['zip_id'],
                'city_id'                            => $data['city_id'],
                'state_id'                           => $data['state_id'],
                'country_id'                         => $data['country_id'],
            ];

            if ($resurrecting && $resurrecting->doctor) {
                $doctor = $resurrecting->doctor;
                $doctor->fill($doctorData)->save();
            } else {
                $doctor = Doctor::create($doctorData);
            }
            $createdDoctor = $doctor;

            // Stamp ownership on the just-created primary practice (if it was new).
            if ($newPractice) {
                $newPractice->update(['owner_id' => $doctor->id]);
            }

            // ─── Practice links (pivot rows) ───────────────────────────────
            // Wipe any prior pivot rows when resurrecting a rejected doctor.
            $doctor->practices()->detach();

            // Primary practice link — pending until admin approves it explicitly.
            $doctor->practices()->attach($practiceId, [
                'approval_status' => 'PENDING',
                'is_primary'      => true,
                'requested_at'    => now(),
            ]);

            // Build the form-index → practice_id resolution map so we can later
            // translate the Step 3 dropdown's `primary_address_practice_ref`
            // (e.g. "primary" or "extra-2") into a real practices.id.
            $refMap = ['primary' => $practiceId];

            // Additional existing-practice claims — all pending.
            foreach ($existingIds as $entry) {
                $doctor->practices()->attach($entry['id'], [
                    'approval_status' => 'PENDING',
                    'is_primary'      => false,
                    'requested_at'    => now(),
                ]);
                $refMap['extra-' . $entry['form_idx']] = $entry['id'];
            }

            // Additional new-practice creations — create Practice rows owned by
            // this doctor, then attach as PENDING. Admin still has to approve
            // each one (per product decision: every practice needs admin review).
            foreach ($newRows as $entry) {
                $row = $entry['data'];
                $created = Practice::create([
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

                $doctor->practices()->attach($created->id, [
                    'approval_status' => 'PENDING',
                    'is_primary'      => false,
                    'requested_at'    => now(),
                ]);
                $refMap['extra-' . $entry['form_idx']] = $created->id;
            }

            // Resolve the Step 3 dropdown ref → real practice_id (PRACTICE source only).
            if (($data['primary_address_source'] ?? null) === 'PRACTICE') {
                $ref = $data['primary_address_practice_ref'] ?? null;
                $doctor->update([
                    'primary_address_practice_id' => $refMap[$ref] ?? null,
                ]);
            }

            // Per product decision: do NOT create a shipping/billing DoctorAddress here.
            // Doctor adds shipping/billing later in the profile.

            $doctor->modalities()->sync($data['modalities'] ?? []);
            $doctor->specialties()->sync($data['specialties'] ?? []);
            $doctor->treatmentModalities()->sync($data['treatment_modalities'] ?? []);
            $doctor->buccalCorridorOptions()->sync($data['buccal_corridors'] ?? []);
        });

        $isAdminCreating = auth()->check() && auth()->user()->role === 'ADMIN';

        if (! $isAdminCreating && $createdUser && $createdDoctor) {
            // Public registration: hide the Doctor row from admin queries until
            // the OTP is verified. Soft-delete is automatically respected by
            // every Doctor::query() in the admin area (and elsewhere) thanks
            // to the SoftDeletes trait on the Doctor model. The matching
            // ->restore() lives in EmailVerificationController::markVerified.
            $createdDoctor->delete();

            EmailVerificationController::sendVerificationEmail($createdUser);

            return redirect()
                ->route('verify-email.show', ['email' => $createdUser->email])
                ->with('success', 'Registration submitted. Check your email for a 6-digit verification code.');
        }

        // Admin-created doctor: skip the OTP step entirely. Mark the user as
        // verified now so Gate 3 in LoginController doesn't block them later.
        if ($isAdminCreating && $createdUser && is_null($createdUser->email_verified_at)) {
            $createdUser->forceFill(['email_verified_at' => now()])->save();
        }

        return redirect('/login')->with('success', 'Registration submitted. Your account is pending admin approval — you\'ll receive an email once approved.');
    }

    private function activeZipcodes()
    {
        return Zipcode::with('city.state.country')
            ->where('status', 'ACTIVE')
            ->whereHas('city', fn ($q) => $q->where('status', 'ACTIVE'))
            ->orderBy('code')
            ->get();
    }

    private function activePhoneCodes(): array
    {
        return Country::where('status', 'ACTIVE')
            ->select('phone_code')
            ->distinct()
            ->orderBy('phone_code')
            ->pluck('phone_code')
            ->all();
    }
}
