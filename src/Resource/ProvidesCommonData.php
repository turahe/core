<?php
/*
 * This source code is the proprietary and confidential information of
 * Nur Wachid. You may not disclose, copy, distribute,
 *  or use this code without the express written permission of
 * Nur Wachid.
 *
 * Copyright (c) 2023.
 *
 *
 */

namespace Turahe\Core\Resource;

use Illuminate\Support\Collection;
use Turahe\Core\Facades\Innoclapps;
use Turahe\Activities\Models\Activity;
use Turahe\Activities\Contracts\Attendeeable;
use Turahe\Billable\Http\Resources\BillableResource;
use Turahe\Activities\Http\Resources\ActivityResource;
use Turahe\Billable\Contracts\BillableResource as BillableResourceContract;

trait ProvidesCommonData
{
    /**
     * The created follow up task for the resource.
     */
    protected static ?Activity $createdActivity = null;

    /**
     * Add common data to the resource.
     *
     * @param  \Turahe\Core\Resource\Http\ResourceRequest|\Illuminate\Http\Request  $request
     */
    protected function withCommonData(array $data, $request): array
    {
        $data = parent::withCommonData($data, $request);

        $data[] = $this->mergeWhen(! is_null(static::$createdActivity), [
            'createdActivity' => new ActivityResource(static::$createdActivity),
        ]);

        static::withActivity(null);

        if (! $request->isZapier()) {
            $resourceInstance = Innoclapps::resourceByModel($this->resource);

            if ($resourceInstance instanceof BillableResourceContract && $this->relationLoaded('billable')) {
                $data[] = $this->merge([
                    'billable' => new BillableResource($this->billable),
                ]);
            }

            if ($this->resource instanceof Attendeeable) {
                $data[] = $this->merge([
                    'guest_email'        => $this->getGuestEmail(),
                    'guest_display_name' => $this->getGuestDisplayName(),
                ]);
            }

            if ($this->shouldMergeAssociations()) {
                $data[] = $this->merge([
                    'associations' => $this->prepareAssociationsForResponse(),
                ]);
            }
        }

        return $data;
    }

    /**
     * Set the follow up activity which was created to merge in the resource
     */
    public static function withActivity(?Activity $task): void
    {
        static::$createdActivity = $task;
    }

    /**
     * Get the resource associations
     */
    protected function prepareAssociationsForResponse(): Collection
    {
        return collect($this->resource->associatedResources())
            ->map(function ($resourceRecords) {
                return $resourceRecords->map(function ($record, $index) {
                    // Only included needed data for the front-end
                    // if needed via API, users can use the ?with= parameter to load associated resources
                    return [
                        'id'                    => $record->id,
                        'display_name'          => $record->display_name,
                        'path'                  => $record->path,
                        'is_primary_associated' => $index === 0,
                    ];
                });
            });
    }

    /**
     * Check whether a resource has associations and should be merged
     *
     * Associations are merged only if they are previously eager loaded
     */
    protected function shouldMergeAssociations(): bool
    {
        if (! method_exists($this->resource, 'associatedResources')) {
            return false;
        }

        return $this->resource->associationsLoaded();
    }
}
