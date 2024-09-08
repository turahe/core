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

namespace Turahe\Core\Fields;

use Exception;
use Turahe\Core\Table\Column;

class IntroductionField extends Field
{
    /**
     * Field component
     */
    public ?string $component = 'introduction-field';

    /**
     * Field title icon
     */
    public ?string $titleIcon = null;

    /**
     * Initialize new IntroductionField
     */
    public function __construct(public string $title, public ?string $message = null)
    {
        $this->excludeFromImport();
        $this->excludeFromExport();
        $this->excludeFromSettings();
        $this->excludeFromIndex();
    }

    /**
     * Set field title
     *
     *
     * @return static
     */
    public function title(string $title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Set field message
     *
     *
     * @return static
     */
    public function message(?string $message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Set field title icon
     *
     *
     * @return static
     */
    public function titleIcon(?string $icon)
    {
        $this->titleIcon = $icon;

        return $this;
    }

    /**
     * Get the mailable template placeholder
     *
     * @param  \Turahe\Core\Models\Model  $model
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
     * Resolve the actual field value
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return mixed
     */
    public function resolve($model)
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
     * @param  \Turahe\Core\Models\Model  $model
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

    /**
     * Resolve the field value for JSON Resource
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return null
     */
    public function resolveForJsonResource($model)
    {
        return null;
    }

    /**
     * Add custom value resolver
     *
     *
     * @return static
     */
    public function resolveUsing(callable $resolveCallback)
    {
        throw new Exception(__CLASS__.' cannot have custom resolve callback');
    }

    /**
     * Add custom display resolver
     *
     *
     * @return static
     */
    public function displayUsing(callable $displayCallback)
    {
        throw new Exception(__CLASS__.' cannot have custom display callback');
    }

    /**
     * Add custom import value resolver
     *
     *
     * @return static
     */
    public function importUsing(callable $importCallback)
    {
        throw new Exception(__CLASS__.' cannot be used in imports');
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'title' => $this->title,
            'message' => $this->message,
            'titleIcon' => $this->titleIcon,
        ]);
    }
}
