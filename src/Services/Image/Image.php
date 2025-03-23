<?php

declare(strict_types=1);

namespace Turahe\Core\Services\Image;

use Exception;
use Illuminate\Support\Str;

class Image
{
    const DEFAULT_RESIZE = 'fit';

    /**
     * north (top edge)
     */
    const DEFAULT_GRAVITY = 'no';

    const MAX_ENLARGE = 5;

    const MIN_ENLARGE = 0;

    /**
     * @var string
     */
    protected $resize;

    /**
     * @var int
     */
    protected $width;

    /**
     * @var int
     */
    protected $height;

    /**
     * @var string
     */
    protected $gravity;

    /**
     * @var int
     */
    protected $enlarge;

    /**
     * @var string
     */
    protected $extension;

    /**
     * @var mixed
     */
    protected $url;

    /**
     * @var string
     */
    protected $preset;

    /**
     * Init most common resize settings. Later you can update defaults
     *
     * @param  null  $extension
     * @return Image
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

    public function makePreset(string $path, string $preset, $extension = null)
    {
        $this->setOriginalPictureUrl($path)
            ->setPreset($preset)
            ->setExtension($extension);

        return $this;
    }

    public function getPreset()
    {
        return $this->preset;
    }

    public function setPreset($preset)
    {
        $this->preset = $preset;

        return $this;
    }

    /**
     * @return mixed
     */
    public function setResize(?string $argument1 = null)
    {
        $argument1 = Str::lower($argument1);

        $this->resize = (! in_array($argument1, config('img-proxy.resize_values'), true))
            ? self::DEFAULT_RESIZE
            : $argument1;

        return $this;
    }

    public function getResize(): string
    {
        return $this->resize;
    }

    public function setWidth(int $argument1 = 1)
    {
        $argument1 = abs($argument1) ?: 1;
        if ($argument1 > config('img-proxy.max_dim_px')) {
            $argument1 = config('img-proxy.max_dim_px');
        }
        $this->width = $argument1;

        return $this;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setHeight(int $argument1 = 1)
    {
        $argument1 = abs($argument1) ?: 1;
        if ($argument1 > config('img-proxy.max_dim_px')) {
            $argument1 = config('img-proxy.max_dim_px');
        }
        $this->height = $argument1;

        return $this;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @return mixed
     */
    public function setGravity(?string $argument1 = null)
    {
        $argument1 = Str::lower($argument1);
        $this->gravity = (! in_array($argument1, config('img-proxy.gravity_values')))
            ? self::DEFAULT_GRAVITY
            : $argument1;

        return $this;
    }

    public function getGravity(): string
    {
        return $this->gravity;
    }

    /**
     * @return mixed
     */
    public function setEnlarge(int $argument1 = 0)
    {
        $argument1 = abs($argument1);
        if ($argument1 > self::MAX_ENLARGE) {
            $argument1 = self::MAX_ENLARGE;
        }
        $this->enlarge = $argument1;

        return $this;
    }

    public function getEnlarge(): int
    {
        return $this->enlarge;
    }

    public function setExtension($argument1)
    {
        if (is_null($argument1)) {
            $argument1 = config('img-proxy.default_extension');
        }
        if ($argument1 !== false) {
            $argument1 = Str::lower($argument1);

            if (! in_array($argument1, config('img-proxy.formats'))) {
                throw new Exception($argument1);
            }
        }
        if (! $argument1) {
            $argument1 = '';
        }
        $this->extension = $argument1;

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
