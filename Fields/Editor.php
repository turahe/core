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

use Modules\Core\EditorPendingMediaProcessor;
use Modules\Core\Placeholders\GenericPlaceholder;

class Editor extends Field
{
    /**
     * Field component
     */
    public ?string $component = 'editor-field';

    /**
     * Get the mailable template placeholder
     *
     * @param  \Modules\Core\Models\Model|null  $model
     * @return \Modules\Core\Placeholders\GenericPlaceholder
     */
    public function mailableTemplatePlaceholder($model)
    {
        return GenericPlaceholder::make($this->attribute)
            ->description($this->label)
            ->withStartInterpolation('{{{')
            ->withEndInterpolation('}}}')
            ->value(function () use ($model) {
                return $this->resolveForDisplay($model);
            });
    }

    /**
     * Handle the resource record "created" event
     *
     * @param  \Modules\Core\Models\Model  $model
     * @return void
     */
    public function recordCreated($model)
    {
        $this->runImagesProcessor($model);
    }

    /**
     * Handle the resource record "updated" event
     *
     * @param  \Modules\Core\Models\Model  $model
     * @return void
     */
    public function recordUpdated($model)
    {
        $this->runImagesProcessor($model);
    }

    /**
     * Handle the resource record "deleted" event
     *
     * @param  \Modules\Core\Models\Model  $model
     * @return void
     */
    public function recordDeleted($model)
    {
        $this->createImagesProcessor()->deleteAllViaModel(
            $model,
            $this->attribute
        );
    }

    /**
     * Run the editor images processor
     *
     * @param  $this  $model
     * @return void
     */
    protected function runImagesProcessor($model)
    {
        $this->createImagesProcessor()->processViaModel(
            $model,
            $this->attribute
        );
    }

    /**
     * Resolve the field value
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return string
     */
    public function resolve($model)
    {
        return clean(parent::resolve($model));
    }

    /**
     * Create editor images processor
     *
     * @return \Modules\Core\EditorPendingMediaProcessor
     */
    protected function createImagesProcessor()
    {
        return new EditorPendingMediaProcessor();
    }
}
