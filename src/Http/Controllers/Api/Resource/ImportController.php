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

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Turahe\Core\Contracts\Resources\Importable;
use Turahe\Core\Http\Controllers\ApiController;
use Turahe\Core\Http\Resources\ImportResource;
use Turahe\Core\Models\Import;
use Turahe\Core\Resource\Http\ResourceRequest;
use Turahe\Core\Resource\Import\RowsExceededException;

class ImportController extends ApiController
{
    /**
     * Get the import files in storage for the resource.
     */
    public function index(ResourceRequest $request): AnonymousResourceCollection
    {
        abort_unless($request->resource() instanceof Importable, 404);

        return ImportResource::collection(
            Import::with('user')->byResource($request->resource()->name())->latest()->get()
        );
    }

    /**
     * Perform import for the current resource.
     */
    public function handle(ResourceRequest $request): JsonResponse
    {
        $this->increasePhpIniValues();

        abort_unless($request->resource() instanceof Importable, 404);

        $import = Import::findOrFail($request->route('id'));

        $request->validate([
            'mappings' => 'required|array',
            'mappings.*.attribute' => 'nullable|distinct|string',
            'mappings.*.auto_detected' => 'required|boolean',
            'mappings.*.original' => 'required|string',
            'mappings.*.skip' => 'required|boolean',
            'mappings.*.detected_attribute' => 'present',
        ]);

        // Update with the user provided mappings
        $import->fill([
            'data' => array_merge($import->data ?? [], [
                'mappings' => $request->mappings,
            ]),
        ]);

        $import->save();

        try {
            $request->resource()->importable()->perform($import);

            return $this->response(new ImportResource($import->loadMissing('user')));
        } catch (Exception|RowsExceededException $e) {
            if ($e instanceof RowsExceededException) {
                $deleted = $import->delete();
            }

            return $this->response([
                'message' => $e->getMessage(),
                'deleted' => $deleted ?? false,
                'rows_exceeded' => $e instanceof RowsExceededException,
            ], 500);
        }
    }

    /**
     * Initiate new import and start mapping.
     */
    public function upload(ResourceRequest $request): JsonResponse
    {
        abort_unless($request->resource() instanceof Importable, 404);

        $request->validate(['file' => 'required|mimes:csv,txt']);

        $import = $request->resource()
            ->importable()
            ->upload(
                $request->file('file'),
                $request->user()
            );

        return $this->response(new ImportResource($import->loadMissing('user')));
    }

    /**
     * Download sample import file.
     */
    public function sample(ResourceRequest $request): BinaryFileResponse
    {
        abort_unless($request->resource() instanceof Importable, 404);

        return $request->resource()->importSample()->download();
    }

    /**
     * Delete the given import.
     */
    public function destroy(ResourceRequest $request): JsonResponse
    {
        abort_unless($request->resource() instanceof Importable, 404);

        $import = Import::findOrFail($request->route('id'));

        $this->authorize('delete', $import);

        $import->delete();

        return $this->response('', 204);
    }

    protected function increasePhpIniValues(): void
    {
        if (! app()->runningUnitTests()) {
            \DetachedHelper::raiseMemoryLimit('256M');
            @ini_set('max_execution_time', 300);
        }
    }
}
