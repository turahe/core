<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Feature\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Turahe\Core\Models\Taxonomy;
use Turahe\Core\Tests\Feature\Factories\TaxonomyFactory;
use Turahe\Core\Tests\Models\DummyTaxonomy;
use Turahe\Core\Tests\TestCase;

class HasTaxonomiesTest extends TestCase
{
    protected $testModel;

    public function setUp(): void
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
}
