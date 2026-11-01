<?php

declare(strict_types=1);
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

namespace Turahe\Core\Google\Concerns;

use Google\Service\Gmail\MessagePart;
use Illuminate\Support\Collection;

trait HasParts
{
    /**
     * @var Collection
     */
    protected $allParts;

    /**
     * Find all Parts of a message.
     *
     * Necessary to reset the $allParts variable.
     *
     * @return Collection
     */
    protected function getAllParts(Collection $partsContainer)
    {
        $this->iterateParts($partsContainer);

        return new Collection($this->allParts);
    }

    /**
     * Recursive Method. Iterates through a collection,
     * finding all 'parts'.
     *
     * @return bool
     */
    protected function iterateParts(Collection $partsContainer, bool $returnOnFirstFound = false)
    {
        $parts = [];
        $plucked = $partsContainer->flatten()->filter();

        if ($plucked->count()) {
            $parts = $plucked;
        } else {
            if ($partsContainer->count()) {
                $parts = $partsContainer;
            }
        }

        if ($parts) {
            /** @var MessagePart $part */
            foreach ($parts as $part) {
                if ($part) {
                    if ($returnOnFirstFound) {
                        return true;
                    }

                    $this->allParts[$part->getPartId()] = $part;

                    $this->iterateParts(new Collection($part->getParts()));
                }
            }
        }
    }
}
