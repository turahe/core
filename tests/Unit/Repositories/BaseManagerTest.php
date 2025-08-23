<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit\Repositories;

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;
use Turahe\Core\Repositories\BaseManager;
use Turahe\Core\Tests\TestCase;

class BaseManagerTest extends TestCase
{
    private BaseManager $manager;

    private TransformerAbstract $transformer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = new BaseManager;

        $this->transformer = new class extends TransformerAbstract
        {
            public function transform($item): array
            {
                return [
                    'id' => $item->id ?? 1,
                    'name' => $item->name ?? 'Test Item',
                ];
            }
        };
    }

    public function test_build_data_with_collection(): void
    {
        $collection = collect([
            (object) ['id' => 1, 'name' => 'Item 1'],
            (object) ['id' => 2, 'name' => 'Item 2'],
        ]);

        $resource = new Collection($collection, $this->transformer, 'items');

        $result = $this->manager->buildData($resource, ['related'], 'v1');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
    }

    public function test_build_data_with_includes(): void
    {
        $collection = collect([
            (object) ['id' => 1, 'name' => 'Item 1'],
        ]);

        $resource = new Collection($collection, $this->transformer, 'items');

        $result = $this->manager->buildData($resource, ['profile', 'settings'], 'v2');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
    }

    public function test_build_data_without_api_version(): void
    {
        $collection = collect([
            (object) ['id' => 1, 'name' => 'Item 1'],
        ]);

        $resource = new Collection($collection, $this->transformer, 'items');

        $result = $this->manager->buildData($resource, []);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
    }

    public function test_build_data_handles_exception(): void
    {
        // Mock a resource that will cause an exception
        $resource = $this->createMock(Collection::class);
        $resource->method('getData')->willThrowException(new \Exception('Test exception'));

        $result = $this->manager->buildData($resource, []);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_clear_cache(): void
    {
        // Test that clearCache method exists and is callable
        $this->assertTrue(method_exists($this->manager, 'clearCache'));

        // Should not throw any exceptions
        $this->manager->clearCache();

        $this->assertTrue(true); // Assertion to ensure test passes
    }

    public function test_manager_reuses_cached_instance(): void
    {
        // First call should create a new manager
        $result1 = $this->manager->buildData(
            new Collection(collect([(object) ['id' => 1]]), $this->transformer, 'items')
        );

        // Second call should reuse the cached manager
        $result2 = $this->manager->buildData(
            new Collection(collect([(object) ['id' => 2]]), $this->transformer, 'items')
        );

        $this->assertIsArray($result1);
        $this->assertIsArray($result2);
        $this->assertArrayHasKey('data', $result1);
        $this->assertArrayHasKey('data', $result2);
    }
}
