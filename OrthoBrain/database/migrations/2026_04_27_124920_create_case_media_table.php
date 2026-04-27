<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Server-side persistence for case Photographs and X-Rays. Replaces the
     * browser-only IndexedDB store (case-image-store.js), which was
     * device-local and so invisible across browsers / to admins.
     *
     * One row per (case, section, tile) — unique constraint enforces it.
     * Files live on the `public` disk under
     * case-media/{case_id}/{section}/{uuid}.{ext} so admins and PDF export
     * can read them without a controller round-trip.
     */
    public function up(): void
    {
        Schema::create('case_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')
                ->constrained('cases')
                ->cascadeOnDelete();
            $table->enum('section', ['photograph', 'xray']);
            $table->string('tile_id', 40);
            $table->string('disk', 20)->default('public');
            $table->string('path', 255);
            $table->string('mime_type', 80);
            $table->unsignedInteger('size_bytes');
            $table->string('original_name', 255)->nullable();
            $table->json('crop_params')->nullable();
            $table->timestamps();

            $table->unique(['case_id', 'section', 'tile_id']);
            $table->index(['case_id', 'section']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_media');
    }
};
