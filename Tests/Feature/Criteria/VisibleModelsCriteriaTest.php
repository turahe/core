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

namespace Turahe\Core\Tests\Feature\Criteria;

use Turahe\Core\Tests\TestCase;
use Turahe\Core\Criteria\VisibleModelsCriteria;
use Turahe\Core\Models\ModelVisibilityGroup;
use Turahe\Users\Models\Organization;
use Turahe\Users\Models\User;

class VisibleModelsCriteriaTest extends TestCase
{
    public function test_visible_pipelines_criteria()
    {
        $user = User::factory()->has(Organization::factory())->create();

        Pipeline::factory()
            ->has(
                ModelVisibilityGroup::factory()->organizations->hasAttached($user->organizations->first()),
                'visibilityGroup'
            )
            ->create();

        $this->assertSame(1, Pipeline::criteria(new VisibleModelsCriteria($user))->count());
    }
}
