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

namespace Turahe\Core\Tests\Feature\Media;

use Illuminate\Support\Carbon;
use Tests\TestCase;
use Turahe\Core\Media\PruneStaleMediaAttachments;
use Turahe\Core\Models\Media;

class PruneStaleMediaAttachmentsTest extends TestCase
{
    public function test_it_prunes_stale_media_attachments()
    {
        Carbon::setTestNow(now()->subDay(1)->startOfDay());
        $media = $this->createMedia();

        $pendingMedia = $media->markAsPending('draft-id');

        Carbon::setTestNow(null);

        (new PruneStaleMediaAttachments)();

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        $this->assertDatabaseMissing('pending_media_attachments', ['id' => $pendingMedia->id]);
    }

    protected function createMedia()
    {
        $media = new Media;

        $media->forceFill([
            'disk' => 'local',
            'directory' => 'media',
            'filename' => 'filename',
            'extension' => 'jpg',
            'mime_type' => 'image/jpg',
            'size' => 200,
            'aggregate_type' => 'image',
        ]);

        $media->save();

        return $media;
    }
}
