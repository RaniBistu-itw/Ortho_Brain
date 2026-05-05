<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email    = env('SUPER_ADMIN_EMAIL', 'admin@orthobrain.local');
        $password = env('SUPER_ADMIN_PASSWORD', 'Password@1');

        DB::transaction(function () use ($email, $password) {
            // User model casts 'password_hash' => 'hashed',
            // so we pass the plain password and Laravel will bcrypt it.
            $user = User::withTrashed()->updateOrCreate(
                ['email' => $email],
                [
                    'password_hash'     => $password,
                    'role'              => 'ADMIN',
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ]
            );

            if ($user->trashed()) {
                $user->restore();
            }

            $admin = Admin::withTrashed()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'first_name' => 'Super',
                    'last_name'  => 'Admin',
                ]
            );

            if ($admin->trashed()) {
                $admin->restore();
            }
        });

        $this->command->info("Super admin ready: {$email}");
    }
}
