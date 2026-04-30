<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * HIGH-priority performance indexes.
 *
 * Source: docs/missing-indexes.md (audited 2026-04-29).
 * Each index here addresses a query pattern that fires on most page loads
 * or on every dashboard render. After applying, run EXPLAIN against the
 * cited queries on production-sized data to confirm the planner picks
 * these indexes — a composite with the wrong leading column can be worse
 * than no index.
 *
 *  1. countries(status, name)                  — idx_countries_status_name
 *     Pattern : WHERE status = 'ACTIVE' ORDER BY name
 *     Used in : Admin/CountryController:33, Admin/StateController:58,
 *               Admin/CityController:72, Admin/PracticeController:86,
 *               Admin/DoctorController:102, ProfileController,
 *               Auth/RegisterController — every page hydrating a country
 *               dropdown.
 *
 *  2. practices(status, name)                  — idx_practices_status_name
 *     Pattern : WHERE practices.status = ? [GROUP BY status | ORDER BY name]
 *     Used in : Admin/PracticeController:63, :81, PracticeController:30
 *     Note    : existing (owner_id, status) only helps when owner_id is
 *               also in the filter.
 *
 *  3. doctors(approval_status, last_name)      — idx_doctors_approval_status_last_name
 *     Pattern : WHERE approval_status = ? ORDER BY last_name
 *               + GROUP BY approval_status aggregate
 *     Used in : Admin/DoctorController:32, :53, :73 — fires on every
 *               admin doctors list load.
 *
 *  4. cases(status, updated_at)                — idx_cases_status_updated_at
 *     Pattern : WHERE status = ? ORDER BY updated_at DESC (->latest())
 *     Used in : DashboardController:54-69, :150-151,
 *               Admin/NotificationController:50, CasesController:44-46
 *     Note    : existing (doctor_id, status) doesn't help when doctor_id
 *               isn't in the filter (dashboard aggregates).
 *
 *  5. products_category(status, name)          — idx_products_category_status_name
 *     Pattern : WHERE status = 'ACTIVE' ORDER BY name
 *     Used in : Admin/ProductController:72, :89, :117,
 *               Admin/ProductSubcategoryController:51,
 *               Admin/ProductCategoryController:28
 *     Note    : products_category currently has NO non-PK indexes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->index(['status', 'name'], 'idx_countries_status_name');
        });

        Schema::table('practices', function (Blueprint $table) {
            $table->index(['status', 'name'], 'idx_practices_status_name');
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->index(['approval_status', 'last_name'], 'idx_doctors_approval_status_last_name');
        });

        Schema::table('cases', function (Blueprint $table) {
            $table->index(['status', 'updated_at'], 'idx_cases_status_updated_at');
        });

        Schema::table('products_category', function (Blueprint $table) {
            $table->index(['status', 'name'], 'idx_products_category_status_name');
        });
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropIndex('idx_countries_status_name');
        });

        Schema::table('practices', function (Blueprint $table) {
            $table->dropIndex('idx_practices_status_name');
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->dropIndex('idx_doctors_approval_status_last_name');
        });

        Schema::table('cases', function (Blueprint $table) {
            $table->dropIndex('idx_cases_status_updated_at');
        });

        Schema::table('products_category', function (Blueprint $table) {
            $table->dropIndex('idx_products_category_status_name');
        });
    }
};
