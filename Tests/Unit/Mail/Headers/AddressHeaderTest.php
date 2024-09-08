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

namespace Turahe\Core\Tests\Unit\Mail\Headers;

use Turahe\Core\Tests\TestCase;
use Turahe\Core\Mail\Headers\AddressHeader;

class AddressHeaderTest extends TestCase
{
    public function test_address_header_has_address()
    {
        $header = new AddressHeader('to', 'email@example.com');

        $this->assertSame('email@example.com', $header->getAddress());
    }

    public function test_address_header_has_person_name()
    {
        $header = new AddressHeader('to', 'email@example.com', 'Marjan');

        $this->assertSame('Marjan', $header->getPersonName());
    }

    public function test_address_header_has_addresses()
    {
        $header = new AddressHeader('to', 'email@example.com', 'Marjan');

        $this->assertCount(1, $header->getAll());
    }

    public function test_address_header_is_parsed_correctly()
    {
        $header = new AddressHeader('to', 'John <john@example.com>');

        $this->assertSame('John', $header->getPersonName());
        $this->assertSame('john@example.com', $header->getAddress());

        $header = new AddressHeader('to', 'John <john@example.com>, Jack <jack@example.com>');

        $this->assertCount(2, $header->getAll());
        $this->assertSame('John', $header->getAll()[0]['name']);
        $this->assertSame('john@example.com', $header->getAll()[0]['address']);
        $this->assertSame('Jack', $header->getAll()[1]['name']);
        $this->assertSame('jack@example.com', $header->getAll()[1]['address']);

        $header = new AddressHeader('to', [
            'jack@example.com' => 'Jack',
            'john@example.com',
        ]);

        $this->assertCount(2, $header->getAll());

        $this->assertSame('Jack', $header->getAll()[0]['name']);
        $this->assertSame('jack@example.com', $header->getAll()[0]['address']);

        $this->assertSame('john@example.com', $header->getAll()[1]['address']);
        $this->assertSame('john@example.com', $header->getAll()[1]['name']);
    }

    public function test_address_header_arrayable_returns_all_addresses()
    {
        $header = new AddressHeader('to', 'John <john@example.com>, Jack <jack@example.com>');

        $this->assertCount(2, $header->toArray());
    }
}
