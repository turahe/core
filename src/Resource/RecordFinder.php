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

namespace Turahe\Core\Resource;

use Turahe\Core\Models\Model;
use Illuminate\Support\LazyCollection;

class RecordFinder
{
    protected ?LazyCollection $records = null;

    protected int $limit = 500;

    protected array|string $with = [];

    protected bool $withoutTrashed = false;

    /**
     * Initialize new RecordFinder instance.
     */
    public function __construct(protected Model $model)
    {
    }

    /**
     * Match all of the given values
     */
    public function matchAll(array $attributes): ?Model
    {
        $this->createCollection();

        if (collect($attributes)->filter(fn ($value) => ! $value)->isNotEmpty()) {
            return null;
        }

        foreach ($this->records as $record) {
            $matches = 0;

            foreach ($attributes as $attribute => $value) {
                if (strcasecmp($record->{$attribute}, $value) === 0) {
                    $matches++;
                }
            }

            if ($matches === count($attributes)) {
                return $record;
            }
        }

        return null;
    }

    /**
     * Match any of the given values
     */
    public function match(array $attributes): ?Model
    {
        $this->createCollection();

        foreach ($this->records as $record) {
            foreach ($attributes as $attribute => $value) {
                if ($value && strcasecmp($record->{$attribute}, $value) === 0) {
                    return $record;
                }
            }
        }

        return null;
    }

    /**
     * Limit the number of rows loaded via the lazy method
     */
    public function limit(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Add the relationships to lazy load
     */
    public function with(array|string $relationships): static
    {
        $this->with = $relationships;

        return $this;
    }

    /**
     * Remove the trashed criteria
     */
    public function withoutTrashed(): static
    {
        $this->withoutTrashed = true;

        return $this;
    }

    /**
     * Create lazy collection for the finder
     */
    protected function createCollection(): void
    {
        if (is_null($this->records)) {
            $query = $this->model->with($this->with);

            if (! $this->withoutTrashed) {
                $query->withTrashed();
            }

            $this->records = $query->lazy($this->limit)->remember();
        }
    }

    /**
     * For when resource is serialized in a Workflow action
     *
     * @return array
     */
    public function __sleep()
    {
        return ['limit', 'with'];
    }
}
