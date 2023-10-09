<?php
/*
 * This source code is the proprietary and confidential information of
 * Nur Wachid. You may not disclose, copy, distribute,
 *  or use this code without the express written permission of
 * Nur Wachid.
 *
 * Copyright (c) 2022-2023.
 *
 *
 */

namespace Modules\Core\Google\Services;

use Illuminate\Support\Collection;

class MessageCollection extends Collection
{
    protected static ?string $pageToken = null;

    protected static ?string $prevPageToken = null;

    protected static ?int $resultSizeEstimate = null;

    protected static ?Message $service = null;

    public function setResultSizeEstimate(int $resultSizeEstimate)
    {
        static::$resultSizeEstimate = $resultSizeEstimate;

        return $this;
    }

    public function getResultSizeEstimate(): int
    {
        return static::$resultSizeEstimate;
    }

    public function setNextPageToken(?string $token): static
    {
        static::$prevPageToken = static::$pageToken;
        static::$pageToken = $token;

        return $this;
    }

    public function getNextPageToken(): ?string
    {
        return static::$pageToken;
    }

    public function getPrevPageToken(): ?string
    {
        return static::$prevPageToken;
    }

    public function getNextPageResults(): bool|static
    {
        if (! $token = $this->getNextPageToken()) {
            return false;
        }

        return static::$service->all($token);
    }

    public function setMessageService(Message $service): static
    {
        static::$service = $service;

        return $this;
    }
}
