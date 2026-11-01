<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit\OAuth;

use Turahe\Core\OAuth\ResourceOwner;
use Turahe\Core\Tests\TestCase;

class ResourceOwnerTest extends TestCase
{
    public function test_reads_the_id_email_and_raw_response(): void
    {
        $response = ['id' => '1', 'email' => 'a@b.c', 'name' => 'Ada'];
        $owner = new ResourceOwner($response);

        $this->assertSame('1', $owner->getId());
        $this->assertSame('a@b.c', $owner->getEmail());
        $this->assertSame($response, $owner->toArray());
    }

    public function test_email_is_null_when_the_response_omits_it(): void
    {
        $owner = new ResourceOwner(['id' => '1']);

        $this->assertNull($owner->getEmail());
    }
}
