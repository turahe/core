<?php

declare(strict_types=1);
/*
 * This source code is the proprietary and confidential information of
 * Nur Wachid. You may not disclose, copy, distribute,
 *  or use this code without the express written permission of
 * Nur Wachid.
 *
 * Copyright (c) 2023.
 *
 *
 */

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
        Schema::create(config('core.tables.taxonomies'), function (Blueprint $table): void {
            if (config('userstamps.users_table_column_type') === 'bigincrements') {
                $table->id();
            }
            if (config('userstamps.users_table_column_type') === 'ulid') {
                $table->ulid('id')->primary();
            }
            if (config('userstamps.users_table_column_type') === 'uuid') {
                $table->uuid('id')->primary();
            }

            $table->string('name')->index();
            $table->string('slug')->index()->unique();
            $table->string('code')->index()->nullable();
            $table->text('description')->nullable();

            $table->unsignedBigInteger('record_left')->index()->nullable();
            $table->unsignedBigInteger('record_right')->index()->nullable();
            $table->unsignedBigInteger('record_ordering')->index()->nullable();
            if (config('userstamps.users_table_column_type') === 'bigincrements') {
                $table->foreign('parent_id')->references('id')->on(config('core.tables.taxonomies'))->onDelete('cascade');
            }
            if (config('userstamps.users_table_column_type') === 'ulid') {
                $table->foreignUlid('parent_id')->index()->nullable();
            }
            if (config('userstamps.users_table_column_type') === 'uuid') {
                $table->foreignUuid('parent_id')->index()->nullable();
            }

            $table->userstamps();
            $table->softUserstamps();

            $table->timestamps();
            $table->softDeletes();

            $table->index('id', 'taxonomies_id_idx', 'hash');

        });

        Schema::create(config('core.tables.model_has_taxonomies'), function (Blueprint $table): void {
            if (config('userstamps.users_table_column_type') === 'bigincrements') {
                $table->bigInteger('taxonomy_id')->index();
                $table->foreign('taxonomy_id')->references('id')->on(config('core.tables.taxonomies'))->onDelete('cascade');
                $table->morphs('model');
            }
            if (config('userstamps.users_table_column_type') === 'ulid') {
                $table->ulid('taxonomy_id')->index();
                $table->foreign('taxonomy_id')->references('id')->on(config('core.tables.taxonomies'))->onDelete('cascade');
                $table->ulidMorphs('model');
            }
            if (config('userstamps.users_table_column_type') === 'uuid') {
                $table->uuid('taxonomy_id')->index();
                $table->foreign('taxonomy_id')->references('id')->on(config('core.tables.taxonomies'))->onDelete('cascade');
                $table->uuidMorphs('model');
            }

            $table->integer('created_at')->index()->nullable();
            $table->integer('updated_at')->index()->nullable();

            $table->index('model_id', 'taxonomies_model_id_idx', 'hash');
            $table->index('model_type', 'taxonomies_model_type_idx', 'hash');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('core.tables.model_has_taxonomies'));
        Schema::dropIfExists(config('core.tables.taxonomies'));
    }
};
