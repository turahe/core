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

namespace Modules\Core\Resource;

use Modules\Core\Fields\Field;
use Modules\Core\Models\Model;
use Modules\Core\Facades\Innoclapps;
use Modules\Core\Placeholders\Placeholder;
use Modules\Core\Placeholders\GenericPlaceholder;

class PlaceholdersGroup
{
    protected Resource $resource;

    /**
     * Initialize new PlaceholdersGroup instance.
     *
     * @param  \Modules\Core\Models\Model|null  $model Provide the model when parsing is needed
     */
    public function __construct(Resource|string $resource, protected ?Model $model = null)
    {
        $this->resource = is_string($resource) ? Innoclapps::resourceByName($resource) : $resource;
    }

    /**
     * Get the all of the resource placeholders.
     */
    public function all(): array
    {
        return $this->resource->resolveFields()->map(function (Field $field) {
            $placeholder = $field->mailableTemplatePlaceholder($this->model);

            if ($placeholder instanceof Placeholder) {
                return $placeholder;
            } elseif (is_string($placeholder)) { // Allow pass value directly without providing placeholder
                return GenericPlaceholder::make($field->attribute)
                    ->description($field->label)
                    ->value($placeholder);
            }
        })
            ->filter()
            ->each(function (Placeholder $placeholder) {
                $placeholder->prefixTag($this->tagPrefix());
            })
            ->values()
            ->all();
    }

    /**
     * Get the model for the placeholders.
     */
    public function getModel(): ?Model
    {
        return $this->model;
    }

    /**
     * Get the group resource instance.
     */
    public function getResource(): Resource
    {
        return $this->resource;
    }

    /**
     * Get the placeholders tag prefix.
     */
    public function tagPrefix(): string
    {
        return $this->resource->singularName().'.';
    }
}
