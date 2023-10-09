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

namespace Modules\Core\Models;

use DateTime;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Synchronization\SyncState;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Contracts\Synchronization\SynchronizesViaWebhook;

/**
 * Modules\Core\Models\Synchronization
 *
 * @property SyncState $sync_state
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $synchronizable
 *
 * @method static Builder|Synchronization enabled()
 * @method static Builder|Synchronization enabledPeriodicable()
 * @method static Builder|Synchronization newModelQuery()
 * @method static Builder|Synchronization newQuery()
 * @method static Builder|Synchronization notDisabled()
 * @method static Builder|Model orderByNullsLast(string $column, string $direction = 'asc')
 * @method static Builder|Synchronization query()
 * @method static Builder|Model withCommon()
 * @method static Builder|Synchronization withoutOAuthAuthenticationRequired()
 *
 * @property string $id
 * @property string $synchronizable_type
 * @property int $synchronizable_id
 * @property string|null $token
 * @property string|null $resource_id
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon $last_synchronized_at
 * @property \Illuminate\Support\Carbon $start_sync_from
 * @property string|null $sync_state_comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static Builder|Synchronization whereCreatedAt($value)
 * @method static Builder|Synchronization whereExpiresAt($value)
 * @method static Builder|Synchronization whereId($value)
 * @method static Builder|Synchronization whereLastSynchronizedAt($value)
 * @method static Builder|Synchronization whereResourceId($value)
 * @method static Builder|Synchronization whereStartSyncFrom($value)
 * @method static Builder|Synchronization whereSyncState($value)
 * @method static Builder|Synchronization whereSyncStateComment($value)
 * @method static Builder|Synchronization whereSynchronizableId($value)
 * @method static Builder|Synchronization whereSynchronizableType($value)
 * @method static Builder|Synchronization whereToken($value)
 * @method static Builder|Synchronization whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Synchronization extends Model
{
    use HasUlids;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'last_synchronized_at' => 'datetime',
        'start_sync_from'      => 'datetime',
        'expires_at'           => 'datetime',
        'sync_state'           => SyncState::class,
    ];

    /**
     * Check whether sync is enabled for this synchronization instance
     */
    public function enabled(): bool
    {
        return ! $this->isSyncDisabled() && ! $this->isSyncStoppedBySystem();
    }

    /**
     * Check whether the user disabled the sync
     */
    public function isSyncDisabled(): bool
    {
        return $this->sync_state === SyncState::DISABLED;
    }

    /**
     * Check whether the system disabled the sync
     */
    public function isSyncStoppedBySystem(): bool
    {
        return $this->sync_state === SyncState::STOPPED;
    }

    /**
     * Ping the snychronizable to synchronize the data
     *
     * @return mixed
     */
    public function ping()
    {
        return $this->synchronizable->synchronize();
    }

    /**
     * Start listening for changes
     *
     * @return mixed
     */
    public function startListeningForChanges()
    {
        return $this->synchronizable->synchronizer()->watch($this);
    }

    /**
     * Stop listening for changes
     */
    public function stopListeningForChanges(): void
    {
        if (! $this->isSynchronizingViaWebhook()) {
            return;
        }

        $this->synchronizable->synchronizer()->unwatch($this);
    }

    /**
     * Refresh the synchronizable webhook
     */
    public function refreshWebhook(): static
    {
        $this->stopListeningForChanges();

        // Update the UUID since the previous one has
        // already been associated to watcher
        $this->{static::getUuidColumnName()} = static::generateUuid();
        $this->save();

        $this->startListeningForChanges();

        return $this;
    }

    /**
     * Get the synchronizable model
     */
    public function synchronizable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Boot the model
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->last_synchronized_at = now();
            $model->start_sync_from = now();
        });

        static::created(function ($model) {
            if ($model->synchronizable->synchronizer() instanceof SynchronizesViaWebhook) {
                $model->startListeningForChanges();
            }

            $model->ping();
        });

        static::deleting(function ($model) {
            if ($model->synchronizable->synchronizer() instanceof SynchronizesViaWebhook) {
                try {
                    $model->stopListeningForChanges();
                } catch (\Exception) {
                }
            }
        });
    }

    /**
     * Check whether the synchronization is currently synchronizing via webhook
     */
    public function isSynchronizingViaWebhook(): bool
    {
        return ! is_null($this->resource_id);
    }

    /**
     * Get the model uuid column name
     */
    protected static function getUuidColumnName(): string
    {
        return 'id';
    }

    /**
     * Scope a query to only include enabled synchronizations.
     */
    public function scopeEnabled(Builder $query): void
    {
        $query->where('sync_state', SyncState::ENABLED);
    }

    /**
     * Scope a query to exclude disabled synchronizations.
     */
    public function scopeNotDisabled(Builder $query): void
    {
        $query->where('sync_state', '!=', SyncState::DISABLED);
    }

    /**
     * Apply where query where oauth account does not require authentication.
     */
    public function scopeWithoutOAuthAuthenticationRequired(Builder $query): void
    {
        $query->whereHas('synchronizable', function ($query) {
            return $query->whereHas('oAuthAccount', function ($query) {
                return $query->where('requires_auth', false);
            });
        });
    }

    /**
     * Mark the synchronization as webhook synchronizable.
     */
    public function markAsWebhookSynchronizable(string $resourceId, string|Carbon|DateTime $expiresAt): static
    {
        $this->fill([
            'resource_id' => $resourceId,
            'expires_at'  => $expiresAt instanceof Carbon ? $expiresAt : Carbon::parse($expiresAt),
        ])->save();

        return $this;
    }

    /**
     * Unmark the synchronization as webhook synchronizable.
     */
    public function unmarkAsWebhookSynchronizable(): static
    {
        $this->fill(['resource_id' => null, 'expires_at' => null])->save();

        return $this;
    }

    /**
     * Set the sync state.
     */
    public function setSyncState(SyncState $state, ?string $comment = null): static
    {
        $this->fill(['sync_state' => $state, 'sync_state_comment' => $comment])->save();

        return $this;
    }

    /**
     * Enable synchronization.
     */
    public function enableSync(): static
    {
        $this->loadMissing('synchronizable');

        // When enabling synchronization, we will try again to re-configure the webhook
        // and catch any URL related errors, if any errors, the sync won't be enabled
        if ($this->synchronizable->synchronizer() instanceof SynchronizesViaWebhook) {
            $this->refreshWebhook();
        }

        $this->setSyncState(SyncState::ENABLED);

        return $this;
    }

    /**
     * Disable synchronization.
     */
    public function disableSync(?string $comment = null): static
    {
        if ($this->isSynchronizingViaWebhook()) {
            $this->stopListeningForChanges();
        }

        $this->setSyncState(SyncState::DISABLED, $comment);

        $this->fill(['token' => null])->save();

        return $this;
    }

    /**
     * Stop synchronization.
     */
    public function stopSync(?string $comment = null): static
    {
        if ($this->isSynchronizingViaWebhook()) {
            $this->stopListeningForChanges();
        }

        $this->setSyncState(SyncState::STOPPED, $comment);

        $this->fill(['token' => null])->save();

        return $this;
    }

    /**
     * Scope a query to only include synchronization for period sync.
     */
    public function scopeEnabledPeriodicable(Builder $query): void
    {
        $query->withoutOAuthAuthenticationRequired()
            ->whereNull('resource_id')
            ->enabled();
    }

    /**
     * Update the last synchronized date.
     */
    public function updateLastSyncDate(array $extra = []): static
    {
        Model::withoutTimestamps(
            fn () => $this->fill(array_merge(['last_synchronized_at' => now()], $extra))->save()
        );

        return $this;
    }
}
