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

namespace Turahe\Core\Resource;

use Illuminate\Database\Eloquent\Builder;
use Turahe\Core\Export\ExportViaFields;
use Turahe\Core\Fields\FieldsCollection;

class Export extends ExportViaFields
{
    /**
     * Export chunk size.
     */
    public static int $chunkSize = 500;

    /**
     * Create new Export instance.
     */
    public function __construct(protected Resource $resource, protected Builder $query) {}

    /**
     * Provides the export data.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        [$with, $withCount] = $this->resource->getEagerLoadable($this->fields());

        return $this->query->withCount($withCount->all())
            ->with($with->all())
            ->lazy(static::$chunkSize);
    }

    /**
     * Provides the resource available fields.
     */
    public function fields(): FieldsCollection
    {
        return $this->resource->resolveFields();
    }

    /**
     * The export file name (without extension)
     */
    public function fileName(): string
    {
        return $this->resource->name();
    }
}
