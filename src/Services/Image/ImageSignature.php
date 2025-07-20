<?php

declare(strict_types=1);

namespace Turahe\Core\Services\Image;

use Illuminate\Support\Str;
use InvalidArgumentException;
use Turahe\Core\Contracts\ImageSignatureInterface;

class ImageSignature implements ImageSignatureInterface
{
    /**
     * @var Image
     */
    private $img;

    /**
     * @var int
     */
    private $signature_size = null;

    /**
     * TODO ImageInterface
     */
    public function __construct($img)
    {
        if (config('core.imgproxy.signature_size')) {
            $signature_size = config('core.imgproxy.signature_size');
            if (is_numeric($signature_size)) {
                $this->signature_size = (int) $signature_size;
            }
        }
        $this->img = $img;
    }

    /**
     * Take picture signature
     * [domain]/signature.jpg is the resized image path
     */
    public function take(): string
    {
        $path = $this->getPath();
        $signature = hash_hmac(
            'sha256',
            $this->getBinarySalt().$path,
            $this->getBinaryKey(),
            true
        );
        if ($this->signature_size) {
            $signature = pack('A'.$this->signature_size, $signature);
        }

        $signature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        return sprintf('/%s%s', $signature, $path);
    }
    // below are the methods used by take()

    public function getKey(): string
    {
        if (empty($key = config('core.imgproxy.key'))) {
            throw new InvalidArgumentException;
        }

        if (Str::length($key) < 32) {
            throw new InvalidArgumentException;
        }

        return $key;
    }

    /**
     * @return mixed
     */
    public function getSalt(): string
    {
        if (empty($salt = config('core.imgproxy.salt'))) {
            throw new InvalidArgumentException;
        }

        if (Str::length($salt) < 32) {
            throw new InvalidArgumentException;
        }

        return $salt;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getBinaryKey(): string
    {
        if (empty($keyBin = pack('H*', $this->getKey()))) {
            throw new InvalidArgumentException('Key expected to be hex-encoded string');
        }

        return $keyBin;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getBinarySalt(): string
    {
        if (empty($saltBin = pack('H*', $this->getSalt()))) {
            throw new InvalidArgumentException('Salt expected to be hex-encoded string');
        }

        return $saltBin;
    }

    public function getEncodedUrl(): string
    {
        return rtrim(strtr(base64_encode($this->img->getOriginalPictureUrl()), '+/', '-_'), '=');
    }

    public function getPath(): string
    {
        if ($this->img->getPreset()) {
            $path = sprintf(
                '/%s/%s%s',
                $this->img->getPreset(),
                $this->getEncodedURL(),
                $this->img->getExtension()
            );
        } else {
            $path = "/rs:{$this->img->getResize()}:{$this->img->getWidth()}:{$this->img->getHeight()}:{$this->img->getEnlarge()}/g:{$this->img->getGravity()}/{$this->getEncodedURL()}{$this->img->getExtension()}";
        }

        return $path;
    }
}
