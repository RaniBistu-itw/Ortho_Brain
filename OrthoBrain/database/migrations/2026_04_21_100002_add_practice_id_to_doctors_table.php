<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn([
                'practice_name',
                'practice_phone_country_code',
                'practice_phone_number',
                'practice_website',
            ]);

            $table->foreignId('practice_id')
                ->nullable()
                ->after('user_id')
                ->constrained('practices')
                ->nullOnDelete();

            $table->index('practice_id');
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropForeign(['practice_id']);
            $table->dropIndex(['practice_id']);
            $table->dropColumn('practice_id');

            $table->string('practice_name', 200);
            $table->enum('practice_phone_country_code', ['+1_US', '+1_CA', '+61_AU']);
            $table->string('practice_phone_number', 20);
            $table->string('practice_website');
        });
    }
};
