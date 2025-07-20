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
        Schema::create(config('core.tables.oauth_accounts'), function (Blueprint $table): void {
            $table->ulid('id')->primary();

            $table->string('type');
            $table->foreignIdFor(config('auth.providers.users.model', \App\Models\User::class), 'user_id');
            $table->string('oauth_user_id');
            $table->string('email')->nullable();
            $table->boolean('requires_auth')->default(false);
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->integer('expires');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('core.tables.oauth_accounts'));
    }
};
