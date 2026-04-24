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

        DB::statement("
            UPDATE cases c
            JOIN doctors d ON c.doctor_id = d.id
            SET c.practice_id = d.practice_id
            WHERE c.practice_id IS NULL
        ");
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
