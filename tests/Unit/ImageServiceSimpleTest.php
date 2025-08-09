<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit;

use Exception;
use Turahe\Core\Services\Image\Image;
use PHPUnit\Framework\TestCase;

class ImageServiceSimpleTest extends TestCase
{
    private Image $image;

    protected function setUp(): void
    {
        parent::setUp();
        $this->image = new Image();
    }

    public function testImageClassExists(): void
    {
        $this->assertInstanceOf(Image::class, $this->image);
    }

    public function testConstantsAreDefined(): void
    {
        $this->assertEquals('fit', Image::DEFAULT_RESIZE);
        $this->assertEquals('no', Image::DEFAULT_GRAVITY);
        $this->assertEquals(5, Image::MAX_ENLARGE);
        $this->assertEquals(0, Image::MIN_ENLARGE);
    }

    public function testSetOriginalPictureUrlSetsUrl(): void
    {
        $result = $this->image->setOriginalPictureUrl('https://example.com/image.jpg');

        $this->assertSame($this->image, $result);
        $this->assertEquals('https://example.com/image.jpg', $this->image->getOriginalPictureUrl());
    }

    public function testGetOriginalPictureUrlReturnsUrl(): void
    {
        $this->image->setOriginalPictureUrl('/local/path/image.png');
        
        $this->assertEquals('/local/path/image.png', $this->image->getOriginalPictureUrl());
    }

    public function testSetWidthWithValidValue(): void
    {
        $result = $this->image->setWidth(500);

        $this->assertSame($this->image, $result);
        $this->assertEquals(500, $this->image->getWidth());
    }

    public function testSetWidthWithZeroUsesOne(): void
    {
        $result = $this->image->setWidth(0);

        $this->assertSame($this->image, $result);
        $this->assertEquals(1, $this->image->getWidth());
    }

    public function testSetWidthWithNegativeValueUsesAbsolute(): void
    {
        $result = $this->image->setWidth(-300);

        $this->assertSame($this->image, $result);
        $this->assertEquals(300, $this->image->getWidth());
    }

    public function testGetWidthReturnsWidth(): void
    {
        $this->image->setWidth(800);
        
        $this->assertEquals(800, $this->image->getWidth());
    }

    public function testSetHeightWithValidValue(): void
    {
        $result = $this->image->setHeight(400);

        $this->assertSame($this->image, $result);
        $this->assertEquals(400, $this->image->getHeight());
    }

    public function testSetHeightWithZeroUsesOne(): void
    {
        $result = $this->image->setHeight(0);

        $this->assertSame($this->image, $result);
        $this->assertEquals(1, $this->image->getHeight());
    }

    public function testSetHeightWithNegativeValueUsesAbsolute(): void
    {
        $result = $this->image->setHeight(-200);

        $this->assertSame($this->image, $result);
        $this->assertEquals(200, $this->image->getHeight());
    }

    public function testGetHeightReturnsHeight(): void
    {
        $this->image->setHeight(600);
        
        $this->assertEquals(600, $this->image->getHeight());
    }

    public function testSetEnlargeWithValidValue(): void
    {
        $result = $this->image->setEnlarge(3);

        $this->assertSame($this->image, $result);
        $this->assertEquals(3, $this->image->getEnlarge());
    }

    public function testSetEnlargeWithZero(): void
    {
        $result = $this->image->setEnlarge(0);

        $this->assertSame($this->image, $result);
        $this->assertEquals(0, $this->image->getEnlarge());
    }

    public function testSetEnlargeWithNegativeValueUsesAbsolute(): void
    {
        $result = $this->image->setEnlarge(-2);

        $this->assertSame($this->image, $result);
        $this->assertEquals(2, $this->image->getEnlarge());
    }

    public function testSetEnlargeExceedsMaxEnlarge(): void
    {
        $result = $this->image->setEnlarge(10); // Exceeds MAX_ENLARGE (5)

        $this->assertSame($this->image, $result);
        $this->assertEquals(5, $this->image->getEnlarge()); // Capped at max
    }

    public function testGetEnlargeReturnsEnlarge(): void
    {
        $this->image->setEnlarge(4);
        
        $this->assertEquals(4, $this->image->getEnlarge());
    }

    public function testGetPresetReturnsPreset(): void
    {
        $this->image->setPreset('custom-preset');
        
        $this->assertEquals('custom-preset', $this->image->getPreset());
    }

    public function testSetPresetReturnsSelf(): void
    {
        $result = $this->image->setPreset('test-preset');

        $this->assertSame($this->image, $result);
        $this->assertEquals('test-preset', $this->image->getPreset());
    }

    public function testChainingMethods(): void
    {
        $result = $this->image
            ->setWidth(800)
            ->setHeight(600)
            ->setEnlarge(2)
            ->setOriginalPictureUrl('/path/to/image.jpg');

        $this->assertSame($this->image, $result);
        $this->assertEquals(800, $this->image->getWidth());
        $this->assertEquals(600, $this->image->getHeight());
        $this->assertEquals(2, $this->image->getEnlarge());
        $this->assertEquals('/path/to/image.jpg', $this->image->getOriginalPictureUrl());
    }

    public function testImageServiceBasicFunctionality(): void
    {
        // Test basic image configuration
        $image = new Image();
        
        $image->setOriginalPictureUrl('https://example.com/photo.jpg')
              ->setWidth(1920)
              ->setHeight(1080)
              ->setEnlarge(1)
              ->setPreset('large');
        
        $this->assertEquals('https://example.com/photo.jpg', $image->getOriginalPictureUrl());
        $this->assertEquals(1920, $image->getWidth());
        $this->assertEquals(1080, $image->getHeight());
        $this->assertEquals(1, $image->getEnlarge());
        $this->assertEquals('large', $image->getPreset());
    }

    public function testImageServiceWithDifferentSizes(): void
    {
        $image = new Image();
        
        // Test small size
        $image->setWidth(100)->setHeight(100);
        $this->assertEquals(100, $image->getWidth());
        $this->assertEquals(100, $image->getHeight());
        
        // Test large size
        $image->setWidth(4000)->setHeight(3000);
        $this->assertEquals(4000, $image->getWidth());
        $this->assertEquals(3000, $image->getHeight());
    }

    public function testImageServiceWithDifferentUrls(): void
    {
        $image = new Image();
        
        // Test local path
        $image->setOriginalPictureUrl('/local/path/image.jpg');
        $this->assertEquals('/local/path/image.jpg', $image->getOriginalPictureUrl());
        
        // Test remote URL
        $image->setOriginalPictureUrl('https://cdn.example.com/images/photo.png');
        $this->assertEquals('https://cdn.example.com/images/photo.png', $image->getOriginalPictureUrl());
        
        // Test relative path
        $image->setOriginalPictureUrl('images/avatar.webp');
        $this->assertEquals('images/avatar.webp', $image->getOriginalPictureUrl());
    }

    public function testImageServiceWithDifferentPresets(): void
    {
        $image = new Image();
        
        $presets = ['thumbnail', 'medium', 'large', 'original', 'custom-preset'];
        
        foreach ($presets as $preset) {
            $image->setPreset($preset);
            $this->assertEquals($preset, $image->getPreset());
        }
    }

    public function testImageServiceEnlargeLimits(): void
    {
        $image = new Image();
        
        // Test minimum enlarge
        $image->setEnlarge(0);
        $this->assertEquals(0, $image->getEnlarge());
        
        // Test maximum enlarge
        $image->setEnlarge(5);
        $this->assertEquals(5, $image->getEnlarge());
        
        // Test exceeding maximum (should be capped)
        $image->setEnlarge(10);
        $this->assertEquals(5, $image->getEnlarge());
        
        // Test negative values (should use absolute)
        $image->setEnlarge(-3);
        $this->assertEquals(3, $image->getEnlarge());
    }

    public function testImageServiceDimensionLimits(): void
    {
        $image = new Image();
        
        // Test minimum dimensions
        $image->setWidth(0)->setHeight(0);
        $this->assertEquals(1, $image->getWidth());
        $this->assertEquals(1, $image->getHeight());
        
        // Test negative dimensions (should use absolute)
        $image->setWidth(-500)->setHeight(-300);
        $this->assertEquals(500, $image->getWidth());
        $this->assertEquals(300, $image->getHeight());
        
        // Test normal dimensions
        $image->setWidth(1920)->setHeight(1080);
        $this->assertEquals(1920, $image->getWidth());
        $this->assertEquals(1080, $image->getHeight());
    }
} 