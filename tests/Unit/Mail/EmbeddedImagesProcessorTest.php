<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit\Mail;

use Turahe\Core\Common\Mail\EmbeddedImagesProcessor;
use Turahe\Core\Tests\TestCase;

class EmbeddedImagesProcessorTest extends TestCase
{
    public function test_returns_null_when_the_body_is_null(): void
    {
        $processor = new EmbeddedImagesProcessor;

        $this->assertNull($processor(null, function (): string {
            $this->fail('The callback must not run for an empty body.');
        }));
    }

    public function test_leaves_a_remote_image_unchanged(): void
    {
        $processor = new EmbeddedImagesProcessor;
        $body = '<img src="https://example.com/a.png">';

        $this->assertSame($body, $processor($body, function (): string {
            $this->fail('The callback must not run for a remote image.');
        }));
    }

    public function test_replaces_a_data_uri_with_the_callback_id(): void
    {
        $processor = new EmbeddedImagesProcessor;
        $body = '<img src="data:image/png;base64,aGVsbG8=">';
        $seen = [];

        $result = $processor($body, function (string $contents, string $fileName, string $mime) use (&$seen): string {
            $seen = [$contents, $fileName, $mime];

            return 'cid:image-1';
        });

        $this->assertSame('hello', $seen[0]);
        $this->assertSame('image/png', $seen[2]);
        $this->assertStringEndsWith('.png', $seen[1]);
        $this->assertSame('<img src="cid:image-1">', $result);
    }
}
