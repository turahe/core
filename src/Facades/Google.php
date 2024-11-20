<?php

namespace Turahe\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Turahe\Core\Google\Client;

/**
 * @method static static connectUsing(\Turahe\Core\OAuth\AccessTokenProvider|string)
 * @method static \Turahe\Core\Google\Services\Message message()
 * @method static \Turahe\Core\Google\Services\Labels labels()
 * @method static \Turahe\Core\Google\Services\History history()
 * @method static \Turahe\Core\Google\Services\Calendar calendar()
 * @method static void revokeToken(?string $accessToken = null)
 * @method static \Google\Client getClient()
 */
class Google extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return Client::class;
    }
}
