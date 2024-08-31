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

namespace Turahe\Core\Http\Controllers\Api\Resource;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Turahe\Core\Resource\Resource;
use Turahe\Core\Facades\Innoclapps;
use Turahe\Core\Timeline\Timelineables;
use Turahe\Core\Models\PinnedTimelineSubject;
use Turahe\Core\Http\Controllers\ApiController;
use Turahe\Core\Http\Resources\ChangelogResource;

class TimelineController extends ApiController
{
    /**
     * Get the resource changelog.
     */
    public function index(Request $request, string $resourceName, string $recordId): JsonResponse
    {
        $request->validate(['resources' => 'sometimes|array']);

        $resource = Innoclapps::resourceByName($resourceName);
        $record = $resource->newModel()->findOrFail($recordId);
        $hasChangelog = $record->isRelation('changelog');

        $resources = $this->getResourcesForChangelog($request);

        // When there is no resources included for the changelog and
        // the resource record does not have the changelog relation
        // in this case, 404 error will be shown
        if ($resources->isEmpty()) {
            abort_unless($hasChangelog, 404);
        }

        $this->authorize('view', $record);

        $includeChangelog = $hasChangelog && $resources->contains('changelog');

        $changelog = collect([])->when($includeChangelog, function ($collection) use ($record, $request) {
            ChangelogResource::topLevelResource($record);

            return $this->resolveChangelogJsonResource($record, $request);
        })->when(true, function ($collection) use ($record, $request) {
            $this->resolveResourcesJsonResource($record, $request)
                ->each(function ($data) use ($collection) {
                    $collection->push(...$data);
                });

            return $collection;
        })->sortBy([['is_pinned', 'desc'], ['pinned_date', 'desc'], [function ($record) {
            return $record['timeline_sort_column'];
        }, 'desc']]);

        return $this->response(['data' => $changelog->values()->all()]);
    }

    /**
     * Resolve the changelog JSON resource
     *
     * @param  \Turahe\Core\Models\Model  $record
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Support\Collection
     */
    protected function resolveChangelogJsonResource($record, $request)
    {
        $query = $record->changelog()
            ->select(Resource::prefixColumns($record->changelog()->getModel()))
            ->withPinnedTimelineSubjects($record)
            ->orderBy((new PinnedTimelineSubject)->getQualifiedCreatedAtColumn(), 'desc')
            ->orderBy($record->changelog()->getModel()->getQualifiedCreatedAtColumn(), 'desc');

        return collect(ChangelogResource::collection(
            $query->paginate($request->integer('per_page', 15))
        )->resolve());
    }

    /**
     * Resolve the changelog JSON resource
     *
     * @param  \Turahe\Core\Models\Model  $record
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Support\Collection
     */
    protected function resolveResourcesJsonResource($record, $request)
    {
        return $this->getResourcesForChangelog($request)->map(function (Resource $resource) use ($record, $request) {
            $resource->jsonResource()::topLevelResource($record);

            return $resource->createJsonResource(
                $resource->timelineQuery($record)->paginate($request->integer('per_page', null)),
                true
            );
        });
    }

    /**
     * Get the resources that should be added in the changelog
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Support\Collection
     */
    protected function getResourcesForChangelog($request)
    {
        return collect($request->input('resources', []))->map(function (string $resourceName) {
            return Innoclapps::resourceByName($resourceName);
        })->reject(function (Resource $resource) {
            return ! Timelineables::isTimelineable($resource->newModel());
        })->values();
    }
}
