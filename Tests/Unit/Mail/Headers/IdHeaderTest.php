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

use Turahe\Core\Mail\Headers\IdHeader;
use Turahe\Core\Tests\TestCase;

class IdHeaderTest extends TestCase
{
    public function test_id_header_has_value()
    {
        $header = new IdHeader('message-id', '<unique-id@example.com>');

        $this->assertSame('unique-id@example.com', $header->getValue());
    }

    public function test_id_header_is_properly_parsed()
    {
        $header = new IdHeader('message-id', '<unique-id@example.com>');

        $this->assertSame('unique-id@example.com', $header->getValue());

        $header = new IdHeader('message-id', '<unique-id@example.com>, <unique-id1@example.com>');
        $this->assertCount(2, $header->getIds());
        $this->assertSame('unique-id@example.com', $header->getIds()[0]);
        $this->assertSame('unique-id1@example.com', $header->getIds()[1]);

        $header = new IdHeader('message-id', '<unique-id@example.com>,<unique-id1@example.com>');
        $this->assertCount(2, $header->getIds());
        $this->assertSame('unique-id@example.com', $header->getIds()[0]);
        $this->assertSame('unique-id1@example.com', $header->getIds()[1]);

        $header = new IdHeader('message-id', '<unique-id@example.com>any-separator <unique-id1@example.com>');
        $this->assertCount(2, $header->getIds());
        $this->assertSame('unique-id@example.com', $header->getIds()[0]);
        $this->assertSame('unique-id1@example.com', $header->getIds()[1]);

        $header = new IdHeader('message-id', '<unique-id@example.com>    <unique-id1@example.com>');
        $this->assertCount(2, $header->getIds());
        $this->assertSame('unique-id@example.com', $header->getIds()[0]);
        $this->assertSame('unique-id1@example.com', $header->getIds()[1]);

        $header = new IdHeader('message-id', '<unique-id@example.com><unique-id1@example.com>');
        $this->assertCount(2, $header->getIds());
        $this->assertSame('unique-id@example.com', $header->getIds()[0]);
        $this->assertSame('unique-id1@example.com', $header->getIds()[1]);

        $header = new IdHeader('message-id', ['<unique-id@example.com>', '<unique-id1@example.com>']);
        $this->assertCount(2, $header->getIds());
        $this->assertSame('unique-id@example.com', $header->getIds()[0]);
        $this->assertSame('unique-id1@example.com', $header->getIds()[1]);
    }
}
