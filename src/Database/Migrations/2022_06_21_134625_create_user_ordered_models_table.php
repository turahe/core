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

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_ordered_models', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->unsignedInteger('display_order')->index();
            $table->foreignIdFor(\Turahe\Users\Models\User::class);
            $table->morphs('orderable');
            $table->unique(['user_id', 'orderable_id', 'orderable_type'], 'unique_order');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @codeCoverageIgnore
     */
    public function down(): void
    {
        Schema::dropIfExists('user_ordered_models');
    }
};
