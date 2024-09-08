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
use Turahe\Core\Http\Controllers\ApiController;
use Turahe\Core\Http\Resources\MailableResource;
use Turahe\Core\Models\MailableTemplate;

class MailableController extends ApiController
{
    /**
     * Retrieve all mail templates.
     */
    public function index(): JsonResponse
    {
        $collection = MailableResource::collection(MailableTemplate::orderBy('name')->get());

        return $this->response($collection);
    }

    /**
     * Retrieve mail templates in specific locale.
     */
    public function forLocale(string $locale): JsonResponse
    {
        $collection = MailableResource::collection(
            MailableTemplate::orderBy('name')->forLocale($locale)->get()
        );

        return $this->response($collection);
    }

    /**
     * Display the specified resource.
     */
    public function show(MailableTemplate $template): JsonResponse
    {
        return $this->response(new MailableResource($template));
    }

    /**
     * Update the specified mail template in storage.
     */
    public function update(MailableTemplate $template, Request $request): JsonResponse
    {
        $request->validate([
            'subject' => 'required|string|max:191',
            'html_template' => 'required|string',
        ]);

        $template->fill($request->all())->save();

        return $this->response(new MailableResource($template));
    }
}
