<?php

declare(strict_types=1);

namespace Turahe\Core\Services\Image;

use Exception;
use Illuminate\Support\Str;

/**
 * Image Service Class
 *
 * Provides image processing and manipulation capabilities for the Turahe Core package.
 * This class handles image resizing, cropping, format conversion, and preset management
 * for integration with image proxy services.
 *
 * Features:
 * - Configurable image resizing with multiple algorithms
 * - Gravity-based cropping and positioning
 * - Image format conversion
 * - Preset-based image processing
 * - Enlarge/scale control
 * - URL generation for image proxy services
 */
class Image
{
    /** Default resize algorithm - fits image within dimensions while maintaining aspect ratio */
    const DEFAULT_RESIZE = 'fit';

    /** Default gravity position - north (top edge) for cropping */
    const DEFAULT_GRAVITY = 'no';

    /** Maximum allowed enlargement factor */
    const MAX_ENLARGE = 5;

    /** Minimum allowed enlargement factor */
    const MIN_ENLARGE = 0;

    /** The resize algorithm to use (fit, fill, etc.) */
    protected $resize;

    /** The target width for the image */
    protected $width;

    /** The target height for the image */
    protected $height;

    /** The gravity position for cropping (no, so, ea, we, etc.) */
    protected $gravity;

    /** The enlargement factor (0-5) */
    protected $enlarge;

    /** The target file extension for format conversion */
    protected $extension;

    /** The original image URL or path */
    protected $url;

    /** The preset name for predefined image processing */
    protected $preset;

    /**
     * Initialize image with custom dimensions and settings
     *
     * Sets up the most common resize settings with sensible defaults.
     * The image will be resized to fit within the specified dimensions
     * while maintaining aspect ratio.
     *
     * @param  string  $path  The image path or URL
     * @param  int  $width  The target width
     * @param  int  $height  The target height
     * @param  string|null  $extension  Optional target file extension
     * @return Image Returns self for method chaining
     */
    public function make(string $path, int $width, int $height, $extension = null)
    {
        $this->setOriginalPictureUrl($path)
            ->setWidth($width)
            ->setHeight($height)
            ->setResize('fit')
            ->setGravity('no')
            ->setEnlarge(0)
            // convert img to extension
            ->setExtension($extension);

        return $this;
    }

    /**
     * Initialize image with a predefined preset
     *
     * Sets up the image using a predefined preset configuration instead
     * of custom dimensions. This is useful for consistent image processing
     * across an application.
     *
     * @param  string  $path  The image path or URL
     * @param  string  $preset  The preset name to use
     * @param  string|null  $extension  Optional target file extension
     * @return Image Returns self for method chaining
     */
    public function makePreset(string $path, string $preset, $extension = null)
    {
        $this->setOriginalPictureUrl($path)
            ->setPreset($preset)
            ->setExtension($extension);

        return $this;
    }

    /**
     * Get the current preset name
     *
     * @return string|null The current preset name or null if not set
     */
    public function getPreset()
    {
        return $this->preset;
    }

    /**
     * Set the preset name for predefined image processing
     *
     * @param  string  $preset  The preset name to use
     * @return Image Returns self for method chaining
     */
    public function setPreset($preset)
    {
        $this->preset = $preset;

        return $this;
    }

    /**
     * Set the resize algorithm for image processing
     *
     * @param  string|null  $resize  The resize algorithm to use
     * @return Image Returns self for method chaining
     */
    public function setResize(?string $resize = null): self
    {
        // Cache config values to avoid repeated calls
        static $resizeValues = null;
        if ($resizeValues === null) {
            $resizeValues = config('core.imgproxy.resize_values');
        }

        $resize = Str::lower($resize);
        $this->resize = in_array($resize, $resizeValues, true) ? $resize : self::DEFAULT_RESIZE;

        return $this;
    }

    public function getResize(): string
    {
        return $this->resize;
    }

    /**
     * Set the target width for image processing
     *
     * @param  int  $width  The target width
     * @return Image Returns self for method chaining
     */
    public function setWidth(int $width = 1): self
    {
        // Cache config value to avoid repeated calls
        static $maxDimension = null;
        if ($maxDimension === null) {
            $maxDimension = config('core.imgproxy.max_dim_px');
        }

        $width = abs($width) ?: 1;
        $this->width = min($width, $maxDimension);

        return $this;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * Set the target height for image processing
     *
     * @param  int  $height  The target height
     * @return Image Returns self for method chaining
     */
    public function setHeight(int $height = 1): self
    {
        // Cache config value to avoid repeated calls
        static $maxDimension = null;
        if ($maxDimension === null) {
            $maxDimension = config('core.imgproxy.max_dim_px');
        }

        $height = abs($height) ?: 1;
        $this->height = min($height, $maxDimension);

        return $this;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * Set the gravity position for image cropping
     *
     * @param  string|null  $gravity  The gravity position
     * @return Image Returns self for method chaining
     */
    public function setGravity(?string $gravity = null): self
    {
        // Cache config values to avoid repeated calls
        static $gravityValues = null;
        if ($gravityValues === null) {
            $gravityValues = config('core.imgproxy.gravity_values');
        }

        $gravity = Str::lower($gravity);
        $this->gravity = in_array($gravity, $gravityValues) ? $gravity : self::DEFAULT_GRAVITY;

        return $this;
    }

    public function getGravity(): string
    {
        return $this->gravity;
    }

    /**
     * Set the enlargement factor for image processing
     *
     * @param  int  $enlarge  The enlargement factor
     * @return Image Returns self for method chaining
     */
    public function setEnlarge(int $enlarge = 0): self
    {
        $enlarge = abs($enlarge);
        $this->enlarge = min($enlarge, self::MAX_ENLARGE);

        return $this;
    }

    public function getEnlarge(): int
    {
        return $this->enlarge;
    }

    /**
     * Set the target file extension for image processing
     *
     * @param  string|false|null  $extension  The target file extension
     * @return Image Returns self for method chaining
     *
     * @throws Exception When invalid extension is provided
     */
    public function setExtension($extension): self
    {
        // Cache config values to avoid repeated calls
        static $defaultExtension = null;
        static $formats = null;

        if ($defaultExtension === null) {
            $defaultExtension = config('core.imgproxy.default_extension');
            $formats = config('core.imgproxy.formats');
        }

        if ($extension === null) {
            $extension = $defaultExtension;
        }

        if ($extension !== false) {
            $extension = Str::lower($extension);

            if (! in_array($extension, $formats)) {
                throw new Exception("Invalid extension: {$extension}");
            }
        }

        $this->extension = $extension ?: '';

        return $this;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    /**
     * @return Image
     */
    public function setOriginalPictureUrl(string $argument1)
    {
        $this->url = $argument1;

        return $this;
    }

    public function getOriginalPictureUrl(): string
    {
        return $this->url;
    }
}
