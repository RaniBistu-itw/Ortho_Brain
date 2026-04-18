<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scanners', function (Blueprint $table) {
            // Strict ERD: id, name, description, portal_password, portal_link,
            // status, timestamps, soft deletes.
            // (ERD typo 'desciption' corrected to 'description'; enum labels
            //  normalized to ACTIVE/INACTIVE — per earlier team decision.)
            $table->id();
            $table->string('name', 255);
            $table->string('description', 255)->nullable();
            $table->string('portal_password', 255)->nullable();
            $table->string('portal_link', 255)->nullable();
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scanners');
    }
};
