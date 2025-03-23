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
        Schema::create('taxonomies', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('name')->index();
            $table->string('slug')->index()->unique();
            $table->string('code')->index()->nullable();
            $table->text('description')->nullable();

            $table->unsignedBigInteger('record_left')->index()->nullable();
            $table->unsignedBigInteger('record_right')->index()->nullable();
            $table->unsignedBigInteger('record_ordering')->index()->nullable();
            $table->foreignUlid('parent_id')->index()->nullable();

            $table->userstamps();
            $table->softUserstamps();

            if (config('core.table.use_timestamps')) {
                $table->timestamps();
                $table->softDeletes();
            } else {
                $table->integer('created_at')->index()->nullable();
                $table->integer('updated_at')->index()->nullable();
                $table->integer('deleted_at')->index()->nullable();
            }

            $table->index('id', 'taxonomies_id_idx', 'hash');

        });

        Schema::create('model_has_taxonomies', function (Blueprint $table): void {
            $table->ulidMorphs('model');
            $table->ulid('taxonomy_id')->index();
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
        Schema::dropIfExists('model_has_taxonomies');
        Schema::dropIfExists('taxonomies');
    }
};
