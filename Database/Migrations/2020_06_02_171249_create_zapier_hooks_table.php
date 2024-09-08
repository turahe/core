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
        Schema::create('zapier_hooks', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('hook');
            $table->string('resource_name');
            $table->string('action');
            $table->text('data')->nullable();
            $table->unsignedBigInteger('zap_id')->index();
            $table->foreignId(\Turahe\Users\Models\User::class)->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @codeCoverageIgnore
     */
    public function down(): void
    {
        Schema::dropIfExists('zapier_hooks');
    }
};
