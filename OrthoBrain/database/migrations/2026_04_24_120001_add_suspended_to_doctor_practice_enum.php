<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE doctor_practice
            MODIFY COLUMN approval_status
            ENUM('PENDING','APPROVED','REJECTED','CANCELLED','LEFT','SUSPENDED')
            NOT NULL DEFAULT 'PENDING'
        ");
    }

    public function down(): void
    {
        DB::statement("
            UPDATE doctor_practice SET approval_status = 'REJECTED'
            WHERE approval_status = 'SUSPENDED'
        ");
        DB::statement("
            ALTER TABLE doctor_practice
            MODIFY COLUMN approval_status
            ENUM('PENDING','APPROVED','REJECTED','CANCELLED','LEFT')
            NOT NULL DEFAULT 'PENDING'
        ");
    }
};
