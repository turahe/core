<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit;

use Exception;
use Turahe\Core\Services\Image\Image;
use PHPUnit\Framework\TestCase;

class ImageServiceTest extends TestCase
{
    private Image $image;

    protected function setUp(): void
    {
        parent::setUp();
        $this->image = new Image();
        
        // Mock the config values that the Image service depends on
        if (!function_exists('config')) {
            function config($key, $default = null) {
                $config = [
                    'core.imgproxy.resize_values' => ['fit', 'fill', 'crop', 'stretch', 'pad'],
                    'core.imgproxy.max_dim_px' => 10000,
                    'core.imgproxy.gravity_values' => ['no', 'sm', 'md', 'lg', 'xl', 'xxl', 'center', 'top', 'bottom', 'left', 'right'],
                    'core.imgproxy.default_extension' => 'jpg',
                    'core.imgproxy.formats' => ['jpg', 'jpeg', 'png', 'webp'],
                ];
                
                return $config[$key] ?? $default;
            }
        }
    }

    public function testMakeMethodSetsAllProperties(): void
    {
        $result = $this->image->make('/path/to/image.jpg', 300, 200, 'png');

        $this->assertSame($this->image, $result);
        $this->assertEquals('/path/to/image.jpg', $this->image->getOriginalPictureUrl());
        $this->assertEquals(300, $this->image->getWidth());
        $this->assertEquals(200, $this->image->getHeight());
        $this->assertEquals('fit', $this->image->getResize());
        $this->assertEquals('no', $this->image->getGravity());
        $this->assertEquals(0, $this->image->getEnlarge());
        $this->assertEquals('png', $this->image->getExtension());
    }

    public function testMakeMethodWithNullExtension(): void
    {
        $result = $this->image->make('/path/to/image.jpg', 300, 200);

        $this->assertSame($this->image, $result);
        $this->assertEquals('jpg', $this->image->getExtension()); // Uses default from config
    }

    public function testMakePresetMethodSetsPreset(): void
    {
        $result = $this->image->makePreset('/path/to/image.jpg', 'thumbnail', 'webp');

        $this->assertSame($this->image, $result);
        $this->assertEquals('/path/to/image.jpg', $this->image->getOriginalPictureUrl());
        $this->assertEquals('thumbnail', $this->image->getPreset());
        $this->assertEquals('webp', $this->image->getExtension());
    }

    public function testMakePresetMethodWithNullExtension(): void
    {
        $result = $this->image->makePreset('/path/to/image.jpg', 'thumbnail');

        $this->assertSame($this->image, $result);
        $this->assertEquals('jpg', $this->image->getExtension()); // Uses default from config
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

    public function testSetResizeWithValidValue(): void
    {
        $result = $this->image->setResize('fill');

        $this->assertSame($this->image, $result);
        $this->assertEquals('fill', $this->image->getResize());
    }

    public function testSetResizeWithInvalidValueUsesDefault(): void
    {
        $result = $this->image->setResize('invalid-resize');

        $this->assertSame($this->image, $result);
        $this->assertEquals('fit', $this->image->getResize()); // Default value
    }

    public function testSetResizeWithNullUsesDefault(): void
    {
        $result = $this->image->setResize(null);

        $this->assertSame($this->image, $result);
        $this->assertEquals('fit', $this->image->getResize()); // Default value
    }

    public function testSetResizeIsCaseInsensitive(): void
    {
        $result = $this->image->setResize('FILL');

        $this->assertSame($this->image, $result);
        $this->assertEquals('fill', $this->image->getResize());
    }

    public function testGetResizeReturnsResize(): void
    {
        $this->image->setResize('crop');
        
        $this->assertEquals('crop', $this->image->getResize());
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

    public function testSetWidthExceedsMaxDimension(): void
    {
        $result = $this->image->setWidth(15000); // Exceeds max_dim_px (10000)

        $this->assertSame($this->image, $result);
        $this->assertEquals(10000, $this->image->getWidth()); // Capped at max from config
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

    public function testSetHeightExceedsMaxDimension(): void
    {
        $result = $this->image->setHeight(15000); // Exceeds max_dim_px (10000)

        $this->assertSame($this->image, $result);
        $this->assertEquals(10000, $this->image->getHeight()); // Capped at max from config
    }

    public function testGetHeightReturnsHeight(): void
    {
        $this->image->setHeight(600);
        
        $this->assertEquals(600, $this->image->getHeight());
    }

    public function testSetGravityWithValidValue(): void
    {
        $result = $this->image->setGravity('center');

        $this->assertSame($this->image, $result);
        $this->assertEquals('center', $this->image->getGravity());
    }

    public function testSetGravityWithInvalidValueUsesDefault(): void
    {
        $result = $this->image->setGravity('invalid-gravity');

        $this->assertSame($this->image, $result);
        $this->assertEquals('no', $this->image->getGravity()); // Default value
    }

    public function testSetGravityWithNullUsesDefault(): void
    {
        $result = $this->image->setGravity(null);

        $this->assertSame($this->image, $result);
        $this->assertEquals('no', $this->image->getGravity()); // Default value
    }

    public function testSetGravityIsCaseInsensitive(): void
    {
        $result = $this->image->setGravity('CENTER');

        $this->assertSame($this->image, $result);
        $this->assertEquals('center', $this->image->getGravity());
    }

    public function testGetGravityReturnsGravity(): void
    {
        $this->image->setGravity('top');
        
        $this->assertEquals('top', $this->image->getGravity());
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

    public function testSetExtensionWithValidValue(): void
    {
        $result = $this->image->setExtension('png');

        $this->assertSame($this->image, $result);
        $this->assertEquals('png', $this->image->getExtension());
    }

    public function testSetExtensionWithNullUsesDefault(): void
    {
        $result = $this->image->setExtension(null);

        $this->assertSame($this->image, $result);
        $this->assertEquals('jpg', $this->image->getExtension()); // Uses default from config
    }

    public function testSetExtensionWithFalse(): void
    {
        $result = $this->image->setExtension(false);

        $this->assertSame($this->image, $result);
        $this->assertEquals('', $this->image->getExtension()); // Empty string when false
    }

    public function testSetExtensionWithInvalidFormatThrowsException(): void
    {
        $this->expectException(Exception::class);
        
        $this->image->setExtension('invalid');
    }

    public function testSetExtensionIsCaseInsensitive(): void
    {
        $result = $this->image->setExtension('PNG');

        $this->assertSame($this->image, $result);
        $this->assertEquals('png', $this->image->getExtension());
    }

    public function testGetExtensionReturnsExtension(): void
    {
        $this->image->setExtension('webp');
        
        $this->assertEquals('webp', $this->image->getExtension());
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

    public function testConstantsAreDefined(): void
    {
        $this->assertEquals('fit', Image::DEFAULT_RESIZE);
        $this->assertEquals('no', Image::DEFAULT_GRAVITY);
        $this->assertEquals(5, Image::MAX_ENLARGE);
        $this->assertEquals(0, Image::MIN_ENLARGE);
    }

    public function testChainingMethods(): void
    {
        $result = $this->image
            ->setWidth(800)
            ->setHeight(600)
            ->setResize('crop')
            ->setGravity('center')
            ->setEnlarge(2)
            ->setExtension('webp')
            ->setOriginalPictureUrl('/path/to/image.jpg');

        $this->assertSame($this->image, $result);
        $this->assertEquals(800, $this->image->getWidth());
        $this->assertEquals(600, $this->image->getHeight());
        $this->assertEquals('crop', $this->image->getResize());
        $this->assertEquals('center', $this->image->getGravity());
        $this->assertEquals(2, $this->image->getEnlarge());
        $this->assertEquals('webp', $this->image->getExtension());
        $this->assertEquals('/path/to/image.jpg', $this->image->getOriginalPictureUrl());
    }

    public function testMakeMethodWithAllParameters(): void
    {
        $result = $this->image->make(
            'https://example.com/photo.jpg',
            1920,
            1080,
            'webp'
        );

        $this->assertSame($this->image, $result);
        $this->assertEquals('https://example.com/photo.jpg', $this->image->getOriginalPictureUrl());
        $this->assertEquals(1920, $this->image->getWidth());
        $this->assertEquals(1080, $this->image->getHeight());
        $this->assertEquals('fit', $this->image->getResize());
        $this->assertEquals('no', $this->image->getGravity());
        $this->assertEquals(0, $this->image->getEnlarge());
        $this->assertEquals('webp', $this->image->getExtension());
    }

    public function testMakePresetMethodWithAllParameters(): void
    {
        $result = $this->image->makePreset(
            'https://example.com/photo.jpg',
            'large-thumbnail',
            'png'
        );

        $this->assertSame($this->image, $result);
        $this->assertEquals('https://example.com/photo.jpg', $this->image->getOriginalPictureUrl());
        $this->assertEquals('large-thumbnail', $this->image->getPreset());
        $this->assertEquals('png', $this->image->getExtension());
    }
} 