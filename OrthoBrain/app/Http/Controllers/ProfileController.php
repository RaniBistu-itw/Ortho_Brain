<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\Doctor;
use App\Models\User;

class ProfileController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'account');
        return view('profile.index', compact('tab'));
    }

    public function update(Request $request)
    {
        $tab = $request->query('tab', 'account');

        if ($tab === 'account') {
            $request->validate([
                'first_name' => 'required|string|max:100|regex:/^[A-Za-z\s\-]+$/',
                'last_name'  => 'required|string|max:100|regex:/^[A-Za-z\s\-]+$/',
                'email'      => 'required|email|max:150|unique:users,email,' . Auth::id(),
                'practice_phone_number' => 'nullable|string|max:20',
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
                if ($request->filled('practice_phone_number')) {
                    $doctor->practice_phone_number       = $request->practice_phone_number;
                    $doctor->practice_phone_country_code = $request->input('practice_phone_country_code', $doctor->practice_phone_country_code);
                }
                $doctor->save();
            }
        }

        if ($tab === 'practice') {
            $request->validate([
                'practice_name'         => 'required|string|max:200',
                'practice_phone_number' => 'required|string|max:20',
                'website'               => 'required|string|max:500',
                'language'              => 'nullable|string|max:50',
            ]);

            $user   = Auth::user();
            $doctor = Doctor::where('user_id', $user->id)->first();
            if ($doctor) {
                $doctor->practice_name               = $request->practice_name;
                $doctor->practice_phone_number       = preg_replace('/\D/', '', $request->practice_phone_number);
                $doctor->practice_phone_country_code = $request->input('practice_phone_country_code', $doctor->practice_phone_country_code);
                $doctor->practice_website            = $request->website;
                if ($request->filled('language')) {
                    $doctor->preferred_language = $request->language;
                }
                $doctor->save();
            }
        }

        return back()->with('success', ucfirst($tab) . ' information updated successfully.');
    }

    public function settings()
    {
        return view('profile.settings');
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
}
