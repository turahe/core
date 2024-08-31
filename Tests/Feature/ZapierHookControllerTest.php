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

use Tests\TestCase;

class ZapierHookControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_zapier_hooks_endpoints()
    {
        $this->postJson('/api/zapier/hooks/DUMMY_RESOURCE/DUMMY_ACTION')->assertUnauthorized();
        $this->deleteJson('/api/zapier/hooks/DUMMY_ID')->assertUnauthorized();
    }

    public function test_zapier_can_subscribe_to_an_action()
    {
        $user = $this->signIn();

        $this->postJson('/api/zapier/hooks/events/create', [
            'targetUrl' => $url = 'https://wach.id',
            'zapId'     => 123,
            'data'      => ['dummy-data' => 'dummy-value'],
        ])->assertCreated()
            ->assertJson([
                'user_id' => $user->id,
                'hook'    => $url,
                'zap_id'  => 123,
                'data'    => ['dummy-data' => 'dummy-value'],
            ]);
    }

    public function test_zapier_can_unsubscribe_from_an_action()
    {
        $this->signIn();

        $id = $this->postJson('/api/zapier/hooks/events/create', [
            'targetUrl' => 'https://wach.id',
            'zapId'     => 123,
            'data'      => ['dummy-data' => 'dummy-value'],
        ])->getData()->id;

        $this->deleteJson('/api/zapier/hooks/'.$id)->assertNoContent();

        $this->assertDatabaseMissing('zapier_hooks', ['id' => $id]);
    }
}
