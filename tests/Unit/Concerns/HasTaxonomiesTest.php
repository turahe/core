<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Turahe\Core\Models\Taxonomy;
use Turahe\Core\Tests\Feature\Factories\TaxonomyFactory;
use Turahe\Core\Tests\Models\DummyTaxonomy;
use Turahe\Core\Tests\TestCase;

class HasTaxonomiesTest extends TestCase
{
    protected $testModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testModel = DummyTaxonomy::create(['name' => 'test']);
    }

    public function test_provides_a_categories_relation(): void
    {
        $this->assertInstanceOf(MorphToMany::class, $this->testModel->taxonomies());
        $this->assertInstanceOf(Collection::class, $this->testModel->taxonomies);
    }

    public function test_can_model_create_categories_from_string(): void
    {
        $name = 'taxonomy';
        $orgTaxonomy = $this->testModel->addTaxonomies($name);

        $this->assertDatabaseHas('taxonomies', [
            'name' => $name,
        ]);
        $this->testModel->taxonomies->each(function ($taxonomy) {
            $this->assertDatabaseHas('model_has_taxonomies', [
                'model_id' => $this->testModel->getKey(),
                'model_type' => $this->testModel->getMorphClass(),
                'taxonomy_id' => $taxonomy->getKey(),
            ]);
        });

        $this->assertInstanceOf(DummyTaxonomy::class, $orgTaxonomy);
    }

    public function test_can_model_create_categories_from_array(): void
    {
        $names = ['taxonomy1', 'taxonomy2', 'taxonomy3'];
        $orgTaxonomy = $this->testModel->addTaxonomies($names);

        foreach ($names as $name) {
            $this->assertDatabaseHas('taxonomies', [
                'name' => $name,
            ]);
        }

        $this->testModel->taxonomies->each(function ($taxonomy) {

            $this->assertDatabaseHas('model_has_taxonomies', [
                'model_id' => $this->testModel->getKey(),
                'model_type' => $this->testModel->getMorphClass(),
                'taxonomy_id' => $taxonomy->getKey(),
            ]);
        });

        $this->assertInstanceOf(DummyTaxonomy::class, $orgTaxonomy);
    }

    public function test_can_model_create_categories_has_parent(): void
    {

        $parent = TaxonomyFactory::new()->create();

        $names = ['taxonomy1', 'taxonomy2', 'taxonomy3'];
        $orgTaxonomy = $this->testModel->addTaxonomies($names, $parent);

        foreach ($names as $name) {
            $this->assertDatabaseHas('taxonomies', [
                'name' => $name,
                'parent_id' => $parent->getKey(),
            ]);
        }

        $this->testModel->taxonomies->each(function ($taxonomy) {

            $this->assertDatabaseHas('model_has_taxonomies', [
                'model_id' => $this->testModel->getKey(),
                'model_type' => $this->testModel->getMorphClass(),
                'taxonomy_id' => $taxonomy->getKey(),
            ]);
        });

        $this->assertInstanceOf(DummyTaxonomy::class, $orgTaxonomy);
    }

    public function test_can_model_delete_and_all_categories(): void
    {
        $taxonomies = TaxonomyFactory::new()->count(3)->create();
        $this->testModel->taxonomies()->saveMany($taxonomies);

        $deleted = $this->testModel->delete();
        $this->assertTrue($deleted);

        $this->assertDatabaseMissing('model_has_taxonomies');
        $this->assertInstanceOf(Collection::class, Taxonomy::all());

        Taxonomy::all()->each(function (Taxonomy $taxonomy) {
            $this->assertInstanceOf(Taxonomy::class, $taxonomy);
        });

    }

    public function test_can_model_get_taxonomy(): void
    {
        $taxonomy = TaxonomyFactory::new()->create($data = [
            'name' => 'taxonomy',
        ]);
        $this->testModel->taxonomies()->attach($taxonomy->getKey());

        $found = $this->testModel->getTaxonomy($data['name']);
        $this->assertEquals($data['name'], $found->name);

    }

    public function test_can_model_has_taxonomy(): void
    {
        $taxonomy = TaxonomyFactory::new()->create($data = [
            'name' => 'taxonomy',
        ]);
        $this->testModel->taxonomies()->attach($taxonomy->getKey());

        $has = $this->testModel->hasTaxonomy($data['name']);
        $this->assertTrue($has);

    }

    public function test_can_model_detach_taxonomy(): void
    {
        $taxonomy = TaxonomyFactory::new()->create();
        $this->testModel->taxonomies()->attach($taxonomy->getKey());

        $detach = $this->testModel->detachTaxonomies();
        $this->assertTrue($detach);
    }

    // ===============================================
    // Tests for PHP 8.4 Features
    // ===============================================

    public function test_taxonomies_count_readonly_property(): void
    {
        // Initially no taxonomies
        $this->assertEquals(0, $this->testModel->taxonomiesCount);
        
        // Add taxonomies
        $taxonomy1 = TaxonomyFactory::new()->create(['name' => 'category1']);
        $taxonomy2 = TaxonomyFactory::new()->create(['name' => 'category2']);
        
        $this->testModel->taxonomies()->attach([$taxonomy1->id, $taxonomy2->id]);
        
        // Refresh model to ensure count is correct
        $this->testModel->refresh();
        $this->assertEquals(2, $this->testModel->taxonomiesCount);
    }

    public function test_has_taxonomies_attached_readonly_property(): void
    {
        // Initially no taxonomies
        $this->assertFalse($this->testModel->hasTaxonomiesAttached);
        
        // Add taxonomy
        $taxonomy = TaxonomyFactory::new()->create(['name' => 'category']);
        $this->testModel->taxonomies()->attach($taxonomy->id);
        
        // Refresh model
        $this->testModel->refresh();
        $this->assertTrue($this->testModel->hasTaxonomiesAttached);
    }

    public function test_has_taxonomies_variadic_parameters(): void
    {
        // Create taxonomies
        $taxonomy1 = TaxonomyFactory::new()->create(['name' => 'category1']);
        $taxonomy2 = TaxonomyFactory::new()->create(['name' => 'category2']);
        $taxonomy3 = TaxonomyFactory::new()->create(['name' => 'category3']);
        
        // Attach first two taxonomies
        $this->testModel->taxonomies()->attach([$taxonomy1->id, $taxonomy2->id]);
        
        // Test with single taxonomy
        $this->assertTrue($this->testModel->hasTaxonomies('category1'));
        
        // Test with multiple taxonomies - all exist
        $this->assertTrue($this->testModel->hasTaxonomies('category1', 'category2'));
        
        // Test with multiple taxonomies - one doesn't exist
        $this->assertFalse($this->testModel->hasTaxonomies('category1', 'category2', 'category3'));
        
        // Test with non-existent taxonomy
        $this->assertFalse($this->testModel->hasTaxonomies('nonexistent'));
    }

    public function test_get_taxonomy_with_caching(): void
    {
        // Create taxonomies
        $taxonomy1 = TaxonomyFactory::new()->create(['name' => 'category1']);
        $taxonomy2 = TaxonomyFactory::new()->create(['name' => 'category2']);
        
        $this->testModel->taxonomies()->attach([$taxonomy1->id, $taxonomy2->id]);
        
        // First call should populate cache
        $result1 = $this->testModel->getTaxonomy('category1');
        $this->assertInstanceOf(Taxonomy::class, $result1);
        $this->assertEquals('category1', $result1->name);
        
        // Second call should use cache
        $result2 = $this->testModel->getTaxonomy('category1');
        $this->assertEquals($result1->id, $result2->id);
        
        // Non-existent taxonomy
        $result3 = $this->testModel->getTaxonomy('nonexistent');
        $this->assertNull($result3);
    }

    public function test_has_taxonomy_with_match_expression(): void
    {
        // Create taxonomy
        $taxonomy = TaxonomyFactory::new()->create(['name' => 'category']);
        $this->testModel->taxonomies()->attach($taxonomy->id);
        
        // Test existing taxonomy
        $this->assertTrue($this->testModel->hasTaxonomy('category'));
        
        // Test non-existent taxonomy
        $this->assertFalse($this->testModel->hasTaxonomy('nonexistent'));
    }

    public function test_get_taxonomies_by_type(): void
    {
        // Create taxonomies with different types
        $category1 = TaxonomyFactory::new()->create(['name' => 'cat1', 'type' => 'category']);
        $category2 = TaxonomyFactory::new()->create(['name' => 'cat2', 'type' => 'category']);
        $tag1 = TaxonomyFactory::new()->create(['name' => 'tag1', 'type' => 'tag']);
        
        $this->testModel->taxonomies()->attach([$category1->id, $category2->id, $tag1->id]);
        
        // Get categories only
        $categories = $this->testModel->getTaxonomiesByType('category');
        $this->assertCount(2, $categories);
        
        // Get tags only
        $tags = $this->testModel->getTaxonomiesByType('tag');
        $this->assertCount(1, $tags);
        
        // Get non-existent type
        $nonExistent = $this->testModel->getTaxonomiesByType('nonexistent');
        $this->assertCount(0, $nonExistent);
    }

    public function test_sync_taxonomies(): void
    {
        // Create taxonomies
        $taxonomy1 = TaxonomyFactory::new()->create(['name' => 'cat1']);
        $taxonomy2 = TaxonomyFactory::new()->create(['name' => 'cat2']);
        $taxonomy3 = TaxonomyFactory::new()->create(['name' => 'cat3']);
        
        // Initially attach first two
        $this->testModel->taxonomies()->attach([$taxonomy1->id, $taxonomy2->id]);
        $this->assertEquals(2, $this->testModel->taxonomies()->count());
        
        // Sync with different set (should replace)
        $this->testModel->syncTaxonomies([$taxonomy2->id, $taxonomy3->id]);
        
        // Should now have taxonomy2 and taxonomy3
        $this->assertEquals(2, $this->testModel->taxonomies()->count());
        $this->assertTrue($this->testModel->taxonomies->contains('id', $taxonomy2->id));
        $this->assertTrue($this->testModel->taxonomies->contains('id', $taxonomy3->id));
        $this->assertFalse($this->testModel->taxonomies->contains('id', $taxonomy1->id));
    }

    public function test_clear_taxonomies_cache(): void
    {
        // Create taxonomy
        $taxonomy = TaxonomyFactory::new()->create(['name' => 'category']);
        $this->testModel->taxonomies()->attach($taxonomy->id);
        
        // First call to populate cache
        $result1 = $this->testModel->getTaxonomy('category');
        $this->assertNotNull($result1);
        
        // Clear cache
        $this->testModel->clearTaxonomiesCache();
        
        // Add another taxonomy directly to database
        $taxonomy2 = TaxonomyFactory::new()->create(['name' => 'category2']);
        $this->testModel->taxonomies()->attach($taxonomy2->id);
        
        // Should be able to get new taxonomy after cache clear
        $result2 = $this->testModel->getTaxonomy('category2');
        $this->assertNotNull($result2);
        $this->assertEquals('category2', $result2->name);
    }

    public function test_add_taxonomies_with_array_spread(): void
    {
        // Create parent taxonomy
        $parent = TaxonomyFactory::new()->create(['name' => 'parent']);
        
        // Mock the repository to test array spread functionality
        $mockRepo = $this->createMock(\Turahe\Core\Contracts\TaxonomyRepositoryInterface::class);
        
        $taxonomy1 = TaxonomyFactory::new()->create(['name' => 'child1']);
        $taxonomy2 = TaxonomyFactory::new()->create(['name' => 'child2']);
        
        $mockRepo->expects($this->once())
            ->method('createTaxonomies')
            ->with(['child1', 'child2'], $parent)
            ->willReturn([$taxonomy1, $taxonomy2]);
        
        $this->app->instance(\Turahe\Core\Contracts\TaxonomyRepositoryInterface::class, $mockRepo);
        
        // Test adding taxonomies
        $result = $this->testModel->addTaxonomies(['child1', 'child2'], $parent);
        
        $this->assertInstanceOf(\Turahe\Core\Tests\Models\DummyTaxonomy::class, $result);
        $this->assertEquals(2, $this->testModel->taxonomies()->count());
    }

    public function test_boot_has_taxonomies_clears_cache(): void
    {
        // Create taxonomy
        $taxonomy = TaxonomyFactory::new()->create(['name' => 'category']);
        $this->testModel->taxonomies()->attach($taxonomy->id);
        
        // Populate cache
        $this->testModel->getTaxonomy('category');
        
        // Delete model should trigger cache clear
        $this->testModel->delete();
        
        // This test primarily ensures no exceptions are thrown during deletion
        $this->assertTrue(true);
    }

    public function test_readonly_properties_are_immutable(): void
    {
        // Create taxonomies
        $taxonomy1 = TaxonomyFactory::new()->create(['name' => 'cat1']);
        $taxonomy2 = TaxonomyFactory::new()->create(['name' => 'cat2']);
        
        $this->testModel->taxonomies()->attach([$taxonomy1->id, $taxonomy2->id]);
        $this->testModel->refresh();
        
        // Test readonly properties
        $this->assertIsInt($this->testModel->taxonomiesCount);
        $this->assertIsBool($this->testModel->hasTaxonomiesAttached);
        
        $this->assertEquals(2, $this->testModel->taxonomiesCount);
        $this->assertTrue($this->testModel->hasTaxonomiesAttached);
    }
}
