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

use Modules\Core\Table\Column;

class VisibilityGroup extends Field
{
    /**
     * Field component
     */
    public ?string $component = 'visibility-group-field';

    /**
     * Initialize new VisibilityGroup instance class
     */
    public function __construct()
    {
        parent::__construct(...func_get_args());

        $this->rules(['nullable', 'array'])
            ->excludeFromZapierResponse()
            ->strictlyForForms()
            ->excludeFromDetail()
            ->excludeFromImport()
            ->excludeFromImportSample()
            ->excludeFromSettings();
    }

    /**
     * Provides the relationships that should be eager loaded when quering resource data
     */
    public function withRelationships(): array
    {
        return ['visibilityGroup.organizations', 'visibilityGroup.users'];
    }

    /**
     * Resolve the field value for JSON Resource
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return array|null
     */
    public function resolveForJsonResource($model)
    {
        if (! $model->relationLoaded('visibilityGroup')) {
            return null;
        }

        return [$this->attribute => $model->visibilityGroupData()];
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
     * Provide the column used for index
     *
     * @return null
     */
    public function indexColumn(): ?Column
    {
        return null;
    }

    /**
     * Resolve the displayable field value
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return null
     */
    public function resolveForDisplay($model)
    {
        return null;
    }

    /**
     * Resolve the field value for export
     *
     * @param  \Modules\Core\Models\Model  $model
     * @return null
     */
    public function resolveForExport($model)
    {
        return null;
    }

    /**
     * Resolve the field value for import
     *
     * @param  string|null  $value
     * @param  array  $row
     * @param  array  $original
     * @return null
     */
    public function resolveForImport($value, $row, $original)
    {
        return null;
    }
}
