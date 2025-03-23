<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Turahe\Core\Enums\OrganizationType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique()->index();
            $table->string('code')->index()->nullable();

            $table->unsignedBigInteger('record_left')->index()->nullable();
            $table->unsignedBigInteger('record_right')->index()->nullable();
            $table->unsignedBigInteger('record_ordering')->index()->nullable();
            $table->foreignUlid('parent_id')->index()->nullable();

            /**
             * Organizational unit type
             */
            $table->enum('type', array_column(OrganizationType::cases(), 'value'))->index();

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

            $table->index('id', 'organizations_id_idx', 'hash');

        });

        Schema::create('model_has_organization', function (Blueprint $table): void {
            $table->ulidMorphs('model');
            $table->ulid('organization_id');
            $table->enum('role', ['OWNER', 'MEMBER', 'ADMIN'])->default('OWNER');

            $table->index('organization_id', 'model_has_organization_organization_id_index', 'hash');
            $table->index('model_id', 'model_has_organization_model_id_index', 'hash');
            $table->index('model_type', 'model_has_organization_model_type_index', 'hash');
            $table->index('role', 'model_has_organization_role_index', 'hash');
            $table->integer('created_at')->index()->nullable();
            $table->integer('updated_at')->index()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('model_has_organization');
        Schema::dropIfExists('organizations');
    }
};
