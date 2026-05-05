<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->string('verification_otp', 6)->nullable()->after('email_verified_at');
            $table->timestamp('verification_otp_expires_at')->nullable()->after('verification_otp');
        });

        // Backfill existing seeded users (admin + test doctor) so the new
        // email-verified login gate doesn't lock them out.
        DB::table('users')->whereNull('email_verified_at')->update([
            'email_verified_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'email_verified_at',
                'verification_otp',
                'verification_otp_expires_at',
            ]);
        });
    }
};
