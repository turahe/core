<?php

declare(strict_types=1);

namespace Turahe\Core\Repositories;

use League\Fractal\Manager;
use League\Fractal\Serializer\JsonApiSerializer;

/**
 * Base Manager Class
 *
 * Handles Fractal transformation and API response building.
 * Optimized to reuse Manager instances and provide better error handling.
 */
class BaseManager
{
    /**
     * Cached manager instance for better performance
     */
    private static ?Manager $cachedManager = null;

    /**
     * Build data using Fractal transformation
     *
     * @param  mixed  $resource  The resource to transform
     * @param  array  $includes  Array of related data to include
     * @param  string|null  $apiVer  API version for the response
     * @return array Transformed data
     */
    public function buildData($resource, array $includes = [], ?string $apiVer = null): array
    {
        try {
            $manager = $this->getManager($apiVer);
            $manager->parseIncludes($includes);

            return $manager->createData($resource)->toArray();
        } catch (\Exception $e) {
            // Log error and return empty array as fallback
            \Log::error('Fractal transformation failed: '.$e->getMessage());

            return [];
        }
    }

    /**
     * Get or create a Fractal manager instance
     *
     * @param  string|null  $apiVer  API version
     */
    private function getManager(?string $apiVer): Manager
    {
        if (self::$cachedManager === null) {
            self::$cachedManager = new Manager;
        }

        $manager = clone self::$cachedManager;
        $manager->setSerializer(new JsonApiSerializer(config('app.url').($apiVer ?? '')));

        return $manager;
    }

    /**
     * Clear cached manager instance
     */
    public static function clearCache(): void
    {
        self::$cachedManager = null;
    }
}
