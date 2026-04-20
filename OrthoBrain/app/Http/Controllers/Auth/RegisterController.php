<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BuccalCorridorOption;
use App\Models\Doctor;
use App\Models\Modality;
use App\Models\Specialty;
use App\Models\TreatmentModality;
use App\Models\User;
use App\Models\Zipcode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    public function show()
    {
        return view('register', [
            'zipcodes'             => $this->activeZipcodes(),
            'modalitiesList'       => Modality::orderBy('id')->get(),
            'specialtiesList'      => Specialty::orderBy('id')->get(),
            'treatmentModalitiesList' => TreatmentModality::orderBy('id')->get(),
            'buccalCorridorsList'  => BuccalCorridorOption::orderBy('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email'                       => 'required|email|max:150|unique:users,email',
            'first_name'                  => 'required|string|max:100|regex:/^[A-Za-z\s\-]+$/',
            'last_name'                   => 'required|string|max:100|regex:/^[A-Za-z\s\-]+$/',
            'password'                    => 'required|string|min:8|confirmed:confirm_password|regex:/[A-Z]/|regex:/[a-z]/|regex:/\d/|regex:/[!@#$%^&*()\-_+={}\[\]:;<>,.?~\\\\\/]/',
            'confirm_password'            => 'required|string',

            'practice_name'               => 'required|string|max:200',
            'practice_phone_country_code' => ['required', Rule::in(['+1_US', '+1_CA', '+61_AU'])],
            'practice_phone_number'       => 'required|string|regex:/^\d{10}$/',
            'practice_website'            => 'required|string|max:500',
            'preferred_language'          => 'required|string|max:50',

            'street_address_1'            => 'required|string|min:5|max:255',
            'street_address_2'            => 'nullable|string|max:255',
            'zip_id'                      => 'required|integer|exists:zipcodes,id',
            'city_id'                     => 'required|integer|exists:cities,id',
            'state_id'                    => 'required|integer|exists:states,id',
            'country_id'                  => 'required|integer|exists:countries,id',

            'providing_ortho'             => 'nullable|in:yes,no',
            'contact_preference'          => 'nullable|in:doctor,employee,both',
            'terms_agreed'                => 'accepted',

            'modalities'             => 'nullable|array',
            'modalities.*'           => 'integer|exists:modalities,id',
            'specialties'            => 'nullable|array',
            'specialties.*'          => 'integer|exists:specialties,id',
            'treatment_modalities'   => 'nullable|array',
            'treatment_modalities.*' => 'integer|exists:treatment_modalities,id',
            'buccal_corridors'       => 'nullable|array',
            'buccal_corridors.*'     => 'integer|exists:buccal_corridor_options,id',
        ], [
            'password.regex'              => 'Password must include uppercase, lowercase, number & special character.',
            'practice_phone_number.regex' => 'Phone must be exactly 10 digits.',
            'terms_agreed.accepted'       => 'You must accept the Terms and Conditions to continue.',
        ]);

        $contactMap = [
            'doctor'   => 'DOCTOR_ONLY',
            'employee' => 'EMPLOYEE_OFFICE',
            'both'     => 'DOCTOR_AND_EMPLOYEE_OFFICE',
        ];

        DB::transaction(function () use ($data, $contactMap) {
            $user = User::create([
                'email'         => $data['email'],
                'password_hash' => Hash::make($data['password']),
                'role'          => 'DOCTOR',
                'is_active'     => true,
            ]);

            $doctor = Doctor::create([
                'user_id'                          => $user->id,
                'first_name'                       => $data['first_name'],
                'last_name'                        => $data['last_name'],
                'practice_name'                    => $data['practice_name'],
                'practice_phone_country_code'      => $data['practice_phone_country_code'],
                'practice_phone_number'            => $data['practice_phone_number'],
                'practice_website'                 => $data['practice_website'],
                'preferred_language'               => $data['preferred_language'],
                'currently_providing_ortho_services' => ($data['providing_ortho'] ?? 'no') === 'yes',
                'preferred_contact_mode'           => $contactMap[$data['contact_preference'] ?? 'doctor'] ?? 'DOCTOR_ONLY',
                'doctor_contact_email'             => $data['email'],
                'preferred_tooth_numbering_system' => 'UNIVERSAL',
                'smile_arc_pref'                   => 'DEFER',
                'small_lateral_incisors_pref'      => 'DEFER',
                'mixed_dentition_pref'             => 'DEFER',
                'orthodontic_extractions_pref'     => 'DEFER',
                'ipr_protocol_pref'                => 'DEFER',
                'elastics_bonded_buttons_pref'     => 'NO',
                'extractions_if_suggested_pref'    => 'NO',
                'attachment_stage_pref'            => 'AT_STEP_1',
                'approval_status'                  => 'PENDING',
            ]);

            $doctor->addresses()->create([
                'type'             => 'shipping',
                'street_address_1' => $data['street_address_1'],
                'street_address_2' => $data['street_address_2'] ?? null,
                'zip_id'           => $data['zip_id'],
                'city_id'          => $data['city_id'],
                'state_id'         => $data['state_id'],
                'country_id'       => $data['country_id'],
                'is_default'       => true,
            ]);

            $doctor->modalities()->sync($data['modalities'] ?? []);
            $doctor->specialties()->sync($data['specialties'] ?? []);
            $doctor->treatmentModalities()->sync($data['treatment_modalities'] ?? []);
            $doctor->buccalCorridorOptions()->sync($data['buccal_corridors'] ?? []);
        });

        return redirect('/login')->with('success', 'Registered successfully! Please log in with your credentials.');
    }

    private function activeZipcodes()
    {
        return Zipcode::with('city.state.country')
            ->where('status', 'ACTIVE')
            ->whereHas('city', fn ($q) => $q->where('status', 'ACTIVE'))
            ->orderBy('code')
            ->get();
    }
}
