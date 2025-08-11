<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('core.tables.tags'), function (Blueprint $table): void {
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

            $table->string('name');
            $table->string('slug')->index()->unique();
            $table->string('type')->nullable();
            $table->integer('record_ordering')->nullable();

            $table->userstamps();
            $table->softUserstamps();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create(config('core.tables.taggables'), function (Blueprint $table): void {
            if (config('userstamps.users_table_column_type') === 'bigincrements') {
                $table->foreign('tag_id')->references('id')->on(config('core.tables.tags'))->onDelete('cascade');
                $table->morphs('taggable');
            }
            if (config('userstamps.users_table_column_type') === 'ulid') {
                $table->foreignUlid('tag_id')->constrained()->cascadeOnDelete();
                $table->ulidMorphs('taggable');
            }
            if (config('userstamps.users_table_column_type') === 'uuid') {
                $table->foreignUuid('tag_id')->constrained()->cascadeOnDelete();
                $table->uuidMorphs('taggable');
            }

            $table->unique(['tag_id', 'taggable_id', 'taggable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('core.tables.taggables'));
        Schema::dropIfExists(config('core.tables.tags'));
    }
};
