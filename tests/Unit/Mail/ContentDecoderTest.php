<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit\Mail;

use Turahe\Core\Common\Mail\ContentDecoder;
use Turahe\Core\Tests\TestCase;

class ContentDecoderTest extends TestCase
{
    public function test_returns_the_string_when_no_transfer_encoding_is_given(): void
    {
        $this->assertSame('plain', ContentDecoder::decode('plain'));
        $this->assertSame('plain', ContentDecoder::decode('plain', ''));
    }

    public function test_decodes_base64(): void
    {
        $this->assertSame('hello', ContentDecoder::decode(base64_encode('hello'), 'BASE64'));
    }

    public function test_decodes_quoted_printable(): void
    {
        $this->assertSame('hello world', ContentDecoder::decode('hello=20world', 'quoted-printable'));
    }

    public function test_leaves_unknown_transfer_encodings_unchanged(): void
    {
        $this->assertSame('abc', ContentDecoder::decode('abc', '7bit'));
    }

    public function test_converts_between_charsets(): void
    {
        $result = ContentDecoder::decode("caf\xE9", null, 'ISO-8859-1', 'UTF-8');

        $this->assertSame('café', $result);
    }

    public function test_skips_conversion_when_the_charsets_match(): void
    {
        $this->assertSame('abc', ContentDecoder::decode('abc', null, 'UTF-8', 'utf-8'));
    }
}
