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

use Tests\Fixtures\Event;
use Turahe\Core\Tests\TestCase;
use Turahe\Contacts\Models\Company;
use Turahe\Contacts\Models\Contact;
use Turahe\Contacts\Models\Source;
use Turahe\Core\Facades\Fields;
use Turahe\Core\Fields\Email;
use Turahe\Core\Fields\Text;
use Turahe\Deals\Models\Deal;

class ResourcefulControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_the_resources_endpoints()
    {
        $this->getJson('/api/FAKE_RESOURCE')->assertUnauthorized();
        $this->getJson('/api/FAKE_RESOURCE/FAKE_ID')->assertUnauthorized();
        $this->postJson('/api/FAKE_RESOURCE')->assertUnauthorized();
        $this->putJson('/api/FAKE_RESOURCE/FAKE_ID')->assertUnauthorized();
        $this->deleteJson('/api/FAKE_RESOURCE/FAKE_ID')->assertUnauthorized();
    }

    public function test_user_can_associate_assications_provided_in_the_associations_attribute()
    {
        $this->signIn();

        $company = Company::factory()->create();
        $deal = Deal::factory()->create();

        $this->postJson('/api/contacts', [
            'first_name' => 'John',
            'associations' => [
                'companies' => [$company->id],
                'deals' => [$deal->id],
            ],
        ]);

        $this->assertCount(1, $company->contacts);
        $this->assertCount(1, $deal->contacts);
    }

    public function test_user_cannot_associate_associations_that_is_not_authorized_to_see()
    {
        $user1 = $this->asRegularUser()->createUser();
        $user2 = $this->asRegularUser()->createUser();

        $this->signIn($user2);

        $company = Company::factory()->for($user1)->create();

        $this->postJson('/api/contacts', [
            'first_name' => 'John',
            'associations' => [
                'companies' => [$company->id],
            ],
        ]);

        $this->assertCount(0, $company->contacts);
    }

    public function test_user_can_create_resource_by_providing_labels_instead_of_ids()
    {
        $this->signIn();
        $source = Source::factory()->create();

        $this->postJson('/api/contacts', [
            'first_name' => 'John',
            'source_id' => $source->name,
        ]);

        $this->assertDatabaseHas('contacts', ['source_id' => $source->id]);
    }

    public function test_it_makes_sure_non_authorized_fields_are_removed_from_the_request_attributes()
    {
        $this->signIn();

        Fields::replace('contacts', [
            Text::make('first_name'),
            Text::make('last_name')->rules('required')->canSee(function () {
                return false;
            }),
            Email::make('email')->rules('required')->readOnly(true),
        ]);

        $this->postJson('/api/contacts', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ])->assertJsonMissing(['last_name', 'email']);

        $contact = Contact::first();

        $this->assertNull($contact->last_name);
        $this->assertNull($contact->email);
    }

    public function test_it_makes_sure_additional_data_can_be_passed_via_payload()
    {
        $this->signIn();

        Fields::replace('contacts', [
            Text::make('first_name'),
        ]);

        $this->postJson('/api/contacts', [
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertDatabaseHas('contacts', ['last_name' => 'Doe']);

        Fields::replace('contacts', [
            Text::make('first_name'),
            Text::make('last_name')->hidden(),
        ]);

        $this->postJson('/api/contacts', [
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertDatabaseHas('contacts', ['last_name' => 'Doe']);
    }

    public function test_user_can_create_resource_with_morph_many_field()
    {
        $this->signIn();

        $this->postJson('/api/events', [
            'title' => 'Event Title',
            'locations' => $locations = [
                ['display_name' => 'Avenue Park', 'location_type' => 'default'],
                ['display_name' => 'Facebook', 'location_type' => 'Remote'],
            ],
        ])->assertJson([
            'locations' => $locations,
        ]);
    }

    public function test_user_can_create_resource_record_with_belongs_to_field()
    {
        $user = $this->signIn();

        $payload = Event::factory()->make([
            'user_id' => $user->getKey(),
        ])->toArray();

        $this->postJson('/api/events', $payload)
            ->assertJson(['user_id' => $user->getKey()])
            ->assertJsonPath('user.name', $user->name);
    }
}
