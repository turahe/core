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

namespace Turahe\Core\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Turahe\Core\Tests\TestCase;

class LogoControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_logo_endpoints()
    {
        $this->postJson('/api/logo/dark')->assertUnauthorized();
        $this->deleteJson('/api/logo/dark')->assertUnauthorized();
        $this->postJson('/api/logo/light')->assertUnauthorized();
        $this->deleteJson('/api/logo/light')->assertUnauthorized();
    }

    public function test_unauthorized_user_cannot_access_logo_endpoints()
    {
        $this->asRegularUser()->signIn();

        $this->postJson('/api/logo/dark')->assertForbidden();
        $this->deleteJson('/api/logo/dark')->assertForbidden();
        $this->postJson('/api/logo/light')->assertForbidden();
        $this->deleteJson('/api/logo/light')->assertForbidden();
    }

    public function test_user_can_upload_logo()
    {
        $this->signIn();

        Storage::fake('public');

        foreach (['light', 'dark'] as $type) {
            $logo = $this->postJson('/api/logo/'.$type, [
                'logo_'.$type => UploadedFile::fake()->image('logo_'.$type.'.jpg'),
            ])->getData()->logo;

            $fileName = basename($logo);

            $this->assertEquals(settings()->get('logo_'.$type), $logo);
            Storage::disk('public')->assertExists($fileName);
        }
    }

    public function test_user_can_delete_logo()
    {
        $this->signIn();

        Storage::fake('public');

        foreach (['light', 'dark'] as $type) {
            $response = $this->postJson('/api/logo/'.$type, [
                'logo_'.$type => UploadedFile::fake()->image('logo_'.$type.'.jpg'),
            ]);

            $fileName = basename($response->getData()->logo);

            $this->deleteJson('/api/logo/'.$type);

            $this->assertEmpty(settings()->get('logo_'.$type));
            Storage::disk('public')->assertMissing($fileName);
        }
    }
}
