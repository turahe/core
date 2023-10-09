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

namespace Modules\Core\Http\Controllers\Api\Resource;

use Modules\Core\Models\Import;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Http\Resources\ImportResource;
use Modules\Core\Resource\Http\ResourceRequest;
use Modules\Core\Contracts\Resources\Importable;
use Modules\Core\Http\Controllers\ApiController;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportSkipFileController extends ApiController
{
    /**
     * Upload the fixed skip file and start mapping.
     */
    public function upload(ResourceRequest $request): JsonResponse
    {
        abort_unless($request->resource() instanceof Importable, 404);

        $import = Import::findOrFail($request->route('id'));

        abort_if(is_null($import->skip_file_path), 404);

        $this->authorize('uploadFixedSkipFile', $import);

        $request->validate(['skip_file' => 'required|mimes:csv,txt']);

        $request->resource()
            ->importable()
            ->uploadViaSkipFile(
                $request->file('skip_file'),
                $import
            );

        $import->loadMissing('user');

        return $this->response(new ImportResource($import));
    }

    /**
     * Download the skip file for the import.
     */
    public function download(ResourceRequest $request): StreamedResponse
    {
        abort_unless($request->resource() instanceof Importable, 404);

        $import = Import::findOrFail($request->route('id'));

        abort_if(is_null($import->skip_file_path), 404);

        $this->authorize('downloadSkipFile', $import);

        return Storage::disk($import::disk())->download(
            $import->skip_file_path,
            $filename = basename($import->skip_file_path),
            [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename='.$filename,
                'charset'             => 'utf-8',
            ]
        );
    }
}
