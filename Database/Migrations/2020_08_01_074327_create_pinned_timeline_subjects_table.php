<?php
/*
 * This source code is the proprietary and confidential information of
 * Nur Wachid. You may not disclose, copy, distribute,
 *  or use this code without the express written permission of
 * Nur Wachid.
 *
 * Copyright (c) 2022-2023.
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
        Schema::create('pinned_timeline_subjects', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->morphs('subject');
            // Using custom INDEX name
            // SQLSTATE[42000]: Syntax error or access violation: 1059 Identifier name 'tbl_pinned_timeline_subjects_timelineable_type_timelineable_id_index' is too long (SQL: alter table `tbl_pinned_timeline_subjects` add index `tbl_pinned_timeline_subjects_timelineable_type_timelineable_id_index`(`timelineable_type`, `timelineable_id`))
            $table->morphs('timelineable', 'timelineable_type_timelineable_id_index');
            $table->foreignUlid('created_by')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignUlid('updated_by')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignUlid('deleted_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @codeCoverageIgnore
     */
    public function down(): void
    {
        Schema::dropIfExists('pinned_timeline_subjects');
    }
};
