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

namespace Modules\Core\Table;

use Illuminate\Support\Str;

class RelationshipColumn extends Column
{
    /**
     * The relationship name.
     */
    public string $relationName;

    /**
     * The relation field.
     */
    public string $relationField;

    /**
     * The relations to eager load.
     */
    public array $with = [];

    /**
     * Initialize new RelationshipColumn instance.
     */
    public function __construct(string $relation, string $attribute, ?string $label = null)
    {
        // The relation names for front-end are returned in snake case format.
        parent::__construct(Str::snake($relation), $label);

        $this->relationName = $relation;
        $this->relationField = $attribute;
    }

    /**
     * Add relations to eager load for the column relation.
     */
    public function with(array|string $with): static
    {
        $this->with = array_merge(
            $this->with,
            $this->prefixEagerLoadedWithRelationName((array) $with)
        );

        return $this;
    }

    /**
     * Prefix the column eager loaded relationship with the actual relation name.
     */
    protected function prefixEagerLoadedWithRelationName(array $with): array
    {
        foreach ($with as $key => $value) {
            if (is_int($key) && ! str_starts_with($this->relationName, $value)) {
                $with[$key] = $this->relationName.'.'.$value;
            } elseif (! str_starts_with($this->relationName, $key)) {
                unset($with[$key]);
                $with[$this->relationName.'.'.$key] = $value;
            }
        }

        return $with;
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'relationField' => $this->relationField,
        ]);
    }
}
