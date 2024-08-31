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

use Tests\TestCase;
use Illuminate\Support\Carbon;
use Turahe\Core\Facades\Format;
use Turahe\Contacts\Models\Company;
use Turahe\Contacts\Models\Contact;

class PlaceholdersControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_the_placeholders_endpoints()
    {
        $this->getJson('/api/placeholders')->assertUnauthorized();
        $this->postJson('/api/placeholders/input-fields')->assertUnauthorized();
        $this->postJson('/api/placeholders/interpolation')->assertUnauthorized();
    }

    public function test_placeholders_can_be_retrieved()
    {
        $this->signIn();

        $this->getJson('/api/placeholders?'.http_build_query(['resources' => ['contacts']]))->assertJsonStructure([
            'contacts' => ['label', 'placeholders' => [
                0 => [
                    'tag', 'description', 'interpolation_start', 'interpolation_end',
                ],
            ],
            ],
        ]);
    }

    public function test_it_differentiate_between_resources_data()
    {
        $this->signIn();

        $contact = Contact::factory()->create(['created_at' => $contactCreatedAt = Carbon::parse('2023-04-25 00:00:00')]);
        $company = Company::factory()->create(['created_at' => $companyCreatedAt = Carbon::parse('2023-04-26 00:00:00')]);

        $response = $this->postJson('/api/placeholders/input-fields', [
            'content'   => '<input class="_placeholder" type="text" value="" placeholder="Created At" data-tag="contact.created_at" /><input class="_placeholder" type="text" value="" placeholder="Created At" data-tag="company.created_at" />',
            'resources' => [['name' => 'contacts', 'id' => $contact->id], ['name' => 'companies', 'id' => $company->id]],
        ]);

        $expected = '<input class="_placeholder" type="text" value="'.Format::dateTime($contactCreatedAt).'" placeholder="Created At" data-tag="contact.created_at" data-autofilled /><input class="_placeholder" type="text" value="'.Format::dateTime($companyCreatedAt).'" placeholder="Created At" data-tag="company.created_at" data-autofilled />';

        $this->assertEquals($expected, json_decode($response->getContent()));
    }

    public function test_placeholders_can_be_parsed()
    {
        $this->signIn();

        $contact = Contact::factory()->create();

        $response = $this->postJson('/api/placeholders/input-fields', [
            'content'   => '<input class="_placeholder" type="text" value="" placeholder="E-Mail Address" data-tag="contact.email" /><input class="_placeholder" type="text" value="" placeholder="First Name" data-tag="contact.first_name" />',
            'resources' => [['name' => 'contacts', 'id' => $contact->id]],
        ]);

        $expected = '<input class="_placeholder" type="text" value="'.$contact->email.'" placeholder="E-Mail Address" data-tag="contact.email" data-autofilled /><input class="_placeholder" type="text" value="'.$contact->first_name.'" placeholder="First Name" data-tag="contact.first_name" data-autofilled />';

        $this->assertEquals($expected, json_decode($response->getContent()));
    }

    public function test_it_does_not_parse_placeholders_if_the_user_is_not_authorized_to_view_the_resource_record()
    {
        $this->asRegularUser()->signIn();
        $otherUser = $this->createUser();
        $contact = Contact::factory()->for($otherUser)->create();

        $response = $this->postJson('/api/placeholders/input-fields', [
            'content'   => '<input class="_placeholder" type="text" value="" placeholder="E-Mail Address" data-group="contacts" data-tag="email" />',
            'resources' => [['name' => 'contacts', 'id' => $contact->id]],
        ]);

        $this->assertEquals('<input class="_placeholder" type="text" value="" placeholder="E-Mail Address" data-group="contacts" data-tag="email" />', json_decode($response->getContent()));
    }
}
