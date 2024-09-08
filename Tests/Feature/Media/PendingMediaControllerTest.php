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

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Turahe\Core\Tests\TestCase;

class PendingMediaControllerTest extends TestCase
{
    public function test_pending_media_can_be_stored()
    {
        $this->signIn();
        config()->set('mediable.allowed_extensions', ['jpg']);
        Storage::fake(config('mediable.default_disk'));

        $this->postJson('/api/media/pending/testDraftId', [
            'file' => UploadedFile::fake()->image('photo1.jpg'),
        ])->assertJson([
            'file_name' => 'photo1.jpg',
            'extension' => 'jpg',
            'disk_path' => 'pending-attachments/photo1.jpg',
            'was_recently_created' => true,
            'pending_data' => ['draft_id' => 'testDraftId'],
        ]);
    }

    public function test_pending_media_can_be_deleted()
    {
        $this->signIn();
        config()->set('mediable.allowed_extensions', ['jpg']);
        Storage::fake(config('mediable.default_disk'));

        $id = $this->postJson('/api/media/pending/testDraftId', [
            'file' => UploadedFile::fake()->image('photo1.jpg'),
        ])->getData()->id;

        $this->deleteJson('/api/media/pending/'.$id)->assertNoContent();
        $this->assertDatabaseCount('pending_media_attachments', 0);
    }
}
