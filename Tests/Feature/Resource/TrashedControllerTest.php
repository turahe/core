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

use Turahe\Core\Tests\TestCase;
use Turahe\Contacts\Models\Contact;

class TrashedControllerTest extends TestCase
{
    public function test_user_can_retrieve_trashed_resource_records()
    {
        $this->signIn();
        $contacts = Contact::factory()->count(5)->create();

        $contacts->take(4)->each->delete();

        $this->getJson('/api/trashed/contacts')->assertJsonCount(4, 'data');
    }

    public function test_user_can_search_trashed_resource_records()
    {
        $this->signIn();
        Contact::factory()->create(['first_name' => 'John'])->delete();

        $this->getJson('/api/trashed/contacts/search?q=John')->assertJsonCount(1);
    }

    public function test_user_can_retrieve_trashed_resource_record()
    {
        $this->signIn();
        $contact = tap(Contact::factory()->create(['first_name' => 'John']))->delete();

        $this->getJson('/api/trashed/contacts/'.$contact->id)
            ->assertOk()
            ->assertJson(['first_name' => 'John']);
    }

    public function test_user_can_force_delete_trashed_resource_record()
    {
        $this->signIn();
        $contact = tap(Contact::factory()->create())->delete();

        $this->deleteJson('/api/trashed/contacts/'.$contact->id)->assertNoContent();
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }

    public function test_user_can_restore_trashed_resource_record()
    {
        $this->signIn();
        $contact = tap(Contact::factory()->create())->delete();

        $this->postJson('/api/trashed/contacts/'.$contact->id)->assertOk();
        $this->assertDatabaseHas('contacts', ['deleted_at' => null]);
    }
}
