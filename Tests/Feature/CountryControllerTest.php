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

namespace Modules\Core\Tests\Feature;

use Tests\TestCase;
use Modules\Core\Database\Seeders\CountriesSeeder;

class CountryControllerTest extends TestCase
{
    public function test_unauthenticated_cannot_access_country_endpoints()
    {
        $this->getJson('/api/countries')->assertUnauthorized();
    }

    public function test_user_can_fetch_countries()
    {
        $this->signIn();

        $this->seed(CountriesSeeder::class);

        $this->getJson('/api/countries')->assertOk();
    }
}
