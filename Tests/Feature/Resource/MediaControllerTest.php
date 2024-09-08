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

namespace Turahe\Core\Tests\Feature\Resource;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Turahe\Contacts\Models\Contact;

class MediaControllerTest extends TestCase
{
    public function test_user_can_upload_media_file_to_resource()
    {
        $this->signIn();
        config()->set('mediable.allowed_extensions', ['jpg']);
        Storage::fake(config('mediable.default_disk'));
        $contact = Contact::factory()->create();

        $this->postJson('/api/contacts/'.$contact->id.'/media', [
            'file' => UploadedFile::fake()->image('photo1.jpg'),
        ])->assertJson([
            'file_name' => 'photo1.jpg',
            'extension' => 'jpg',
            'was_recently_created' => true,
        ]);
    }

    public function test_unauthorized_user_to_update_the_resource_cannot_upload_media_file()
    {
        $this->asRegularUser()->signIn();
        config()->set('mediable.allowed_extensions', ['jpg']);
        Storage::fake(config('mediable.default_disk'));
        $contact = Contact::factory()->create();

        $this->postJson('/api/contacts/'.$contact->id.'/media', [
            'file' => UploadedFile::fake()->image('photo1.jpg'),
        ])->assertForbidden();
    }

    public function test_user_can_delete_media_file_from_resource()
    {
        $this->signIn();
        config()->set('mediable.allowed_extensions', ['jpg']);
        Storage::fake(config('mediable.default_disk'));
        $contact = Contact::factory()->create();

        $id = $this->postJson('/api/contacts/'.$contact->id.'/media', [
            'file' => UploadedFile::fake()->image('photo1.jpg'),
        ])->getData()->id;

        $this->deleteJson('/api/contacts/'.$contact->id.'/media/'.$id)->assertNoContent();
        $this->assertDatabaseCount('media', 0);
    }

    public function test_unauthorized_user_to_update_the_resource_cannot_delete_media_file()
    {
        $this->signIn();
        config()->set('mediable.allowed_extensions', ['jpg']);
        Storage::fake(config('mediable.default_disk'));
        $contact = Contact::factory()->create();

        $id = $this->postJson('/api/contacts/'.$contact->id.'/media', [
            'file' => UploadedFile::fake()->image('photo1.jpg'),
        ])->getData()->id;

        $this->signIn($this->asRegularUser()->createUser());

        $this->deleteJson('/api/contacts/'.$contact->id.'/media/'.$id)->assertForbidden();

        $this->assertDatabaseCount('media', 1);
    }

    public function test_media_cannot_be_uploaded_to_resource_that_does_not_accept_media()
    {
        $this->signIn();

        Storage::fake(config('mediable.default_disk'));

        $this->postJson('/api/fake-resource/fake-id/media', [
            'file' => UploadedFile::fake()->image('photo1.jpg'),
        ])->assertNotFound();
    }
}
