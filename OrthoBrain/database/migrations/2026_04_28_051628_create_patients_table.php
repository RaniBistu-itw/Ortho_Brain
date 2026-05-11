<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Real persistence layer for case patients. Scoped to (doctor, practice)
     * so a doctor's patient roster is per-practice, matching the
     * multi-practice model.
     *
     * The chart_id is intentionally NOT a hard unique constraint — empty
     * strings would collide and partial-unique indexes are MariaDB-quirky.
     * Dedupe is enforced application-side in PatientController::upsert.
     */
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')
                ->constrained('doctors')
                ->cascadeOnDelete();
            $table->foreignId('practice_id')
                ->constrained('practices')
                ->cascadeOnDelete();

            $table->string('first_name', 80);
            $table->string('last_name', 80);
            $table->date('date_of_birth');

            // Validated values: Male, Female, Non-binary, Prefer not to say,
            // Self-describe/Other. Stored as VARCHAR rather than ENUM so the
            // list can grow without a migration.
            $table->string('biological_gender', 40)->nullable();
            $table->string('biological_gender_other', 100)->nullable();

            $table->string('chart_id', 60)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('phone', 40)->nullable();
            $table->text('chief_complaint')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['doctor_id', 'last_name', 'first_name']);
            $table->index('practice_id');
            $table->index('chart_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
