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

use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\TestCase;
use Turahe\Contacts\Models\Contact;
use Turahe\Contacts\Resource\Contact\Contact as ContactResource;
use Turahe\Core\Database\Seeders\PermissionsSeeder;

class SearchControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_the_resource_search_endpoints()
    {
        $this->json('GET', '/api/contacts/search')->assertUnauthorized();
    }

    public function test_non_searchable_resource_cannot_be_searched()
    {
        $this->signIn();

        $model = ContactResource::newModel();

        $searchableColumns = $model->getSearchableColumns();

        $model->setSearchableColumns([]);

        $this->json('GET', '/api/contacts/search?q=test')
            ->assertNotFound();

        $model->setSearchableColumns($searchableColumns);
    }

    public function test_own_criteria_is_applied_on_resource_search()
    {
        $this->seed(PermissionsSeeder::class);

        $user = $this->asRegularUser()->withPermissionsTo('view own contacts')->signIn();

        Contact::factory()->count(2)->state(new Sequence(
            ['first_name' => 'John', 'user_id' => $user->getKey()],
            ['first_name' => 'John', 'user_id' => null]
        ))->create();

        $this->getJson('/api/contacts/search?q=john')
            ->assertJsonCount(1);
    }

    public function test_it_does_not_return_any_results_if_search_query_is_empty()
    {
        $this->signIn();

        Contact::factory()->create();

        $this->json('GET', '/api/contacts/search?q=')
            ->assertJsonCount(0);
    }
}
