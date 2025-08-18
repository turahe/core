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
        Schema::create(config('core.tables.organizations'), function (Blueprint $table): void {
            if (config('userstamps.users_table_column_type') === 'bigincrements') {
                $table->id();
            }
            if (config('userstamps.users_table_column_type') === 'ulid') {
                $table->ulid('id')->primary();
            }
            if (config('userstamps.users_table_column_type') === 'uuid') {
                $table->uuid('id')->primary();
            }

            $table->string('name');
            $table->string('slug')->unique()->index();
            $table->string('code')->index()->nullable();

            $table->unsignedBigInteger('record_left')->index()->nullable();
            $table->unsignedBigInteger('record_right')->index()->nullable();
            $table->unsignedBigInteger('record_ordering')->index()->nullable();
            if (config('userstamps.users_table_column_type') === 'bigincrements') {
                $table->unsignedBigInteger('parent_id')->nullable()->index();
            }
            if (config('userstamps.users_table_column_type') === 'ulid') {
                $table->ulid('parent_id')->nullable()->index();
            }
            if (config('userstamps.users_table_column_type') === 'uuid') {
                $table->uuid('parent_id')->nullable()->index();
            }

            /**
             * Organizational unit type
             */
            $table->enum('type', array_column(OrganizationType::cases(), 'value'))->index();

            // Create userstamp columns with correct data types
            if (config('userstamps.users_table_column_type') === 'bigincrements') {
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->unsignedBigInteger('updated_by')->nullable()->index();
                $table->unsignedBigInteger('deleted_by')->nullable()->index();
            }
            if (config('userstamps.users_table_column_type') === 'ulid') {
                $table->ulid('created_by')->nullable()->index();
                $table->ulid('updated_by')->nullable()->index();
                $table->ulid('deleted_by')->nullable()->index();
            }
            if (config('userstamps.users_table_column_type') === 'uuid') {
                $table->uuid('created_by')->nullable()->index();
                $table->uuid('updated_by')->nullable()->index();
                $table->uuid('deleted_by')->nullable()->index();
            }

            $table->timestamps();
            $table->softDeletes();

            // Add foreign key constraints for parent_id (self-referencing)
            if (config('userstamps.users_table_column_type') === 'bigincrements') {
                $table->foreign('parent_id')->references('id')->on(config('core.tables.organizations'))->onDelete('cascade');
            }
            if (config('userstamps.users_table_column_type') === 'ulid') {
                $table->foreign('parent_id')->references('id')->on(config('core.tables.organizations'))->onDelete('cascade');
            }
            if (config('userstamps.users_table_column_type') === 'uuid') {
                $table->foreign('parent_id')->references('id')->on(config('core.tables.organizations'))->onDelete('cascade');
            }

            // Add foreign key constraints for userstamps
            if (config('userstamps.users_table_column_type') === 'bigincrements') {
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            }
            if (config('userstamps.users_table_column_type') === 'ulid') {
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            }
            if (config('userstamps.users_table_column_type') === 'uuid') {
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            }

            $table->index('id', 'organizations_id_idx', 'hash');

        });

        Schema::create(config('core.tables.model_has_organization'), function (Blueprint $table): void {
            if (config('userstamps.users_table_column_type') === 'bigincrements') {
                $table->unsignedBigInteger('organization_id');
                $table->morphs('model');
                $table->foreign('organization_id')->references('id')->on(config('core.tables.organizations'))->onDelete('cascade');
            }
            if (config('userstamps.users_table_column_type') === 'ulid') {
                $table->ulid('organization_id');
                $table->ulidMorphs('model');
                $table->foreign('organization_id')->references('id')->on(config('core.tables.organizations'))->onDelete('cascade');
            }
            if (config('userstamps.users_table_column_type') === 'uuid') {
                $table->uuid('organization_id');
                $table->uuidMorphs('model');
                $table->foreign('organization_id')->references('id')->on(config('core.tables.organizations'))->onDelete('cascade');
            }

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
        Schema::dropIfExists(config('core.tables.model_has_organization'));
        Schema::dropIfExists(config('core.tables.organizations'));
    }
};
