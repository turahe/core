<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use League\Fractal\TransformerAbstract;
use Turahe\Core\Repositories\BaseRepository;
use Turahe\Core\Tests\TestCase;
use Turahe\Core\Tests\Models\User;

class BaseRepositoryTest extends TestCase
{
    private BaseRepository $repository;
    private User $model;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->model = new User();
        $this->repository = new class($this->model) extends BaseRepository {
            // Concrete implementation for testing
        };
        
        Cache::flush();
    }

    public function test_constructor_initializes_correctly(): void
    {
        $this->assertInstanceOf(User::class, $this->repository->getModel());
        $this->assertEquals(3600, $this->repository->getCacheTtl());
    }

    public function test_get_model_returns_correct_model(): void
    {
        $model = $this->repository->getModel();
        
        $this->assertInstanceOf(User::class, $model);
        $this->assertSame($this->model, $model);
    }

    public function test_set_cache_ttl_updates_value(): void
    {
        $newTtl = 7200;
        
        $result = $this->repository->setCacheTtl($newTtl);
        
        $this->assertSame($this->repository, $result);
        $this->assertEquals($newTtl, $this->repository->getCacheTtl());
    }

    public function test_get_all_without_cache(): void
    {
        User::create(['name' => 'User 1', 'email' => 'user1@test.com']);
        User::create(['name' => 'User 2', 'email' => 'user2@test.com']);
        
        $result = $this->repository->getAll('name', 'asc', false);
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $result);
        $this->assertGreaterThan(0, $result->count());
    }

    public function test_find_by_id_without_cache(): void
    {
        $user = User::create(['name' => 'Test User', 'email' => 'test@example.com']);
        
        $result = $this->repository->findById($user->id, false);
        
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id, $result->id);
    }

    public function test_find_by_id_returns_null_for_nonexistent(): void
    {
        $result = $this->repository->findById(99999, false);
        
        $this->assertNull($result);
    }

    public function test_clear_cache(): void
    {
        $this->repository->setCacheTtl(1800);
        $this->repository->getAll('name', 'asc', true);
        
        $result = $this->repository->clearCache();
        
        $this->assertTrue($result);
    }
}
