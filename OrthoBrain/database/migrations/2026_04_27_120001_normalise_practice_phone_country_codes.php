<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::table('practices')
            ->whereNotNull('phone_country_code')
            ->where('phone_country_code', 'LIKE', '%\_%')
            ->update([
                'phone_country_code' => DB::raw("SUBSTRING_INDEX(phone_country_code, '_', 1)"),
            ]);
    }

    public function down(): void
    {
        // One-way data normalisation. Reverting would require knowing the original country
        // code per row, which we no longer have — leave as no-op.
    }
};
