<?php

declare(strict_types=1);

namespace Turahe\Core\Repositories;

use League\Fractal\Manager;
use League\Fractal\Serializer\JsonApiSerializer;

class BaseManager
{
    public function buildData($resource, array $includes = [], ?string $apiVer = null): array
    {
        $manager = new Manager;
        $manager->setSerializer(new JsonApiSerializer(config('app.url').$apiVer));
        $manager->parseIncludes($includes);

        return $manager->createData($resource)->toArray();
    }
}
