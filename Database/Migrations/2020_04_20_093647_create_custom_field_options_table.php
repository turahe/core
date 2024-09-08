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
        Schema::create('custom_field_options', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignIdFor(\Turahe\Core\Models\CustomField::class, 'custom_field_id');
            $table->string('name');
            $table->string('swatch_color', 7)->nullable();
            $table->unsignedInteger('display_order')->index();
            $table->unique(['custom_field_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @codeCoverageIgnore
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_field_options');
    }
};
