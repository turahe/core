<?php

namespace Turahe\Core\Facades;

use Illuminate\Support\Facades\Facade;
use Turahe\Core\Microsoft\Client;

/**
 * @method static static connectUsing(string|\Turahe\Core\OAuth\AccessTokenProvider)
 * @method static \Microsoft\Graph\Http\GraphRequest createGetRequest(string $endpoint)
 * @method static \Microsoft\Graph\Http\GraphRequest createPostRequest(string $endpoint, null|string $body)
 * @method static \Microsoft\Graph\Http\GraphRequest createPutRequest(string $endpoint, null|string $body)
 * @method static \Microsoft\Graph\Http\GraphRequest createPatchRequest(string $endpoint, null|string $body)
 * @method static \Microsoft\Graph\Http\GraphRequest createDeleteRequest(string $endpoint)
 * @method static \Microsoft\Graph\Http\GraphCollectionRequest createCollectionGetRequest(string $endpoint)
 * @method static \Turahe\Core\Microsoft\Services\Batch\Request createBatchRequest(\Turahe\Core\Microsoft\Services\Batch\BatchRequests $requests)
 * @method static array iterateCollectionRequest(\Microsoft\Graph\Http\GraphCollectionRequest $collection)
 * @method static string getApiVersion()
 * @method static string setApiVersion(string $version)
 *
 * @see \Turahe\Core\Microsoft\Client
 */
class MsGraph extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return Client::class;
    }
}
