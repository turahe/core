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

namespace Modules\Core\Fields;

use Modules\Core\Table\MorphToManyColumn;
use Modules\Core\Http\Resources\TagResource;
use Modules\Core\Resource\Http\ResourceRequest;

class Tags extends Field
{
    /**
     * The type the tags are intended for.
     */
    protected ?string $type = null;

    /**
     * Field component
     */
    public ?string $component = 'tags-field';

    /**
     * Initialize new Tags instance.
     */
    public function __construct($attribute, $label = null)
    {
        parent::__construct($attribute, $label);

        $this->withDefaultValue([])
            ->provideImportValueSampleUsing(fn () => 'Tag1, Tag 2')
            ->saveUsing(function (ResourceRequest $request, string $attribute, array|string $value) {
                return function ($model) use ($value) {
                    if ($this->type) {
                        $model->syncTagsWithType($value, $this->type);
                    } else {
                        $model->syncTags($value);
                    }
                };
            });
    }

    /**
     * Resolve the value for JSON Resource
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return array|null
     */
    public function resolveForJsonResource($model)
    {
        if ($this->shouldResolveForJson($model)) {
            return [
                $this->attribute => TagResource::collection($this->resolve($model)),
            ];
        }
    }

    /**
     * Resolve the field value for import
     *
     * @param  string|null  $value
     * @param  array  $row
     * @param  array  $original
     * @return array|null
     */
    public function resolveForImport($value, $row, $original)
    {
        if (is_string($value)) {
            $value = explode(',', $value);
            $value = array_map('trim', $value);
        }

        return [$this->attribute => $value];
    }

    /**
     * Provides the relationships that should be eager loaded when quering resource data
     */
    public function withRelationships(): array
    {
        return ['tags'];
    }

    /**
     * Check whether the fields values should be resolved for JSON resource
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return bool
     */
    protected function shouldResolveForJson($model)
    {
        return $model->relationLoaded('tags');
    }

    /**
     * Provide the column used for index
     */
    public function indexColumn(): MorphToManyColumn
    {
        return tap(new MorphToManyColumn(
            'tags',
            'name',
            $this->label,
        ), function ($column) {
            $column->select(['type', 'display_order', 'swatch_color'])->useComponent('table-data-option-column');
        });
    }

    /**
     * Get the mailable template placeholder
     *
     * @param  \Modules\Core\Models\Model  $model
     * @return null
     */
    public function mailableTemplatePlaceholder($model)
    {
        return null;
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
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'type' => $this->type,
        ]);
    }
}
