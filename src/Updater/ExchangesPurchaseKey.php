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

namespace Turahe\Core\Updater;

trait ExchangesPurchaseKey
{
    protected ?string $purchaseKey = null;

    /**
     * Use the given custom purchase key.
     */
    public function usePurchaseKey(string $key): static
    {
        $this->purchaseKey = $key;

        return $this;
    }

    /**
     * Get the updater purchase key.
     */
    public function getPurchaseKey(): ?string
    {
        return $this->purchaseKey ?: $this->config['purchase_key'];
    }
}
