<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Turahe\Core\Repositories\TaxonomyRepository;
use Turahe\Core\Tests\Models\Taxonomy;
use Turahe\Core\Tests\TestCase;

class TaxonomyTest extends TestCase
{
    #[Test]
    public function it_can_list_all_taxonomies(): void
    {
        Taxonomy::factory(6)->create();

        $taxonomyRepo = new TaxonomyRepository(new Taxonomy);
        $taxonomies = $taxonomyRepo->getTaxonomies();

        $this->assertInstanceOf(Collection::class, $taxonomies);
        $this->assertCount(6, $taxonomies->all()); // +1 in the TestCase
    }

    #[Test]
    public function it_can_force_delete_the_taxonomy(): void
    {
        $taxonomy = Taxonomy::factory()->create();

        $taxonomyRepo = new TaxonomyRepository($taxonomy);
        $deleted = $taxonomyRepo->deleteTaxonomy();

        $this->assertTrue($deleted);
    }

    #[Test]
    public function it_cannot_get_the_taxonomy(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $taxonomyRepo = new TaxonomyRepository(new Taxonomy);
        $taxonomyRepo->getTaxonomy('121323');

    }

    #[Test]
    public function it_can_delete_the_taxonomy(): void
    {
        $taxonomy = Taxonomy::factory()->create();

        $taxonomyRepo = new TaxonomyRepository($taxonomy);
        $deleted = $taxonomyRepo->trashTaxonomy();

        $this->assertTrue($deleted);
    }

    #[Test]
    public function it_can_update_the_taxonomy(): void
    {
        $taxonomy = Taxonomy::factory()->create();

        $data = [
            'name' => 'test 1',
            'code' => null,
        ];

        $taxonomyRepo = new TaxonomyRepository($taxonomy);
        $updated = $taxonomyRepo->updateTaxonomy($data['name'], $data['code']);

        $taxonomy = $taxonomyRepo->getTaxonomy($taxonomy->getKey());

        $this->assertTrue($updated);
        $this->assertEquals($data['name'], $taxonomy->name);
        $this->assertEquals('test-1', $taxonomy->slug);
        $this->assertNotEmpty($taxonomy->code);
        $this->assertNotEmpty($taxonomy->description);
    }

    #[Test]
    public function it_can_return_the_created_taxonomy(): void
    {
        $taxonomyFactory = Taxonomy::factory()->create();

        $taxonomyRepo = new TaxonomyRepository(new Taxonomy);
        $taxonomy = $taxonomyRepo->getTaxonomy($taxonomyFactory->getKey());

        $this->assertInstanceOf(Taxonomy::class, $taxonomy);
        $this->assertEquals($taxonomyFactory->slug, $taxonomy->slug);
        $this->assertEquals($taxonomyFactory->name, $taxonomy->name);
        $this->assertEquals($taxonomyFactory->code, $taxonomy->code);
        $this->assertEquals($taxonomyFactory->description, $taxonomy->description);
    }

    #[Test]
    public function it_can_create_a_taxonomy(): void
    {
        $data = [
            'name' => 'test 1',
            'code' => 'TEST',
        ];

        $taxonomyRepo = new TaxonomyRepository(new Taxonomy);
        $taxonomy = $taxonomyRepo->createTaxonomy($data['name'], $data['code']);

        $this->assertInstanceOf(Taxonomy::class, $taxonomy);
        $this->assertEquals($data['name'], $taxonomy->name);
        $this->assertEquals($data['code'], $taxonomy->code);
        $this->assertEquals('test-1', $taxonomy->slug);
        $this->assertEmpty($taxonomy->description);
    }

    #[Test]
    public function it_can_create_a_taxonomy_from_string(): void
    {
        $name = 'test';

        $taxonomyRepo = new TaxonomyRepository(new Taxonomy);
        $taxonomy = $taxonomyRepo->createTaxonomy($name);

        $this->assertInstanceOf(Taxonomy::class, $taxonomy);
        $this->assertEquals($name, $taxonomy->name);
    }

    #[Test]
    public function it_can_create_a_taxonomy_from_string_with_pipes(): void
    {
        $name = 'test1|test2';

        $taxonomyRepo = new TaxonomyRepository(new Taxonomy);
        $taxonomies = $taxonomyRepo->createTaxonomies($name);

        $this->assertInstanceOf(Collection::class, $taxonomies);
        $this->assertCount(2, Taxonomy::all());

        $categories = explode('|', $name);
        foreach ($categories as $category) {
            $taxonomy = $taxonomyRepo->getTaxonomyByName($category);
            $this->assertInstanceOf(Taxonomy::class, $taxonomy);
        }

    }

    #[Test]
    public function it_can_create_a_taxonomy_from_array(): void
    {
        $names = ['category1', 'category2', 'category3'];

        $taxonomyRepo = new TaxonomyRepository(new Taxonomy);
        $taxonomies = $taxonomyRepo->createTaxonomies($names);

        $this->assertInstanceOf(Collection::class, $taxonomies);
        $this->assertCount(3, Taxonomy::all());

        foreach ($names as $name) {
            $this->assertDatabaseHas('taxonomies', [
                'name' => $name,
            ]);
        }

    }

    #[Test]
    public function it_can_create_a_taxonomy_with_parent(): void
    {
        $name = 'test';

        $parent = Taxonomy::factory()->create();
        $taxonomyRepo = new TaxonomyRepository(new Taxonomy);
        $taxonomy = $taxonomyRepo->createTaxonomy($name, strtoupper($name), $parent);

        $this->assertInstanceOf(Taxonomy::class, $taxonomy);
        $this->assertEquals($name, $taxonomy->name);
        $this->assertEquals($parent->getKey(), $taxonomy->parent_id);
    }

    #[Test]
    public function it_automatically_generates_a_slug(): void
    {
        $taxonomyRepo = new TaxonomyRepository(new Taxonomy);
        $taxonomy = $taxonomyRepo->createTaxonomy('this is a taxonomy');

        $this->assertEquals('this-is-a-taxonomy', $taxonomy->slug);
        $this->assertNull($taxonomy->code);
    }

    #[Test]
    public function it_can_create_taxonomies_using_an_array(): void
    {

        $taxonomyRepo = new TaxonomyRepository(new Taxonomy);
        $taxonomies = $taxonomyRepo->createTaxonomies(['taxonomy1', 'taxonomy2', 'taxonomy3']);

        $this->assertCount(3, Taxonomy::all());
        $this->assertInstanceOf(Collection::class, $taxonomies);

    }
}
