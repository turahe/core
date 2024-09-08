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
        Schema::create('model_visibility_group_dependents', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(\Turahe\Core\Models\ModelVisibilityGroup::class, 'model_visibility_group_id');
            $table->morphs('dependable', 'dependable_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @codeCoverageIgnore
     */
    public function down(): void
    {
        Schema::dropIfExists('model_visibility_group_dependents');
    }
};
