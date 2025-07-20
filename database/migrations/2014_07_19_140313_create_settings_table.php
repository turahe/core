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
            $table->ulid('id')->primary();
            $table->nullableUlidMorphs('model');
            $table->string('key')->index();
            $table->string('value')->index()->nullable();
            $table->string('group')->index()->nullable();

            $table->userstamps();
            $table->softUserstamps();

            $table->timestamps();
            $table->softDeletes();

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
