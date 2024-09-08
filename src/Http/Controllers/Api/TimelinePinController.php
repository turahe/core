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
use Turahe\Core\Http\Controllers\ApiController;
use Turahe\Core\Models\PinnedTimelineSubject;
use Turahe\Core\Timeline\Timeline;

class TimelinePinController extends ApiController
{
    /**
     * Pin the given timelineable to the given resource.
     */
    public function store(Request $request): void
    {
        $data = $this->validateRequest($request);

        (new PinnedTimelineSubject)->pin(...$data);
    }

    /**
     * Unpin the given timelineable to the given resource.
     */
    public function destroy(Request $request): void
    {
        $data = $this->validateRequest($request);

        (new PinnedTimelineSubject)->unpin(...$data);
    }

    /**
     * Validate the request.
     */
    protected function validateRequest(Request $request): array
    {
        $data = $request->validate([
            'subject_id' => 'required|int',
            'subject_type' => 'required|string',
            'timelineable_id' => 'required|int',
            'timelineable_type' => 'required|string',
        ]);

        $subject = Timeline::getPinableSubject($data['subject_type']);
        $timelineable = Timeline::getSubjectAcceptedTimelineable($data['subject_type'], $data['timelineable_type']);

        abort_if(is_null($subject) || is_null($timelineable), 404);

        return [
            $data['subject_id'],
            $subject['subject'],
            $data['timelineable_id'],
            $timelineable['timelineable_type'],
        ];
    }
}
