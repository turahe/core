<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit;

use InvalidArgumentException;
use Turahe\Core\Services\Image\Image;
use Turahe\Core\Services\Image\ImageSignature;
use Turahe\Core\Tests\TestCase;

class ImageSignatureTest extends TestCase
{
    private function mockImage(array $methods = [])
    {
        $mock = $this->getMockBuilder(Image::class)
            ->disableOriginalConstructor()
            ->onlyMethods($methods)
            ->getMock();

        return $mock;
    }

    public function test_take_returns_signature_path()
    {
        // Arrange
        $img = $this->mockImage([
            'getPreset', 'getExtension',
            'getResize', 'getWidth', 'getHeight', 'getEnlarge', 'getGravity', 'getOriginalPictureUrl',
        ]);
        $img->method('getPreset')->willReturn(null);
        $img->method('getResize')->willReturn('fit');
        $img->method('getWidth')->willReturn(100);
        $img->method('getHeight')->willReturn(100);
        $img->method('getEnlarge')->willReturn(0);
        $img->method('getGravity')->willReturn('no');
        $img->method('getExtension')->willReturn('.jpg');
        $img->method('getOriginalPictureUrl')->willReturn('http://example.com/image.jpg');

        // Set up config values
        config(['core.imgproxy.signature_size' => 32]);
        config(['core.imgproxy.key' => str_repeat('a', 64)]); // 32 bytes in hex = 64 chars
        config(['core.imgproxy.salt' => str_repeat('b', 64)]); // 32 bytes in hex = 64 chars

        $signature = new ImageSignature($img);
        $result = $signature->take();

        $this->assertStringStartsWith('/', $result);
        $this->assertStringContainsString('.jpg', $result);
    }

    public function test_get_key_throws_exception_on_empty_key()
    {
        $img = $this->mockImage();
        config(['core.imgproxy.key' => null]);

        $signature = new ImageSignature($img);
        $this->expectException(InvalidArgumentException::class);
        $signature->getKey();
    }

    public function test_get_salt_throws_exception_on_empty_salt()
    {
        $img = $this->mockImage();
        config(['core.imgproxy.salt' => null]);

        $signature = new ImageSignature($img);
        $this->expectException(InvalidArgumentException::class);
        $signature->getSalt();
    }

    public function test_get_key_throws_exception_on_short_key()
    {
        $img = $this->mockImage();
        config(['core.imgproxy.key' => 'short']);

        $signature = new ImageSignature($img);
        $this->expectException(InvalidArgumentException::class);
        $signature->getKey();
    }

    public function test_get_salt_throws_exception_on_short_salt()
    {
        $img = $this->mockImage();
        config(['core.imgproxy.salt' => 'short']);

        $signature = new ImageSignature($img);
        $this->expectException(InvalidArgumentException::class);
        $signature->getSalt();
    }

    public function test_get_encoded_url_returns_encoded_url()
    {
        $img = $this->mockImage(['getOriginalPictureUrl']);
        $img->method('getOriginalPictureUrl')->willReturn('http://example.com/image.jpg');

        $signature = new ImageSignature($img);
        $result = $signature->getEncodedUrl();

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
        // Should be base64url encoded (no +, /, or = characters)
        $this->assertStringNotContainsString('+', $result);
        $this->assertStringNotContainsString('/', $result);
        $this->assertStringNotContainsString('=', $result);
    }

    public function test_get_path_with_preset()
    {
        $img = $this->mockImage(['getPreset', 'getExtension', 'getOriginalPictureUrl']);
        $img->method('getPreset')->willReturn('thumbnail');
        $img->method('getExtension')->willReturn('.jpg');
        $img->method('getOriginalPictureUrl')->willReturn('http://example.com/image.jpg');

        $signature = new ImageSignature($img);
        $result = $signature->getPath();

        $this->assertStringStartsWith('/thumbnail/', $result);
        $this->assertStringEndsWith('.jpg', $result);
    }

    public function test_get_path_without_preset()
    {
        $img = $this->mockImage([
            'getPreset', 'getExtension', 'getOriginalPictureUrl',
            'getResize', 'getWidth', 'getHeight', 'getEnlarge', 'getGravity',
        ]);
        $img->method('getPreset')->willReturn(null);
        $img->method('getExtension')->willReturn('.jpg');
        $img->method('getOriginalPictureUrl')->willReturn('http://example.com/image.jpg');
        $img->method('getResize')->willReturn('fit');
        $img->method('getWidth')->willReturn(100);
        $img->method('getHeight')->willReturn(100);
        $img->method('getEnlarge')->willReturn(0);
        $img->method('getGravity')->willReturn('no');

        $signature = new ImageSignature($img);
        $result = $signature->getPath();

        $this->assertStringStartsWith('/rs:fit:100:100:0/g:no/', $result);
        $this->assertStringEndsWith('.jpg', $result);
    }
}
