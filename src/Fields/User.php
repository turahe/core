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

namespace Turahe\Core\Fields;

use Closure;
use Illuminate\Support\Facades\Auth;
use Turahe\Core\Facades\Innoclapps;
use Turahe\Core\Table\BelongsToColumn;
use Turahe\Users\Http\Resources\UserResource;
use Turahe\Users\Models\User as UserModel;

class User extends BelongsTo
{
    /**
     * The notification class name to be sent after user change
     */
    public ?string $notification = null;

    /**
     * Whether to skip sending the previously specified notification
     */
    public static bool $skipNotification = false;

    /**
     * The date column name to track changed date
     */
    public ?string $trackChangeDateColumn = null;

    /**
     * The assigneer
     *
     * @var \Turahe\Users\Models\User
     */
    public static $assigneer;

    /**
     * Creat new User instance field
     *
     * @param  string  $label  Custom label
     * @param  string  $relationName
     * @param  string|null  $attribute
     */
    public function __construct($label = null, $relationName = null, $attribute = null)
    {
        parent::__construct(
            $relationName ?: 'user',
            UserModel::class,
            $label ?: __('users::user.user'),
            $attribute
        );

        // Auth check for console usage
        $this->withDefaultValue(Auth::check() ? $this->createOption(Auth::user()) : null)
            ->importRules($this->getUserImportRules())
            ->setJsonResource(UserResource::class)
            ->tapIndexColumn(fn (BelongsToColumn $column) => $column->minWidth('100px'));
    }

    /**
     * Skip sending the notification
     */
    public static function skipNotification(bool $value = true): void
    {
        static::$skipNotification = $value;
    }

    /**
     * Provides the User instance options
     *
     * @return \Illuminate\Support\Collection
     */
    public function resolveOptions()
    {
        return UserModel::select([$this->valueKey, $this->labelKey, 'avatar'])
            ->orderBy($this->labelKey)
            ->get()
            ->map(fn ($user) => $this->createOption($user));
    }

    /**
     * Set the user that perform the assignee
     *
     * @param  \Turahe\Users\Models\User  $user
     */
    public static function setAssigneer($user)
    {
        static::$assigneer = $user;
    }

    /**
     * Send a notification when the user changes
     */
    public function notification(string $notification): static
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * Set date column to track the date when the user was changed.
     */
    public function trackChangeDate(string $dateColumn): static
    {
        $this->trackChangeDateColumn = $dateColumn;

        return $this;
    }

    /**
     * Handle the resource record "creating" event
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function recordCreating($model)
    {
        $foreignKey = $model->{$this->belongsToRelation}()->getForeignKeyName();

        if ($this->trackChangeDateColumn && ! empty($model->{$foreignKey})) {
            $model->{$this->trackChangeDateColumn} = now();
        }
    }

    /**
     * Handle the resource record "created" event
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function recordCreated($model)
    {
        if ($this->notification && ! static::$skipNotification) {
            $this->handleUserChangedNotification($model);
        }
    }

    /**
     * Handle the resource record "updating" event
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function recordUpdating($model)
    {
        if ($this->trackChangeDateColumn) {
            $date = false;
            $foreignKey = $model->{$this->belongsToRelation}()->getForeignKeyName();

            if (empty($model->{$foreignKey})) {
                $date = null;
            } elseif ($model->getOriginal($foreignKey) !== $model->{$foreignKey}) {
                $date = now();
            }

            if ($date !== false) {
                $model->{$this->trackChangeDateColumn} = $date;
            }
        }
    }

    /**
     * Handle the resource record "updated" event
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function recordUpdated($model)
    {
        if ($this->notification && ! static::$skipNotification) {
            $this->handleUserChangedNotification($model);
        }
    }

    /**
     * Handle the user changed notification
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    protected function handleUserChangedNotification($model)
    {
        /** @var \Turahe\Users\Models\User */
        $assigneer = static::$assigneer ?? Auth::user();

        if ($id = $this->shouldSendNotification($model, $assigneer)) {
            $notification = $this->notification;
            // Retrieve new instance of the user as if we access the relation directly,
            // it may be cached by Laravel and the old user will be returned
            UserModel::find($id)->notify(
                new $notification($model, $assigneer)
            );
        }
    }

    /**
     * Check whether a notification should be sent for the given model and assigneer
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  \Turahe\Users\Models\User|null  $assigneer
     */
    protected function shouldSendNotification($model, $assigneer): int|bool
    {
        // Do not trigger additional queries to retrieve the record assignee
        // when importing data, in all cases, there are no notifications sent during import
        if (Innoclapps::isImportInProgress()) {
            return false;
        }

        if (! $assigneer) {
            return false;
        }

        $foreignKeyName = $model->{$this->belongsToRelation}()->getForeignKeyName();
        $currentId = $model->{$foreignKeyName};

        // Asssigned user not found
        if (! $currentId) {
            return false;
        }

        // Is update and is the same user
        if ((! $model->wasRecentlyCreated && $model->getOriginal($foreignKeyName) === $currentId)) {
            return false;
        }

        // The assigned user is the same as the logged in user
        if ($currentId && $currentId === Auth::id()) {
            return false;
        }

        // We will check if there an assigneer, if not, we won't send the notification
        // as well if the assigneer is the same like the actual user from the field
        if (! ($currentId && $assigneer->getKey() !== $currentId)) {
            return false;
        }

        return $currentId;
    }

    /**
     * Create option for the front-end
     *
     * @param  \Turahe\Users\Models\User  $user
     * @return array
     */
    protected function createOption($user)
    {
        return [
            $this->valueKey => $user->{$this->valueKey},
            $this->labelKey => $user->{$this->labelKey},
            'avatar_url' => $user->avatar_url,
        ];
    }

    /**
     * Get the user import rules
     *
     * @return array
     */
    protected function getUserImportRules()
    {
        return [function (string $attribute, mixed $value, Closure $fail) {
            if (is_null($value)) {
                return;
            }

            if (! $this->getCachedOptionsCollection()->filter(function ($user) use ($value) {
                return $user[$this->valueKey] == $value || $user[$this->labelKey] == $value;
            })->count() > 0) {
                $fail('validation.import.user.invalid')->translate(['attribute' => $this->label]);
            }
        }];
    }
}
