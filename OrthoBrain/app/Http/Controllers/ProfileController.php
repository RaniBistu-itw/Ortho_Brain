<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\BuccalCorridorOption;
use App\Models\Doctor;
use App\Models\DoctorAddress;
use App\Models\Modality;
use App\Models\Specialty;
use App\Models\TreatmentModality;
use App\Models\User;
use App\Models\Zipcode;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'account');

        $doctor = Doctor::with('practice')->where('user_id', Auth::id())->first();
        $activePractice = currentPractice();

        $addresses = $doctor
            ? $doctor->addresses()->with('zipcode', 'city', 'state', 'country')->latest()->get()
            : collect();

        $modalitiesList          = Modality::orderBy('id')->get();
        $specialtiesList         = Specialty::orderBy('id')->get();
        $treatmentModalitiesList = TreatmentModality::orderBy('id')->get();
        $buccalCorridorsList     = BuccalCorridorOption::orderBy('id')->get();

        $selectedModalityIds         = $doctor ? $doctor->modalities()->pluck('modalities.id')->all() : [];
        $selectedSpecialtyIds        = $doctor ? $doctor->specialties()->pluck('specialties.id')->all() : [];
        $selectedTreatmentModalityIds= $doctor ? $doctor->treatmentModalities()->pluck('treatment_modalities.id')->all() : [];
        $selectedBuccalCorridorIds   = $doctor ? $doctor->buccalCorridorOptions()->pluck('buccal_corridor_options.id')->all() : [];

        // Zipcodes needed when rendering the "Request Another Practice → new" form.
        $zipcodes = $tab === 'practices'
            ? Zipcode::with('city.state.country')
                ->where('status', 'ACTIVE')
                ->whereHas('city', fn ($q) => $q->where('status', 'ACTIVE'))
                ->orderBy('code')
                ->get()
            : collect();

        return view('profile.index', compact(
            'tab', 'doctor', 'activePractice', 'addresses',
            'modalitiesList', 'specialtiesList', 'treatmentModalitiesList', 'buccalCorridorsList',
            'selectedModalityIds', 'selectedSpecialtyIds', 'selectedTreatmentModalityIds', 'selectedBuccalCorridorIds',
            'zipcodes'
        ));
    }

    public function update(Request $request)
    {
        $tab = $request->query('tab', 'account');

        if ($tab === 'account') {
            $request->validate([
                'first_name' => 'required|string|max:100|regex:/^[A-Za-z\s\-]+$/',
                'last_name'  => 'required|string|max:100|regex:/^[A-Za-z\s\-]+$/',
                'email'      => 'required|email|max:150|unique:users,email,' . Auth::id(),
            ], [
                'first_name.regex' => 'First name may only contain letters, spaces and hyphens.',
                'last_name.regex'  => 'Last name may only contain letters, spaces and hyphens.',
            ]);

            $user = Auth::user();
            $user->email = $request->email;
            $user->save();

            $doctor = Doctor::where('user_id', $user->id)->first();
            if ($doctor) {
                $doctor->first_name = $request->first_name;
                $doctor->last_name  = $request->last_name;
                $doctor->save();
            }
        }

        if ($tab === 'practice') {
            $request->validate([
                'practice_name'         => 'required|string|max:200',
                'practice_phone_number' => 'required|string|max:30',
                'website'               => 'required|string|max:500',
                'language'              => 'nullable|string|max:50',
            ]);

            $doctor   = Doctor::where('user_id', Auth::id())->first();
            $practice = currentPractice();
            if (! $practice) {
                return back()->with('error', 'No active practice to edit. Switch to a practice from the top bar.');
            }

            $practice->name               = $request->practice_name;
            $practice->phone_number       = preg_replace('/\D/', '', $request->practice_phone_number);
            $practice->phone_country_code = $request->input('practice_phone_country_code', $practice->phone_country_code);
            $practice->website            = $request->website;
            $practice->save();

            // preferred_language is a doctor-level attribute, not practice-level
            if ($doctor && $request->filled('language')) {
                $doctor->preferred_language = $request->language;
                $doctor->save();
            }
        }

        return back()->with('success', ucfirst($tab) . ' information updated successfully.');
    }

    public function settings()
    {
        return view('profile.settings');
    }

    public function addressCreate(Request $request)
    {
        $type = $request->query('type', 'shipping');
        if (! in_array($type, ['shipping', 'billing'], true)) {
            $type = 'shipping';
        }
        $doctor = Doctor::where('user_id', Auth::id())->first();

        return view('profile.address-form', [
            'mode'     => 'create',
            'type'     => $type,
            'doctor'   => $doctor,
            'address'  => null,
            'zipcodes' => $this->activeZipcodes(),
        ]);
    }

    public function addressShow(DoctorAddress $address)
    {
        $this->authorizeAddress($address);
        return view('profile.address-form', [
            'mode'     => 'view',
            'type'     => $address->type,
            'doctor'   => $address->doctor,
            'address'  => $address->load('zipcode', 'city', 'state', 'country'),
            'zipcodes' => $this->activeZipcodes(),
        ]);
    }

    public function addressEdit(DoctorAddress $address)
    {
        $this->authorizeAddress($address);
        return view('profile.address-form', [
            'mode'     => 'edit',
            'type'     => $address->type,
            'doctor'   => $address->doctor,
            'address'  => $address->load('zipcode', 'city', 'state', 'country'),
            'zipcodes' => $this->activeZipcodes(),
        ]);
    }

    public function addressUpdate(Request $request, DoctorAddress $address)
    {
        $this->authorizeAddress($address);

        $data = $request->validate([
            'street_address_1' => 'required|string|max:255|min:5',
            'street_address_2' => 'nullable|string|max:255',
            'zip_id'           => 'required|integer|exists:zipcodes,id',
            'city_id'          => 'required|integer|exists:cities,id',
            'state_id'         => 'required|integer|exists:states,id',
            'country_id'       => 'required|integer|exists:countries,id',
            'billing_email'    => ($address->type === 'billing' ? 'required|' : 'nullable|') . 'email|max:150',
        ]);

        $address->update($data);

        return redirect()
            ->route('doctor.profile.index', ['tab' => $address->type])
            ->with('success', ucfirst($address->type) . ' address updated successfully.');
    }

    public function updateAdditional(Request $request)
    {
        $data = $request->validate([
            'modalities'             => 'nullable|array',
            'modalities.*'           => 'integer|exists:modalities,id',
            'specialties'            => 'nullable|array',
            'specialties.*'          => 'integer|exists:specialties,id',
            'treatment_modalities'   => 'nullable|array',
            'treatment_modalities.*' => 'integer|exists:treatment_modalities,id',
            'buccal_corridors'       => 'nullable|array',
            'buccal_corridors.*'     => 'integer|exists:buccal_corridor_options,id',
        ]);

        $doctor = Doctor::where('user_id', Auth::id())->firstOrFail();

        $doctor->modalities()->sync($data['modalities'] ?? []);
        $doctor->specialties()->sync($data['specialties'] ?? []);
        $doctor->treatmentModalities()->sync($data['treatment_modalities'] ?? []);
        $doctor->buccalCorridorOptions()->sync($data['buccal_corridors'] ?? []);

        return redirect()
            ->route('doctor.profile.index', ['tab' => 'additional'])
            ->with('success', 'Additional information updated successfully.');
    }

    private function authorizeAddress(DoctorAddress $address): void
    {
        $doctor = Doctor::where('user_id', Auth::id())->first();
        abort_unless($doctor && $address->doctor_id === $doctor->id, 403);
    }

    private function activeZipcodes()
    {
        return Zipcode::with('city.state.country')
            ->where('status', 'ACTIVE')
            ->whereHas('city', fn ($q) => $q->where('status', 'ACTIVE'))
            ->orderBy('code')
            ->get();
    }

    public function addressZipLookup(Request $request)
    {
        $request->validate(['zip_id' => 'required|integer']);

        $zip = Zipcode::with('city.state.country')->find($request->integer('zip_id'));
        if (! $zip) {
            return response()->json(['ok' => false, 'message' => 'Zip code not found.'], 404);
        }

        return response()->json([
            'ok'         => true,
            'zip_id'     => $zip->id,
            'zip_code'   => $zip->code,
            'city_id'    => $zip->city?->id,
            'city'       => $zip->city?->name,
            'state_id'   => $zip->city?->state?->id,
            'state'      => $zip->city?->state?->name,
            'state_code' => $zip->city?->state?->state_code,
            'country_id' => $zip->city?->state?->country?->id,
            'country'    => $zip->city?->state?->country?->name,
        ]);
    }

    public function addressStore(Request $request)
    {
        $type = $request->input('type');
        $data = $request->validate([
            'type'              => 'required|in:shipping,billing',
            'street_address_1'  => 'required|string|max:255|min:5',
            'street_address_2'  => 'nullable|string|max:255',
            'zip_id'            => 'required|integer|exists:zipcodes,id',
            'city_id'           => 'required|integer|exists:cities,id',
            'state_id'          => 'required|integer|exists:states,id',
            'country_id'        => 'required|integer|exists:countries,id',
            'billing_email'     => ($type === 'billing' ? 'required|' : 'nullable|') . 'email|max:150',
        ]);

        $doctor = Doctor::where('user_id', Auth::id())->firstOrFail();

        $isFirst = ! $doctor->addresses()->where('type', $data['type'])->exists();

        $doctor->addresses()->create([
            'type'             => $data['type'],
            'street_address_1' => $data['street_address_1'],
            'street_address_2' => $data['street_address_2'] ?? null,
            'zip_id'           => $data['zip_id'],
            'city_id'          => $data['city_id'],
            'state_id'         => $data['state_id'],
            'country_id'       => $data['country_id'],
            'billing_email'    => $data['billing_email'] ?? null,
            'is_default'       => $isFirst,
        ]);

        return redirect()
            ->route('doctor.profile.index', ['tab' => $data['type']])
            ->with('success', ucfirst($data['type']) . ' address saved successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/',
            'new_password_confirmation' => 'required|same:new_password'
        ], [
            'new_password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number and one special character.'
        ]);

        $user = Auth::user();

        if (!Hash::check($request->old_password, $user->password_hash)) {
            return back()->withErrors(['old_password' => 'The provided password does not match your current password.']);
        }

        $user->password_hash = Hash::make($request->new_password);
        $user->save();

        return redirect('/dev/cases/list')->with('success', 'Password updated successfully.');
    }

    // ──────────────────────────────────────────────────────────────────
    //  Image uploads (doctor avatar + practice logo) — local public disk
    // ──────────────────────────────────────────────────────────────────

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $doctor = Doctor::where('user_id', Auth::id())->firstOrFail();

        // Delete previous file if it exists on disk
        if ($doctor->profile_photo_s3_key && Storage::disk('public')->exists($doctor->profile_photo_s3_key)) {
            Storage::disk('public')->delete($doctor->profile_photo_s3_key);
        }

        $path = $request->file('avatar')->store("doctors/{$doctor->id}", 'public');
        $doctor->update(['profile_photo_s3_key' => $path]);

        return back()->with('success', 'Profile photo updated.');
    }

    public function deleteAvatar()
    {
        $doctor = Doctor::where('user_id', Auth::id())->firstOrFail();
        if ($doctor->profile_photo_s3_key && Storage::disk('public')->exists($doctor->profile_photo_s3_key)) {
            Storage::disk('public')->delete($doctor->profile_photo_s3_key);
        }
        $doctor->update(['profile_photo_s3_key' => null]);

        return back()->with('success', 'Profile photo removed.');
    }

    public function uploadPracticeLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $practice = currentPractice();
        if (! $practice) {
            return back()->with('error', 'No active practice to update. Switch to a practice from the top bar.');
        }

        if ($practice->logo_path && Storage::disk('public')->exists($practice->logo_path)) {
            Storage::disk('public')->delete($practice->logo_path);
        }

        $path = $request->file('logo')->store("practices/{$practice->id}", 'public');
        $practice->update(['logo_path' => $path]);

        return back()->with('success', 'Practice photo updated.');
    }

    public function deletePracticeLogo()
    {
        $practice = currentPractice();
        if (! $practice) {
            return back()->with('error', 'No active practice to update.');
        }

        if ($practice->logo_path && Storage::disk('public')->exists($practice->logo_path)) {
            Storage::disk('public')->delete($practice->logo_path);
        }
        $practice->update(['logo_path' => null]);

        return back()->with('success', 'Practice photo removed.');
    }
}
