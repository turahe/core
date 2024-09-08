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

namespace Turahe\Core\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Turahe\Core\Media\HasMedia;

/** @mixin \Turahe\Core\Models\Model */
trait HasAvatar
{
    use HasMedia;

    /**
     * Get Gravatar URL.
     */
    public function getGravatarUrl(?string $email = null, string|int $size = '40'): string
    {
        $email ??= $this->email ?? '';

        return 'https://www.gravatar.com/avatar/'.md5(strtolower($email)).'?s='.$size;
    }

    /**
     * Get the model avatar URL.
     */
    public function avatarUrl(): Attribute
    {
        return Attribute::get(function () {
            if (is_null($this->hasMedia('avatar'))) {
                return $this->getGravatarUrl();
            }

            return $this->getFirstMediaUrl('avatar');
        });
    }

    /**
     * Get the actual uploaded path URL for src image.
     */
    public function uploadedAvatarUrl(): Attribute
    {
        return Attribute::get(function () {
            if (is_null($this->hasMedia('avatar'))) {
                return null;
            }

            return $this->getFirstMediaUrl('avatar');
        });
    }
}
