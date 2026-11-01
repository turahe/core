<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit\OAuth;

use Turahe\Core\Microsoft\OAuth\MicrosoftResourceOwner;
use Turahe\Core\Tests\TestCase;

class MicrosoftResourceOwnerTest extends TestCase
{
    public function test_prefers_the_email_claim(): void
    {
        $owner = new MicrosoftResourceOwner([
            'id' => '1',
            'email' => 'a@b.c',
            'userPrincipalName' => 'upn@b.c',
        ]);

        $this->assertSame('a@b.c', $owner->getEmail());
    }

    public function test_falls_back_to_the_user_principal_name(): void
    {
        $owner = new MicrosoftResourceOwner([
            'id' => '1',
            'userPrincipalName' => 'upn@b.c',
        ]);

        $this->assertSame('upn@b.c', $owner->getEmail());
    }
}
