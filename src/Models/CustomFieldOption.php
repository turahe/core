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

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Lang;
use Turahe\Core\Concerns\HasDisplayOrder;

/**
 * Turahe\Core\Models\CustomFieldOption
 *
 * @property-read \Turahe\Core\Models\CustomField|null $field
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFieldOption newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFieldOption newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFieldOption orderByDisplayOrder()
 * @method static Builder|Model orderByNullsLast(string $column, string $direction = 'asc')
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFieldOption query()
 * @method static Builder|Model withCommon()
 *
 * @property int $id
 * @property int $custom_field_id
 * @property string $name
 * @property string|null $swatch_color
 * @property int $display_order
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFieldOption whereCustomFieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFieldOption whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFieldOption whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFieldOption whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CustomFieldOption whereSwatchColor($value)
 *
 * @mixin \Eloquent
 */
class CustomFieldOption extends Model
{
    use HasDisplayOrder;
    use HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'display_order',
        'swatch_color',
    ];

    /**
     * Indicates if the model has timestamps
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'custom_field_id' => 'int',
        'display_order' => 'int',
    ];

    /**
     * A custom field option belongs to custom field
     */
    public function field(): BelongsTo
    {
        return $this->belongsTo(CustomField::class, 'custom_field_id');
    }

    /**
     * Name attribute accessor
     *
     * Supports translation from language file
     */
    public function name(): Attribute
    {
        return Attribute::get(function (string $value, array $attributes) {
            if (! array_key_exists('id', $attributes)) {
                return $value;
            }

            $customKey = 'custom.custom_field.options.'.$attributes['id'];

            if (Lang::has($customKey)) {
                return __($customKey);
            } elseif (Lang::has($value)) {
                return __($value);
            }

            return $value;
        });
    }

    /**
     * Determine if the model touches a given relation.
     * The custom field option touches all parent models
     *
     * For example, when record that is using custom field with options is updated
     * we need to update the record updated_at column.
     *
     * In this case, tha parent must use timestamps too.
     *
     * @param  string  $relation
     * @return bool
     */
    public function touches($relation)
    {
        return true;
    }
}
