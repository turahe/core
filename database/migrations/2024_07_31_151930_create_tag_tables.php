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
            $table->ulid('id')->primary();

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
            $table->foreignUlid('tag_id')->constrained()->cascadeOnDelete();

            $table->ulidMorphs('taggable');

            $table->unique(['tag_id', 'taggable_id', 'taggable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('core.tables.taggables'));
        Schema::dropIfExists(config('core.tables.tags'));
    }
};
