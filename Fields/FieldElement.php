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

use Modules\Core\Authorizeable;
use Modules\Core\MetableElement;

class FieldElement
{
    use Authorizeable, MetableElement;

    /**
     * Indicates whether this field is not hidden on index view
     */
    public bool $showOnIndex = true;

    /**
     * @var bool|callable
     */
    public $applicableForIndex = true;

    /**
     * Indicates whether this field is not hidden on create view
     */
    public bool $showOnCreation = true;

    /**
     * @var bool|callable
     */
    public $applicableForCreation = true;

    /**
     * Indicates whether this field is not hidden on update view
     */
    public bool $showOnUpdate = true;

    /**
     * @var bool|callable
     */
    public $applicableForUpdate = true;

    /**
     * Indicates whether this field is not hidden on detail view
     */
    public bool $showOnDetail = true;

    /**
     * @var bool|callable
     */
    public $applicableForDetail = true;

    /**
     * Indicates whether this field is excluded from setttings
     */
    public bool|string $excludeFromSettings = false;

    /**
     * Indicates whether to exclude the field from import
     */
    public bool $excludeFromImport = false;

    /**
     * Indicates whether the field should be included in sample data
     */
    public bool $excludeFromImportSample = false;

    /**
     * Indicates whether to exclude the field from export
     */
    public bool $excludeFromExport = false;

    /**
     * Set that the field by default should be hidden on index view
     */
    public function hideFromIndex(): static
    {
        $this->showOnIndex = false;

        return $this;
    }

    /**
     * The field is only for index and cannot be used on other views
     */
    public function strictlyForIndex(): static
    {
        $this->excludeFromSettings = true;
        $this->applicableForIndex = true;
        $this->applicableForCreation = false;
        $this->applicableForUpdate = false;
        $this->applicableForDetail = false;

        return $this;
    }

    /**
     * Set that the field by default should be hidden on create view
     */
    public function hideWhenCreating(): static
    {
        $this->showOnCreation = false;

        return $this;
    }

    /**
     * The field is only for creation and cannot be used on other views
     */
    public function strictlyForCreation(): static
    {
        $this->applicableForCreation = true;
        $this->applicableForUpdate = false;
        $this->applicableForDetail = false;
        $this->applicableForIndex = false;

        return $this;
    }

    /**
     * The field is only for creation and index and cannot be used on other views
     */
    public function strictlyForCreationAndIndex(): static
    {
        $this->applicableForCreation = true;
        $this->applicableForIndex = true;
        $this->applicableForUpdate = false;
        $this->applicableForDetail = false;

        return $this;
    }

    /**
     * Set that the field by default should be hidden on update view
     */
    public function hideWhenUpdating(): static
    {
        $this->showOnUpdate = false;

        return $this;
    }

    /**
     * The field is only for update and cannot be used on other views
     */
    public function strictlyForUpdate(): static
    {
        $this->applicableForUpdate = true;
        $this->applicableForCreation = false;
        $this->applicableForIndex = false;
        $this->applicableForDetail = false;

        return $this;
    }

    /**
     * Set that the field by default should be hidden on detail view
     */
    public function hideFromDetail(): static
    {
        $this->showOnDetail = false;

        return $this;
    }

    /**
     * The field is only for detail and cannot be used on other views
     */
    public function strictlyForDetail(): static
    {
        $this->applicableForDetail = true;
        $this->applicableForUpdate = false;
        $this->applicableForCreation = false;
        $this->applicableForIndex = false;

        return $this;
    }

    /**
     * The field is only for import and cannot be used on other views
     */
    public function strictlyForImport(): static
    {
        $this->applicableForUpdate = false;
        $this->applicableForDetail = false;
        $this->applicableForCreation = false;
        $this->applicableForIndex = false;
        $this->excludeFromExport = true;
        $this->excludeFromSettings = true;
        $this->excludeFromImport = false;

        return $this;
    }

    /**
     * The field is only for forms and cannot be used on other views
     */
    public function strictlyForForms(): static
    {
        $this->applicableForUpdate = true;
        $this->applicableForDetail = true;
        $this->applicableForCreation = true;
        $this->applicableForIndex = false;

        return $this;
    }

    /**
     * The field is only usable on views different then forms
     */
    public function exceptOnForms(bool|callable $applicable = false): static
    {
        $this->applicableForUpdate = $applicable;
        $this->applicableForDetail = $applicable;
        $this->applicableForCreation = $applicable;
        $this->applicableForIndex = true;
        $this->excludeFromImport = true;
        $this->excludeFromSettings = true;

        return $this;
    }

    /**
     * Set that the field is excluded from index view
     */
    public function excludeFromIndex(bool|callable $applicable = false): static
    {
        $this->applicableForIndex = $applicable;

        return $this;
    }

    /**
     * Set if the field is excluded from create view
     */
    public function excludeFromCreate(bool|callable $applicable = false): static
    {
        $this->applicableForCreation = $applicable;

        return $this;
    }

    /**
     * Set if the field is excluded from update view
     */
    public function excludeFromUpdate(bool|callable $applicable = false): static
    {
        $this->applicableForUpdate = $applicable;

        return $this;
    }

    /**
     * Set if the field is excluded from detail view
     */
    public function excludeFromDetail(bool|callable $applicable = false): static
    {
        $this->applicableForDetail = $applicable;

        return $this;
    }

    /**
     * Indicates that this field should be excluded from the settings
     */
    public function excludeFromSettings(string|bool $view = true): static
    {
        $this->excludeFromSettings = $view;

        return $this;
    }

    /**
     * Set that the field should be excluded from export
     */
    public function excludeFromExport(): static
    {
        $this->excludeFromExport = true;

        return $this;
    }

    /**
     * Set that the field should be excluded from import
     */
    public function excludeFromImport(): static
    {
        $this->excludeFromImport = true;

        return $this;
    }

    /**
     * Set that the field should should not be included in sample data
     */
    public function excludeFromImportSample(): static
    {
        $this->excludeFromImportSample = true;

        return $this;
    }

    /**
     * Indicates that the field by default should be hidden on all views.
     */
    public function hidden(): static
    {
        $this->hideWhenUpdating();
        $this->hideWhenCreating();
        $this->hideFromIndex();
        $this->hideFromDetail();

        return $this;
    }

    /**
     * Determine if the field is applicable for creation view
     */
    public function isApplicableForCreation(): bool
    {
        return $this->whenTruthful($this->applicableForCreation);
    }

    /**
     * Determine if the field is applicable for update view
     */
    public function isApplicableForUpdate(): bool
    {
        return $this->whenTruthful($this->applicableForUpdate);
    }

    /**
     * Determine if the field is applicable for detail view
     */
    public function isApplicableForDetail(): bool
    {
        return $this->whenTruthful($this->applicableForDetail);
    }

    /**
     * Determine if the field is applicable for index view
     */
    public function isApplicableForIndex(): bool
    {
        return $this->whenTruthful($this->applicableForIndex);
    }

    /**
     * Check whether the given value is truthful
     *
     * @param  bool|callable  $callback
     */
    private function whenTruthful($callback): bool
    {
        return $callback === true || (is_callable($callback) && call_user_func($callback));
    }
}
