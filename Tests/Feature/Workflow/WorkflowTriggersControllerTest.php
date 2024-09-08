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

namespace Turahe\Core\Tests\Feature\Workflow;

use Turahe\Core\Tests\TestCase;
use Turahe\Activities\Models\ActivityType;
use Turahe\Core\Workflow\Workflows;

class WorkflowTriggersControllerTest extends TestCase
{
    public function test_unauthenticated_cannot_access_workflow_triggers_endpoints()
    {
        $this->getJson('/api/workflows/triggers')->assertUnauthorized();
    }

    public function test_workflow_triggers_can_be_retrieved()
    {
        ActivityType::factory()->create(['flag' => 'task']);

        $this->signIn();

        $this->getJson('/api/workflows/triggers')
            ->assertJsonCount(Workflows::triggersInstance()->count());
    }
}
