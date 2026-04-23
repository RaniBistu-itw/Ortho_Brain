<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_practice', function (Blueprint $table) {
            $table->id();

            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('practice_id')->constrained('practices')->cascadeOnDelete();

            $table->enum('approval_status', ['PENDING', 'APPROVED', 'REJECTED', 'CANCELLED', 'LEFT'])
                ->default('PENDING');

            $table->boolean('is_primary')->default(false);

            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('left_at')->nullable();

            $table->timestamps();

            $table->index(['doctor_id', 'approval_status']);
            $table->index(['practice_id', 'approval_status']);
        });

        // Backfill: every doctor with a practice_id gets an APPROVED+primary pivot row.
        // Doctors still PENDING/REJECTED at the account level get a matching link state
        // so the link table mirrors reality on day 1.
        $now = now();
        DB::table('doctors')->whereNotNull('practice_id')->orderBy('id')->chunkById(200, function ($doctors) use ($now) {
            $rows = [];
            foreach ($doctors as $doctor) {
                $linkStatus = match ($doctor->approval_status) {
                    'APPROVED', 'SUSPENDED' => 'APPROVED',
                    'REJECTED'              => 'REJECTED',
                    default                 => 'PENDING',
                };
                $rows[] = [
                    'doctor_id'            => $doctor->id,
                    'practice_id'          => $doctor->practice_id,
                    'approval_status'      => $linkStatus,
                    'is_primary'           => true,
                    'requested_at'         => $doctor->created_at ?? $now,
                    'approved_at'          => $linkStatus === 'APPROVED' ? ($doctor->approved_at ?? $now) : null,
                    'approved_by_admin_id' => $linkStatus === 'APPROVED' ? $doctor->approved_by_admin_id : null,
                    'rejected_at'          => $linkStatus === 'REJECTED' ? ($doctor->updated_at ?? $now) : null,
                    'rejection_reason'     => $linkStatus === 'REJECTED' ? $doctor->rejection_reason : null,
                    'left_at'              => null,
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ];
            }
            if ($rows) {
                DB::table('doctor_practice')->insert($rows);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_practice');
    }
};
