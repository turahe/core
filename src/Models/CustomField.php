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

namespace Turahe\Core\Models;

use Illuminate\Support\Str;
use Turahe\Core\Fields\Field;
use Illuminate\Support\Collection;
use Turahe\Core\Resource\Resource;
use Illuminate\Support\Facades\Lang;
use Turahe\Core\Facades\Innoclapps;
use Turahe\Core\Fields\CustomFieldFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Turahe\Core\Models\CustomField
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Turahe\Core\Models\CustomFieldOption> $options
 * @property-read int|null $options_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField newQuery()
 * @method static Builder|Model orderByNullsLast(string $column, string $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField query()
 * @method static Builder|Model withCommon()
 *
 * @property int $id
 * @property string $resource_name
 * @property string $field_type
 * @property string $field_id
 * @property string $label
 * @property bool|null $is_unique
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereFieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereFieldType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereIsUnique($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereLabel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereResourceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomField whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class CustomField extends Model
{
    use HasUlids;

    /**
     * @var \Turahe\Core\Fields\Field
     */
    protected $instance;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'field_type', 'field_id', 'resource_name', 'label', 'is_unique',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_unique' => 'boolean',
    ];

    /**
     * A custom field has many options
     */
    public function options(): HasMany
    {
        return $this->hasMany(CustomFieldOption::class);
    }

    /**
     * Get the optionable custom field model relation name
     *
     * https://laravel.com/docs/7.x/eloquent-relationships#defining-relationships
     * "Relationship names cannot collide with attribute names as that could lead to your model not being able to know which one to resolve."
     */
    public function relationName(): Attribute
    {
        return Attribute::get(fn () => 'customField'.Str::studly($this->field_id));
    }

    /**
     * Get the instance from the field class
     */
    public function instance(): Field
    {
        if (! $this->instance) {
            $this->instance = CustomFieldFactory::createInstance($this);
        }

        return $this->instance;
    }

    /**
     * Get the field resource instance.
     */
    public function resource(): Resource
    {
        return Innoclapps::resourceByName($this->resource_name);
    }

    /**
     * Check whether the custom field is multi optionable
     */
    public function isMultiOptionable(): bool
    {
        return $this->instance()->isMultiOptionable();
    }

    /**
     * Check whether the custom field is not multi optionable
     */
    public function isNotMultiOptionable(): bool
    {
        return ! $this->isMultiOptionable();
    }

    /**
     * Check whether the custom field is optionable
     */
    public function isOptionable(): bool
    {
        return $this->instance()->isOptionable();
    }

    /**
     * Check whether the custom field is not optionable
     */
    public function isNotOptionable(): bool
    {
        return ! $this->isOptionable();
    }

    /**
     * Prepate the selected options for front-end
     *
     * @param  \Illuminate\Database\Eloquent\Model  $related
     */
    public function prepareRelatedOptions($related): array
    {
        return $this->prepareOptions($related->{$this->relationName});
    }

    /**
     * Check whether the custom field is unique.
     */
    public function isUnique(): bool
    {
        return $this->is_unique === true;
    }

    /**
     * Label attribute accessor
     *
     * Supports translation from language file
     */
    public function label(): Attribute
    {
        return Attribute::get(function (string $value, array $attributes) {
            $customKey = 'custom.custom_field.'.$attributes['field_id'];

            if (Lang::has($customKey)) {
                return __($customKey);
            } elseif (Lang::has($value)) {
                return __($value);
            }

            return $value;
        });
    }

    /**
     * Prepare the options for front-end
     */
    public function prepareOptions(?Collection $options = null): array
    {
        return ($options ?? $this->options)->map(
            fn (CustomFieldOption $option) => [
                'id'           => $option->id,
                'label'        => $option->name,
                'swatch_color' => $option->swatch_color,
            ]
        )->all();
    }
}
