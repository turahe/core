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

namespace Turahe\Core\Tests\Unit\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Tests\Fixtures\Event;
use Turahe\Core\Models\Media;
use Turahe\Core\Tests\TestCase;

class MediaModelTest extends TestCase
{
    public function test_it_can_purge_mediable_media()
    {
        // With array
        $media = $this->createMedia();
        $event = Event::factory()->create();
        $event->attachMedia($media, 'tag');

        $media->purgeByMediableIds(Event::class, [$event->id]);

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        $this->assertCount(0, $event->media);

        // With lazy collection
        $media = $this->createMedia();
        $event = Event::factory()->create();
        $event->attachMedia($media, 'tag');

        $media->purgeByMediableIds(Event::class, new LazyCollection([$event->id]));

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        $this->assertCount(0, $event->media);

        // With regular collection
        $media = $this->createMedia();
        $event = Event::factory()->create();
        $event->attachMedia($media, 'tag');

        $media->purgeByMediableIds(Event::class, new Collection([$event->id]));

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        $this->assertCount(0, $event->media);
    }

    public function test_it_does_not_make_query_when_pruning_if_the_mediable_ids_count_is_zero()
    {
        $this->assertFalse((new Media)->purgeByMediableIds(Event::class, []));
    }

    public function test_it_can_find_media_by_token()
    {
        $media = $this->createMedia();

        $this->assertTrue($media->is(Media::byToken($media->token)->first()));
    }

    protected function createMedia()
    {
        $media = (new Media)->forceFill([
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
