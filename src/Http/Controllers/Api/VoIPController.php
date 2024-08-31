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

use Illuminate\Http\Request;
use Turahe\Core\Facades\VoIP;
use Illuminate\Http\JsonResponse;
use Turahe\Core\Http\Controllers\ApiController;
use Turahe\Core\VoIP\Events\IncomingCallMissed;

class VoIPController extends ApiController
{
    /**
     * Call events
     *
     * @return mixed
     */
    public function events(Request $request)
    {
        VoIP::validateRequest($request);

        $call = VoIP::getCall($request);

        if ($call->isMissedIncoming()) {
            event(new IncomingCallMissed($call));
        }

        return VoIP::events($request);
    }

    /**
     * Initiate new call
     *
     * @return mixed
     */
    public function newCall(Request $request)
    {
        VoIP::validateRequest($request);

        if (VoIP::shouldReceivesEvents()) {
            VoIP::setEventsUrl(VoIP::eventsUrl());
        }

        if ($request->boolean('viaApp')) {
            return VoIP::newOutgoingCall(
                $request->input('To'),
                $request
            );
        }

        return VoIP::newIncomingCall($request);
    }

    /**
     * Create a new client token.
     */
    public function newToken(Request $request): JsonResponse
    {
        return $this->response(['token' => VoIP::newToken($request)]);
    }
}
