<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * TECHNICAL_AUDIT.md L3 / IMPLEMENTATION_PLAN.md Milestone M5.2:
     * `media` was scaffolding for spatie/laravel-medialibrary, a
     * dependency no model ever used (zero InteractsWithMedia traits).
     * Follows the same drop-migration pattern already used to retire
     * `is_active` (2026_08_06_185140_drop_is_active_from_sios_and_silos_tables.php)
     * rather than deleting the original creation migration, so
     * environments that already ran it stay consistent.
     */
    public function up(): void
    {
        Schema::dropIfExists('media');
    }

    public function down(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();

            $table->morphs('model');
            $table->uuid('uuid')->nullable()->unique();
            $table->string('collection_name');
            $table->string('name');
            $table->string('file_name');
            $table->string('mime_type')->nullable();
            $table->string('disk');
            $table->string('conversions_disk')->nullable();
            $table->unsignedBigInteger('size');
            $table->json('manipulations');
            $table->json('custom_properties');
            $table->json('generated_conversions');
            $table->json('responsive_images');
            $table->unsignedInteger('order_column')->nullable()->index();

            $table->nullableTimestamps();
        });
    }
};
