<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit;

use Exception;
use Turahe\Core\Services\Image\Image;
use Turahe\Core\Tests\TestCase;

class ImageTest extends TestCase
{
    private Image $image;

    protected function setUp(): void
    {
        parent::setUp();
        $this->image = new Image;

        // Use the actual config values from config/core.php
        // No need to override config as it's already loaded from the config file
    }

    public function test_make_method_sets_all_properties(): void
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

    public function test_make_method_with_null_extension(): void
    {
        $result = $this->image->make('/path/to/image.jpg', 300, 200);

        $this->assertSame($this->image, $result);
        $this->assertEquals('jpg', $this->image->getExtension()); // Uses default from config
    }

    public function test_make_preset_method_sets_preset(): void
    {
        $result = $this->image->makePreset('/path/to/image.jpg', 'thumbnail', 'webp');

        $this->assertSame($this->image, $result);
        $this->assertEquals('/path/to/image.jpg', $this->image->getOriginalPictureUrl());
        $this->assertEquals('thumbnail', $this->image->getPreset());
        $this->assertEquals('webp', $this->image->getExtension());
    }

    public function test_make_preset_method_with_null_extension(): void
    {
        $result = $this->image->makePreset('/path/to/image.jpg', 'thumbnail');

        $this->assertSame($this->image, $result);
        $this->assertEquals('jpg', $this->image->getExtension()); // Uses default from config
    }

    public function test_get_preset_returns_preset(): void
    {
        $this->image->setPreset('custom-preset');

        $this->assertEquals('custom-preset', $this->image->getPreset());
    }

    public function test_set_preset_returns_self(): void
    {
        $result = $this->image->setPreset('test-preset');

        $this->assertSame($this->image, $result);
        $this->assertEquals('test-preset', $this->image->getPreset());
    }

    public function test_set_resize_with_valid_value(): void
    {
        $result = $this->image->setResize('fill');

        $this->assertSame($this->image, $result);
        $this->assertEquals('fill', $this->image->getResize());
    }

    public function test_set_resize_with_invalid_value_uses_default(): void
    {
        $result = $this->image->setResize('invalid-resize');

        $this->assertSame($this->image, $result);
        $this->assertEquals('fit', $this->image->getResize()); // Default value
    }

    public function test_set_resize_with_null_uses_default(): void
    {
        $result = $this->image->setResize(null);

        $this->assertSame($this->image, $result);
        $this->assertEquals('fit', $this->image->getResize()); // Default value
    }

    public function test_set_resize_is_case_insensitive(): void
    {
        $result = $this->image->setResize('FILL');

        $this->assertSame($this->image, $result);
        $this->assertEquals('fill', $this->image->getResize());
    }

    public function test_get_resize_returns_resize(): void
    {
        $this->image->setResize('crop');

        $this->assertEquals('crop', $this->image->getResize());
    }

    public function test_set_width_with_valid_value(): void
    {
        $result = $this->image->setWidth(500);

        $this->assertSame($this->image, $result);
        $this->assertEquals(500, $this->image->getWidth());
    }

    public function test_set_width_with_zero_uses_one(): void
    {
        $result = $this->image->setWidth(0);

        $this->assertSame($this->image, $result);
        $this->assertEquals(1, $this->image->getWidth());
    }

    public function test_set_width_with_negative_value_uses_absolute(): void
    {
        $result = $this->image->setWidth(-300);

        $this->assertSame($this->image, $result);
        $this->assertEquals(300, $this->image->getWidth());
    }

    public function test_set_width_exceeds_max_dimension(): void
    {
        $result = $this->image->setWidth(15000); // Exceeds max_dim_px (10000)

        $this->assertSame($this->image, $result);
        $this->assertEquals(10000, $this->image->getWidth()); // Capped at max from config
    }

    public function test_get_width_returns_width(): void
    {
        $this->image->setWidth(800);

        $this->assertEquals(800, $this->image->getWidth());
    }

    public function test_set_height_with_valid_value(): void
    {
        $result = $this->image->setHeight(400);

        $this->assertSame($this->image, $result);
        $this->assertEquals(400, $this->image->getHeight());
    }

    public function test_set_height_with_zero_uses_one(): void
    {
        $result = $this->image->setHeight(0);

        $this->assertSame($this->image, $result);
        $this->assertEquals(1, $this->image->getHeight());
    }

    public function test_set_height_with_negative_value_uses_absolute(): void
    {
        $result = $this->image->setHeight(-200);

        $this->assertSame($this->image, $result);
        $this->assertEquals(200, $this->image->getHeight());
    }

    public function test_set_height_exceeds_max_dimension(): void
    {
        $result = $this->image->setHeight(15000); // Exceeds max_dim_px (10000)

        $this->assertSame($this->image, $result);
        $this->assertEquals(10000, $this->image->getHeight()); // Capped at max from config
    }

    public function test_get_height_returns_height(): void
    {
        $this->image->setHeight(600);

        $this->assertEquals(600, $this->image->getHeight());
    }

    public function test_set_gravity_with_valid_value(): void
    {
        $result = $this->image->setGravity('center');

        $this->assertSame($this->image, $result);
        $this->assertEquals('center', $this->image->getGravity());
    }

    public function test_set_gravity_with_invalid_value_uses_default(): void
    {
        $result = $this->image->setGravity('invalid-gravity');

        $this->assertSame($this->image, $result);
        $this->assertEquals('no', $this->image->getGravity()); // Default value
    }

    public function test_set_gravity_with_null_uses_default(): void
    {
        $result = $this->image->setGravity(null);

        $this->assertSame($this->image, $result);
        $this->assertEquals('no', $this->image->getGravity()); // Default value
    }

    public function test_set_gravity_is_case_insensitive(): void
    {
        $result = $this->image->setGravity('CENTER');

        $this->assertSame($this->image, $result);
        $this->assertEquals('center', $this->image->getGravity());
    }

    public function test_get_gravity_returns_gravity(): void
    {
        $this->image->setGravity('top');

        $this->assertEquals('top', $this->image->getGravity());
    }

    public function test_set_enlarge_with_valid_value(): void
    {
        $result = $this->image->setEnlarge(3);

        $this->assertSame($this->image, $result);
        $this->assertEquals(3, $this->image->getEnlarge());
    }

    public function test_set_enlarge_with_zero(): void
    {
        $result = $this->image->setEnlarge(0);

        $this->assertSame($this->image, $result);
        $this->assertEquals(0, $this->image->getEnlarge());
    }

    public function test_set_enlarge_with_negative_value_uses_absolute(): void
    {
        $result = $this->image->setEnlarge(-2);

        $this->assertSame($this->image, $result);
        $this->assertEquals(2, $this->image->getEnlarge());
    }

    public function test_set_enlarge_exceeds_max_enlarge(): void
    {
        $result = $this->image->setEnlarge(10); // Exceeds MAX_ENLARGE (5)

        $this->assertSame($this->image, $result);
        $this->assertEquals(5, $this->image->getEnlarge()); // Capped at max
    }

    public function test_get_enlarge_returns_enlarge(): void
    {
        $this->image->setEnlarge(4);

        $this->assertEquals(4, $this->image->getEnlarge());
    }

    public function test_set_extension_with_valid_value(): void
    {
        $result = $this->image->setExtension('png');

        $this->assertSame($this->image, $result);
        $this->assertEquals('png', $this->image->getExtension());
    }

    public function test_set_extension_with_null_uses_default(): void
    {
        $result = $this->image->setExtension(null);

        $this->assertSame($this->image, $result);
        $this->assertEquals('jpg', $this->image->getExtension()); // Uses default from config
    }

    public function test_set_extension_with_false(): void
    {
        $result = $this->image->setExtension(false);

        $this->assertSame($this->image, $result);
        $this->assertEquals('', $this->image->getExtension()); // Empty string when false
    }

    public function test_set_extension_with_invalid_format_throws_exception(): void
    {
        $this->expectException(Exception::class);

        $this->image->setExtension('invalid');
    }

    public function test_set_extension_is_case_insensitive(): void
    {
        $result = $this->image->setExtension('PNG');

        $this->assertSame($this->image, $result);
        $this->assertEquals('png', $this->image->getExtension());
    }

    public function test_get_extension_returns_extension(): void
    {
        $this->image->setExtension('webp');

        $this->assertEquals('webp', $this->image->getExtension());
    }

    public function test_set_original_picture_url_sets_url(): void
    {
        $result = $this->image->setOriginalPictureUrl('https://example.com/image.jpg');

        $this->assertSame($this->image, $result);
        $this->assertEquals('https://example.com/image.jpg', $this->image->getOriginalPictureUrl());
    }

    public function test_get_original_picture_url_returns_url(): void
    {
        $this->image->setOriginalPictureUrl('/local/path/image.png');

        $this->assertEquals('/local/path/image.png', $this->image->getOriginalPictureUrl());
    }

    public function test_constants_are_defined(): void
    {
        $this->assertEquals('fit', Image::DEFAULT_RESIZE);
        $this->assertEquals('no', Image::DEFAULT_GRAVITY);
        $this->assertEquals(5, Image::MAX_ENLARGE);
        $this->assertEquals(0, Image::MIN_ENLARGE);
    }

    public function test_chaining_methods(): void
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

    public function test_make_method_with_all_parameters(): void
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

    public function test_make_preset_method_with_all_parameters(): void
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
