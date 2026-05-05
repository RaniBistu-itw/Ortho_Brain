<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Link cases to their patient row. Nullable because (a) existing rows
     * predate this migration and have no patient, and (b) a brand-new case
     * shell is created BEFORE the patient form is filled — the FK gets set
     * on the first patient autosave, not at case shell creation.
     *
     * onDelete: nullOnDelete so deleting a patient doesn't cascade-wipe
     * their cases (admin still needs to see them); the case retains its
     * doctor / practice scope.
     */
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->foreignId('patient_id')
                ->nullable()
                ->after('practice_id')
                ->constrained('patients')
                ->nullOnDelete();
            $table->index('patient_id');
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->dropIndex(['patient_id']);
            $table->dropColumn('patient_id');
        });
    }
};
