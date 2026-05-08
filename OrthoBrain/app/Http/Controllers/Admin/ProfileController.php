<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function index()
    {
        $admin = Admin::firstOrCreate(
            ['user_id' => Auth::id()],
            ['first_name' => 'Admin', 'last_name' => '']
        );

        $phoneCodes = Country::where('status', 'ACTIVE')
            ->select('phone_code')->distinct()->orderBy('phone_code')->pluck('phone_code')->all();

        return view('admin.profile.index', compact('admin', 'phoneCodes'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'first_name'         => 'required|string|max:100|regex:/^[A-Za-z\s\-]+$/',
            'last_name'          => 'required|string|max:100|regex:/^[A-Za-z\s\-]+$/',
            'email'              => 'required|email|max:150|unique:users,email,' . Auth::id(),
            'phone_country_code' => 'nullable|string|max:10',
            'phone_number'       => 'nullable|string|regex:/^\d{10}$/',
        ], [
            'first_name.regex' => 'First name may only contain letters, spaces and hyphens.',
            'last_name.regex'  => 'Last name may only contain letters, spaces and hyphens.',
        ]);

        $user = Auth::user();
        $user->email = $request->email;
        $user->save();

        $admin = Admin::firstOrNew(['user_id' => $user->id]);
        $admin->first_name         = $request->first_name;
        $admin->last_name          = $request->last_name;
        $admin->phone_country_code = $request->phone_country_code;
        $admin->phone_number       = $request->phone_number
            ? preg_replace('/\D/', '', $request->phone_number)
            : null;
        $admin->save();

        return back()->with('success', 'Account information updated successfully.');
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $admin = Admin::where('user_id', Auth::id())->firstOrFail();

        if ($admin->profile_photo_s3_key && Storage::disk('public')->exists($admin->profile_photo_s3_key)) {
            Storage::disk('public')->delete($admin->profile_photo_s3_key);
        }

        $path = $request->file('avatar')->store("admins/{$admin->id}", 'public');
        $admin->update(['profile_photo_s3_key' => $path]);

        return back()->with('success', 'Profile photo updated.');
    }

    public function deleteAvatar()
    {
        $admin = Admin::where('user_id', Auth::id())->firstOrFail();

        if ($admin->profile_photo_s3_key && Storage::disk('public')->exists($admin->profile_photo_s3_key)) {
            Storage::disk('public')->delete($admin->profile_photo_s3_key);
        }
        $admin->update(['profile_photo_s3_key' => null]);

        return back()->with('success', 'Profile photo removed.');
    }
}
