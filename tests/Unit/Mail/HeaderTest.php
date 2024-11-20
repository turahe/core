<?php

namespace Turahe\Core\Tests\Unit\Mail;

use Illuminate\Contracts\Support\Arrayable;
use PHPUnit\Framework\TestCase;
use Turahe\Core\Common\Mail\Headers\Header;

class HeaderTest extends TestCase
{
    public function test_header_has_name(): void
    {
        $header = new Header('x-concord-test', 'value');

        $this->assertSame('x-concord-test', $header->getName());
    }

    public function test_header_name_is_aways_in_lowercase(): void
    {
        $header = new Header('X-Concord-Value', 'value');

        $this->assertSame('x-concord-value', $header->getName());
    }

    public function test_header_has_value(): void
    {
        $header = new Header('x-concord-test', 'value');

        $this->assertSame('value', $header->getValue());
    }

    public function test_header_is_arrayable(): void
    {
        $header = new Header('x-concord-test', 'value');

        $this->assertInstanceOf(Arrayable::class, $header);

        $this->assertEquals([
            'name' => 'x-concord-test',
            'value' => 'value',
        ], $header->toArray());
    }
}
