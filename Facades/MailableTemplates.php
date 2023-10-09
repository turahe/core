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

namespace Modules\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Modules\Core\MailableTemplate\MailableTemplatesManager;

/**
 * @method static static register(string|array $mailable)
 * @method static \Illuminate\Support\Collection get()
 * @method static void seed(string $locale, string $mailable = null)
 * @method static bool requiresSeeding()
 * @method static \Illuminate\Support\Collection getMailableTemplates()
 * @method static void seedIfRequired()
 * @method static static flushCache()
 * @method static static flush()
 *
 * @mixin \Modules\Core\MailableTemplate\MailableTemplatesManager
 */
class MailableTemplates extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return MailableTemplatesManager::class;
    }
}
