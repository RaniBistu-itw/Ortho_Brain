<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            // Length 5: matches the existing client-side enforcement
            // (blade maxlength="5", JS regex /^[A-Za-z]{2,5}$/, help
            // text "2-5 letters only"). Nullable so the 253 cases
            // already past DRAFT (status SUBMITTED/IN_REVIEW/APPROVED/
            // REJECTED) can coexist — they were submitted before this
            // field existed and have no initials to backfill.
            $table->string('submitter_initials', 5)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn('submitter_initials');
        });
    }
};
