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

namespace Turahe\Core\Filters;

use Illuminate\Database\Eloquent\Builder;
use Turahe\Core\Models\Tag;
use Turahe\Core\QueryBuilder\Parser;

class Tags extends Optionable
{
    /**
     * The type the tags are intended for.
     */
    protected ?string $type = null;

    /**
     * @param  string  $field
     * @param  string|null  $label
     * @param  null|array  $operators
     */
    public function __construct($field, $label = null, $operators = null)
    {
        parent::__construct($field, $label, $operators);

        $this->options(function () {
            return Tag::query()->when($this->type, fn (Builder $query) => $query->withType($this->type))
                ->get()
                ->mapWithKeys(fn (Tag $tag) => [
                    $tag->id => $tag->name,
                ]);
        })->query($this->getQuery(...));
    }

    /**
     * Defines a filter type
     */
    public function type(): string
    {
        return 'multi-select';
    }

    /**
     * Add the type the tags are intended for.
     */
    public function forType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the query for the filter.
     */
    protected function getQuery($builder, $value, $condition, $sqlOperator, $rule, Parser $parser)
    {
        return $builder->whereHas(
            'tags',
            function ($query) use ($value, $rule, $sqlOperator, $parser, $condition) {
                $query->when(
                    $this->type,
                    fn (Builder $query) => $query->withType($this->type)
                );

                $rule->query->rule = 'id';

                return $parser->convertToQuery($query, $rule, $value, $sqlOperator['operator'], $condition);
            }
        );
    }
}
