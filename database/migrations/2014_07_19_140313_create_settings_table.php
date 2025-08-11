<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(config('core.tables.settings'), function (Blueprint $table): void {
            if (config('userstamps.users_table_column_type') === 'bigincrements') {
                $table->id();
                $table->morphs('model');
            }
            if (config('userstamps.users_table_column_type') === 'ulid') {
                $table->ulid('id')->primary();
                $table->nullableUlidMorphs('model');
            }
            if (config('userstamps.users_table_column_type') === 'uuid') {
                $table->uuid('id')->primary();
                $table->nullableUuidMorphs('model');
            }

            $table->string('key')->index();
            $table->string('value')->index()->nullable();
            $table->string('group')->index()->nullable();

            // Create userstamp columns with correct data types
            if (config('userstamps.users_table_column_type') === 'bigincrements') {
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->unsignedBigInteger('updated_by')->nullable()->index();
                $table->unsignedBigInteger('deleted_by')->nullable()->index();
            }
            if (config('userstamps.users_table_column_type') === 'ulid') {
                $table->ulid('created_by')->nullable()->index();
                $table->ulid('updated_by')->nullable()->index();
                $table->ulid('deleted_by')->nullable()->index();
            }
            if (config('userstamps.users_table_column_type') === 'uuid') {
                $table->uuid('created_by')->nullable()->index();
                $table->uuid('updated_by')->nullable()->index();
                $table->uuid('deleted_by')->nullable()->index();
            }

            $table->timestamps();
            $table->softDeletes();

            // Add foreign key constraints for userstamps
            if (config('userstamps.users_table_column_type') === 'bigincrements') {
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            }
            if (config('userstamps.users_table_column_type') === 'ulid') {
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            }
            if (config('userstamps.users_table_column_type') === 'uuid') {
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            }

            $table->index('id', 'settings_id_idx', 'hash');
            $table->index('model_id', 'settings_model_id_idx', 'hash');
            $table->index('model_type', 'settings_model_type_idx', 'hash');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('core.tables.settings'));
    }
};
