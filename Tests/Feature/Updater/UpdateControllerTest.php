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

namespace Turahe\Core\Tests\Feature\Updater;

use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\App;
use Tests\TestCase;
use Turahe\Core\Updater\Updater;

/**
 * @group updater
 */
class UpdateControllerTest extends TestCase
{
    use TestsUpdater;

    public function test_unauthenticated_user_cannot_access_update_endpoints()
    {
        $this->getJson('api/update')->assertUnauthorized();
        $this->getJson('api/update/FAKE_KEY')->assertUnauthorized();
    }

    public function test_unauthorized_user_cannot_access_update_endpoints()
    {
        $this->asRegularUser()->signIn();

        $this->getJson('api/update')->assertForbidden();
        $this->postJson('api/update/FAKE_KEY')->assertForbidden();
    }

    public function test_update_information_can_be_retrieved()
    {
        $this->signIn();

        App::singleton(Updater::class, function () {
            return $this->createUpdaterInstance([
                new Response(200, [], $this->archiveResponse()),
            ], ['version_installed' => '1.1.0']);
        });

        $this->getJson('/api/update')->assertExactJson([
            'installed_version' => '1.1.0',
            'is_new_version_available' => false,
            'latest_available_version' => '1.1.0',
            'purchase_key' => config('updater.purchase_key'),
        ]);
    }
}
