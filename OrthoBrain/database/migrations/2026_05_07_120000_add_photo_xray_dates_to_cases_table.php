<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->date('photos_date')->nullable()->after('submitted_at');
            $table->date('xrays_date')->nullable()->after('photos_date');
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn(['photos_date', 'xrays_date']);
        });
    }
};
