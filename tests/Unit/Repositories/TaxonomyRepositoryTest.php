<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit\Repositories;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Turahe\Core\Models\Taxonomy;
use Turahe\Core\Repositories\TaxonomyRepository;
use Turahe\Core\Tests\TestCase;

class TaxonomyRepositoryTest extends TestCase
{
    private TaxonomyRepository $repository;
    private Taxonomy $taxonomy;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->taxonomy = new Taxonomy();
        $this->repository = new TaxonomyRepository($this->taxonomy);
    }

    public function test_get_taxonomies_builder(): void
    {
        $result = $this->repository->getTaxonomiesBuilder('name', 'asc');
        
        $this->assertInstanceOf(Builder::class, $result);
    }

    public function test_get_taxonomies_returns_collection(): void
    {
        Taxonomy::create([
            'name' => 'Category 1',
            'code' => 'CAT1'
        ]);
        
        $result = $this->repository->getTaxonomies('created_at', 'desc');
        
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertGreaterThan(0, $result->count());
    }

    public function test_get_taxonomy_by_id(): void
    {
        $taxonomy = Taxonomy::create([
            'name' => 'Test Category',
            'code' => 'TEST'
        ]);
        
        $result = $this->repository->getTaxonomy($taxonomy->id);
        
        $this->assertInstanceOf(Taxonomy::class, $result);
        $this->assertEquals($taxonomy->id, $result->id);
    }

    public function test_create_taxonomy(): void
    {
        $result = $this->repository->createTaxonomy('New Category', 'NEW', null, 'Description');
        
        $this->assertInstanceOf(Taxonomy::class, $result);
        $this->assertEquals('New Category', $result->name);
        $this->assertEquals('NEW', $result->code);
    }

    public function test_create_taxonomies_with_string(): void
    {
        $result = $this->repository->createTaxonomies('Category1|Category2');
        
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(2, $result->count());
    }

    public function test_update_taxonomy(): void
    {
        $taxonomy = Taxonomy::create([
            'name' => 'Original Name',
            'code' => 'ORIGINAL'
        ]);
        
        $this->repository = new TaxonomyRepository($taxonomy);
        
        $result = $this->repository->updateTaxonomy('Updated Name', 'UPDATED');
        
        $this->assertTrue($result);
    }

    public function test_delete_taxonomy(): void
    {
        $taxonomy = Taxonomy::create([
            'name' => 'To Delete',
            'code' => 'DELETE'
        ]);
        
        $this->repository = new TaxonomyRepository($taxonomy);
        
        $result = $this->repository->deleteTaxonomy();
        
        $this->assertTrue($result);
    }
}
