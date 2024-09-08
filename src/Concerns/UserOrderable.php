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

namespace Turahe\Core\Concerns;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Turahe\Core\Models\UserOrderedModel;
use Turahe\Users\Models\User;

/** @mixin \Turahe\Core\Models\Model */
trait UserOrderable
{
    /**
     * Boot the trait.
     */
    protected static function bootUserOrderable(): void
    {
        static::deleting(function ($model) {
            if (! $model->usesSoftDeletes() || $model->isForceDeleting()) {
                $model->userOrder?->delete();
            }
        });
    }

    /**
     * Save the display order for the model for the given user.
     */
    public function saveUserDisplayOrder(User|int $user, int $displayOrder): static
    {
        if (! is_null($this->userOrder)) {
            $this->userOrder->update(['display_order' => $displayOrder]);
        } else {
            $this->userOrder()->save(
                new UserOrderedModel([
                    'display_order' => $displayOrder,
                    'user_id' => is_int($user) ? $user : $user->id,
                ])
            );
        }

        return $this;
    }

    /**
     * Get the current user model order
     */
    public function userOrder(null|User|int $user = null): MorphOne
    {
        $userId = is_null($user) ? Auth::id() : (is_int($user) ? $user : $user->id);

        return $this->morphOne(UserOrderedModel::class, 'orderable')->where('user_id', $userId);
    }

    /**
     * Apply a scope query to order the records as the user specified.
     */
    public function scopeUserOrdered(Builder $query, null|User|int $user = null): void
    {
        $userId = is_null($user) ? Auth::id() : (is_int($user) ? $user : $user->id);

        $table = (new UserOrderedModel)->getTable();

        $query->select($this->prepareColumnsForUserOrderedQuery($query))
            ->leftJoin($table, function ($join) use ($userId, $query, $table) {
                $orderableModel = $query->getModel();

                $join->on($table.'.orderable_id', '=', $orderableModel->getTable().'.'.$orderableModel->getKeyName())
                    ->where($table.'.orderable_type', $orderableModel::class)
                    ->where($table.'.user_id', $userId);
            })
            ->orderBy($table.'.display_order', 'asc');
    }

    /**
     * Qualify the columns to avoid ambigious columns when joining.
     */
    protected function prepareColumnsForUserOrderedQuery(Builder $builder): array|string
    {
        $columns = $builder->getQuery()->columns;

        if (is_null($columns)) {
            return $builder->getModel()->getTable().'.*';
        }

        return collect($columns)->map(function ($column) use ($builder) {
            if (! Str::endsWith($column, '.*') && ! $column instanceof Expression) {
                return $builder->getModel()->qualifyColumn($column);
            }

            return $column;
        })->all();
    }
}
