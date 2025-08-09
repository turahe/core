<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit;

use InvalidArgumentException;
use Turahe\Core\Services\Image\ImageSignature;
use Turahe\Core\Services\Image\Image;
use PHPUnit\Framework\TestCase;

class ImageSignatureServiceTest extends TestCase
{
    private function mockImage(array $methods = [])
    {
        $mock = $this->getMockBuilder(Image::class)
            ->disableOriginalConstructor()
            ->onlyMethods($methods)
            ->getMock();
        return $mock;
    }

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the config values that the ImageSignature service depends on
        if (!function_exists('config')) {
            function config($key, $default = null) {
                $config = [
                    'core.imgproxy.signature_size' => 32,
                    'core.imgproxy.key' => str_repeat('a', 64), // 32 bytes in hex = 64 chars
                    'core.imgproxy.salt' => str_repeat('b', 64), // 32 bytes in hex = 64 chars
                ];
                
                return $config[$key] ?? $default;
            }
        }
    }

    public function testTakeReturnsSignaturePath()
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

        $signature = new ImageSignature($img);
        $result = $signature->take();
        
        $this->assertStringStartsWith('/', $result);
        $this->assertStringContainsString('.jpg', $result);
    }

    public function testGetKeyThrowsExceptionOnEmptyKey()
    {
        $img = $this->mockImage();
        
        // Override config for this test
        $GLOBALS['config_override'] = ['core.imgproxy.key' => null];
        
        $signature = new ImageSignature($img);
        $this->expectException(InvalidArgumentException::class);
        $signature->getKey();
    }

    public function testGetSaltThrowsExceptionOnEmptySalt()
    {
        $img = $this->mockImage();
        
        // Override config for this test
        $GLOBALS['config_override'] = ['core.imgproxy.salt' => null];
        
        $signature = new ImageSignature($img);
        $this->expectException(InvalidArgumentException::class);
        $signature->getSalt();
    }

    public function testGetKeyThrowsExceptionOnShortKey()
    {
        $img = $this->mockImage();
        
        // Override config for this test
        $GLOBALS['config_override'] = ['core.imgproxy.key' => 'short'];
        
        $signature = new ImageSignature($img);
        $this->expectException(InvalidArgumentException::class);
        $signature->getKey();
    }

    public function testGetSaltThrowsExceptionOnShortSalt()
    {
        $img = $this->mockImage();
        
        // Override config for this test
        $GLOBALS['config_override'] = ['core.imgproxy.salt' => 'short'];
        
        $signature = new ImageSignature($img);
        $this->expectException(InvalidArgumentException::class);
        $signature->getSalt();
    }

    public function testGetEncodedUrlReturnsEncodedUrl()
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

    public function testGetPathWithPreset()
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

    public function testGetPathWithoutPreset()
    {
        $img = $this->mockImage([
            'getPreset', 'getExtension', 'getOriginalPictureUrl',
            'getResize', 'getWidth', 'getHeight', 'getEnlarge', 'getGravity'
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

    public function testGetBinaryKeyReturnsBinaryKey()
    {
        $img = $this->mockImage();
        $signature = new ImageSignature($img);
        
        $result = $signature->getBinaryKey();
        
        $this->assertIsString($result);
        $this->assertEquals(32, strlen($result)); // 32 bytes
    }

    public function testGetBinarySaltReturnsBinarySalt()
    {
        $img = $this->mockImage();
        $signature = new ImageSignature($img);
        
        $result = $signature->getBinarySalt();
        
        $this->assertIsString($result);
        $this->assertEquals(32, strlen($result)); // 32 bytes
    }

    public function testGetBinaryKeyThrowsExceptionOnInvalidKey()
    {
        $img = $this->mockImage();
        
        // Override config for this test
        $GLOBALS['config_override'] = ['core.imgproxy.key' => 'invalid-hex'];
        
        $signature = new ImageSignature($img);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Key expected to be hex-encoded string');
        $signature->getBinaryKey();
    }

    public function testGetBinarySaltThrowsExceptionOnInvalidSalt()
    {
        $img = $this->mockImage();
        
        // Override config for this test
        $GLOBALS['config_override'] = ['core.imgproxy.salt' => 'invalid-hex'];
        
        $signature = new ImageSignature($img);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Salt expected to be hex-encoded string');
        $signature->getBinarySalt();
    }

    public function testTakeWithSignatureSize()
    {
        $img = $this->mockImage([
            'getPreset', 'getExtension', 'getOriginalPictureUrl',
            'getResize', 'getWidth', 'getHeight', 'getEnlarge', 'getGravity'
        ]);
        $img->method('getPreset')->willReturn(null);
        $img->method('getResize')->willReturn('fit');
        $img->method('getWidth')->willReturn(100);
        $img->method('getHeight')->willReturn(100);
        $img->method('getEnlarge')->willReturn(0);
        $img->method('getGravity')->willReturn('no');
        $img->method('getExtension')->willReturn('.jpg');
        $img->method('getOriginalPictureUrl')->willReturn('http://example.com/image.jpg');

        $signature = new ImageSignature($img);
        $result = $signature->take();
        
        $this->assertStringStartsWith('/', $result);
        $this->assertStringContainsString('.jpg', $result);
        $this->assertGreaterThan(50, strlen($result)); // Should be a reasonable length
    }

    public function testTakeWithoutSignatureSize()
    {
        $img = $this->mockImage([
            'getPreset', 'getExtension', 'getOriginalPictureUrl',
            'getResize', 'getWidth', 'getHeight', 'getEnlarge', 'getGravity'
        ]);
        $img->method('getPreset')->willReturn(null);
        $img->method('getResize')->willReturn('fit');
        $img->method('getWidth')->willReturn(100);
        $img->method('getHeight')->willReturn(100);
        $img->method('getEnlarge')->willReturn(0);
        $img->method('getGravity')->willReturn('no');
        $img->method('getExtension')->willReturn('.jpg');
        $img->method('getOriginalPictureUrl')->willReturn('http://example.com/image.jpg');

        // Override config to disable signature size
        $GLOBALS['config_override'] = ['core.imgproxy.signature_size' => null];

        $signature = new ImageSignature($img);
        $result = $signature->take();
        
        $this->assertStringStartsWith('/', $result);
        $this->assertStringContainsString('.jpg', $result);
    }
} 