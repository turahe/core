<?php

namespace Turahe\Core\Tests\Unit\Mail;

use PHPUnit\Framework\TestCase;
use Turahe\Core\Common\Mail\Headers\IdHeader;

class IdHeaderTest extends TestCase
{
    public function test_id_header_has_value(): void
    {
        $header = new IdHeader('message-id', '<unique-id@example.com>');

        $this->assertSame('unique-id@example.com', $header->getValue());
    }

    public function test_id_header_is_properly_parsed(): void
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
