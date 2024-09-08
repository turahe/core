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

namespace Turahe\Core\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Turahe\Core\Http\Controllers\ApiController;
use Turahe\Core\LogReader;
use Turahe\Core\SystemInfo;

class SystemController extends ApiController
{
    /**
     * Get the system info
     */
    public function info(Request $request): JsonResponse
    {
        // System info flag

        return $this->response(new SystemInfo($request));
    }

    /**
     * Download the system info
     */
    public function downloadInfo(Request $request): BinaryFileResponse
    {
        // System info download flag

        return Excel::download(new SystemInfo($request), 'system-info.xlsx');
    }

    /**
     * Get the application/Laravel logs
     */
    public function logs(Request $request): JsonResponse
    {
        // System logs flag

        return $this->response(
            new LogReader(['date' => $request->date])
        );
    }
}
