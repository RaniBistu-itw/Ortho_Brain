<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->renameColumn('phone', 'phone_number');
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->string('phone_country_code', 10)->nullable()->after('phone_number');
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('phone_country_code');
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->renameColumn('phone_number', 'phone');
        });
    }
};
