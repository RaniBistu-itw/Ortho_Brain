<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->foreignId('practice_id')
                ->nullable()
                ->after('doctor_id')
                ->constrained('practices')
                ->nullOnDelete();
            $table->index(['doctor_id', 'practice_id']);
        });

        DB::table('cases')
            ->whereNull('practice_id')
            ->update([
                'practice_id' => DB::table('doctors')
                    ->whereColumn('doctors.id', 'cases.doctor_id')
                    ->select('practice_id')
                    ->limit(1)
            ]);
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropForeign(['practice_id']);
            $table->dropIndex(['doctor_id', 'practice_id']);
            $table->dropColumn('practice_id');
        });
    }
};
