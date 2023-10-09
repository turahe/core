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

namespace Modules\Core\Tests\Feature\Resource;

use Tests\TestCase;
use Modules\Core\Fields\Text;
use Modules\Core\Fields\Email;
use Modules\Core\Facades\Fields;
use Modules\Contacts\Models\Contact;

class FieldControllerTest extends TestCase
{
    public function test_resource_create_fields_can_be_retrieved()
    {
        $this->signIn();

        Fields::replace('contacts', [
            Text::make('first_name'),
            Text::make('last_name'),
            Email::make('make')->hideWhenCreating(),
        ]);

        $this->getJson('/api/contacts/create-fields')->assertJsonCount(2);
    }

    public function test_resource_update_fields_can_be_retrieved()
    {
        $this->signIn();
        $contact = Contact::factory()->create();
        Fields::replace('contacts', [
            Text::make('first_name'),
            Text::make('last_name')->hideWhenUpdating(),
        ]);

        $this->getJson('/api/contacts/'.$contact->id.'/update-fields')->assertJsonCount(1);
    }
}
