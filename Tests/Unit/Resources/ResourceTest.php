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

namespace Turahe\Core\Tests\Unit\Resources;

use Tests\Fixtures\EventResource;
use Tests\TestCase;
use Turahe\Core\Facades\Innoclapps;

class ResourceTest extends TestCase
{
    public function test_it_can_find_resource_by_model()
    {
        $this->assertNotNull(Innoclapps::resourceByModel(EventResource::$model));
        $this->assertNotNull(Innoclapps::resourceByModel(resolve(EventResource::$model)));
    }

    public function test_it_can_find_globally_searchable_resources()
    {
        $this->assertNotNull(Innoclapps::globallySearchableResources()->first(function ($resource) {
            return $resource->name() === 'events';
        }));
    }
}
