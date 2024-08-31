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

namespace Turahe\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Turahe\Core\Fields\FieldsManager;

/**
 * @method static static group(string $group, mixed $provider)
 * @method static static add(string $group, mixed $provider)
 * @method static static replace(string $group, mixed $provider)
 * @method static bool has(string $group)
 * @method static \Turahe\Core\Fields\FieldsCollection resolve(string $group, string $view)
 * @method static \Turahe\Core\Fields\FieldsCollection resolveForDisplay(string $group, string $view)
 * @method static \Turahe\Core\Fields\FieldsCollection resolveCreateFieldsForDisplay(string $group)
 * @method static \Turahe\Core\Fields\FieldsCollection resolveUpdateFieldsForDisplay(string $group)
 * @method static \Turahe\Core\Fields\FieldsCollection resolveDetailFieldsForDisplay(string $group)
 * @method static \Turahe\Core\Fields\FieldsCollection resolveCreateFields(string $group)
 * @method static \Turahe\Core\Fields\FieldsCollection resolveUpdateFields(string $group)
 * @method static \Turahe\Core\Fields\FieldsCollection resolveDetailFields(string $group)
 * @method static \Turahe\Core\Fields\FieldsCollection resolveAndAuthorize(string $group, ?string $view = null)
 * @method static \Turahe\Core\Fields\FieldsCollection resolveForSettings(string $group, string $view)
 * @method static \Turahe\Core\Fields\FieldsCollection inGroup(string $group, ?string $view = null)
 * @method static void customize(mixed $data, string $group, string $view)
 * @method static array customized(string $group, string $view, ?string $attribute = null)
 * @method static void flushLoadedCache()
 * @method static void flushRegisteredCache()
 * @method static \Illuminate\Support\Collection customFieldable()
 * @method static array getOptionableCustomFieldsTypes()
 * @method static array getNonOptionableCustomFieldsTypes()
 * @method static array customFieldsTypes()
 * @method static \Illuminate\Support\Collection getCustomFieldsForResource(string $resourceName)
 *
 * @mixin \Turahe\Core\Fields\FieldsManager
 */
class Fields extends Facade
{
    /**
     * The create view name
     */
    const CREATE_VIEW = 'create';

    /**
     * The update view name
     */
    const UPDATE_VIEW = 'update';

    /**
     * The detail view name
     */
    const DETAIL_VIEW = 'detail';

    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return FieldsManager::class;
    }
}
