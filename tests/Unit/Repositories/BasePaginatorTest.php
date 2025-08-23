<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;
use Turahe\Core\Repositories\BasePaginator;
use Turahe\Core\Tests\TestCase;

class BasePaginatorTest extends TestCase
{
    private BasePaginator $paginator;
    private TransformerAbstract $transformer;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->paginator = new BasePaginator();
        
        $this->transformer = new class extends TransformerAbstract {
            public function transform($item): array
            {
                return [
                    'id' => $item->id ?? 1,
                    'name' => $item->name ?? 'Test Item'
                ];
            }
        };
    }

    public function test_paginate_creates_collection_resource(): void
    {
        $items = collect([
            (object)['id' => 1, 'name' => 'Item 1'],
            (object)['id' => 2, 'name' => 'Item 2']
        ]);
        
        $lengthAwarePaginator = new LengthAwarePaginator($items, 2, 10, 1);
        
        $result = $this->paginator->paginate($lengthAwarePaginator, $this->transformer, 'items');
        
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals('items', $result->getResourceKey());
    }

    public function test_paginate_sets_paginator_adapter(): void
    {
        $items = collect([
            (object)['id' => 1, 'name' => 'Item 1']
        ]);
        
        $lengthAwarePaginator = new LengthAwarePaginator($items, 1, 10, 1);
        
        $result = $this->paginator->paginate($lengthAwarePaginator, $this->transformer, 'items');
        
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertNotNull($result->getPaginator());
    }

    public function test_paginate_with_empty_collection(): void
    {
        $items = collect([]);
        
        $lengthAwarePaginator = new LengthAwarePaginator($items, 0, 10, 1);
        
        $result = $this->paginator->paginate($lengthAwarePaginator, $this->transformer, 'items');
        
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals('items', $result->getResourceKey());
    }

    public function test_simple_collection_creates_resource(): void
    {
        $items = collect([
            (object)['id' => 1, 'name' => 'Item 1'],
            (object)['id' => 2, 'name' => 'Item 2']
        ]);
        
        $result = $this->paginator->simpleCollection($items, $this->transformer, 'items');
        
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals('items', $result->getResourceKey());
        $this->assertNull($result->getPaginator()); // Simple collection has no pagination
    }

    public function test_simple_collection_with_empty_items(): void
    {
        $items = collect([]);
        
        $result = $this->paginator->simpleCollection($items, $this->transformer, 'items');
        
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals('items', $result->getResourceKey());
    }

    public function test_paginate_handles_exception(): void
    {
        // Create a mock paginator that will cause an exception
        $mockPaginator = $this->createMock(LengthAwarePaginator::class);
        $mockPaginator->method('getCollection')->willThrowException(new \Exception('Test exception'));
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Test exception');
        
        $this->paginator->paginate($mockPaginator, $this->transformer, 'items');
    }

    public function test_paginate_with_different_resource_keys(): void
    {
        $items = collect([
            (object)['id' => 1, 'name' => 'Item 1']
        ]);
        
        $lengthAwarePaginator = new LengthAwarePaginator($items, 1, 10, 1);
        
        $result1 = $this->paginator->paginate($lengthAwarePaginator, $this->transformer, 'users');
        $result2 = $this->paginator->paginate($lengthAwarePaginator, $this->transformer, 'posts');
        
        $this->assertEquals('users', $result1->getResourceKey());
        $this->assertEquals('posts', $result2->getResourceKey());
    }

    public function test_simple_collection_with_different_resource_keys(): void
    {
        $items = collect([
            (object)['id' => 1, 'name' => 'Item 1']
        ]);
        
        $result1 = $this->paginator->simpleCollection($items, $this->transformer, 'categories');
        $result2 = $this->paginator->simpleCollection($items, $this->transformer, 'tags');
        
        $this->assertEquals('categories', $result1->getResourceKey());
        $this->assertEquals('tags', $result2->getResourceKey());
    }
}
