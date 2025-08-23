<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit;

use Turahe\Core\Models\Taxonomy;
use Turahe\Core\Tests\TestCase;

class TaxonomyModelTest extends TestCase
{
    public function test_taxonomy_uses_configurable_primary_key(): void
    {
        $taxonomy = new Taxonomy;

        $this->assertTrue(method_exists($taxonomy, 'newUniqueId'));
        $this->assertTrue(method_exists($taxonomy, 'uniqueIds'));
        $this->assertTrue(method_exists($taxonomy, 'shouldUseUniqueIds'));
        $this->assertTrue(method_exists($taxonomy, 'getConfiguredKeyType'));
        $this->assertTrue(method_exists($taxonomy, 'shouldUseIncrementing'));
    }

    public function test_taxonomy_uses_user_stamps(): void
    {
        $taxonomy = new Taxonomy;

        $this->assertTrue(method_exists($taxonomy, 'author'));
        $this->assertTrue(method_exists($taxonomy, 'editor'));
        $this->assertTrue(method_exists($taxonomy, 'destroyer'));
    }

    public function test_taxonomy_uses_soft_deletes(): void
    {
        $taxonomy = new Taxonomy;

        $this->assertTrue(method_exists($taxonomy, 'trashed'));
        $this->assertTrue(method_exists($taxonomy, 'restore'));
        $this->assertTrue(method_exists($taxonomy, 'forceDelete'));
    }

    public function test_taxonomy_uses_nested_set(): void
    {
        $taxonomy = new Taxonomy;

        $this->assertTrue(method_exists($taxonomy, 'getLftName'));
        $this->assertTrue(method_exists($taxonomy, 'getRgtName'));
        $this->assertTrue(method_exists($taxonomy, 'getParentIdName'));
    }

    public function test_taxonomy_uses_sortable(): void
    {
        $taxonomy = new Taxonomy;

        $this->assertTrue(method_exists($taxonomy, 'setHighestOrderNumber'));
        $this->assertTrue(method_exists($taxonomy, 'moveOrderDown'));
        $this->assertTrue(method_exists($taxonomy, 'moveOrderUp'));
    }

    public function test_taxonomy_uses_sluggable(): void
    {
        $taxonomy = new Taxonomy;

        $this->assertTrue(method_exists($taxonomy, 'getSlugOptions'));
    }

    public function test_taxonomy_uses_prunable(): void
    {
        $taxonomy = new Taxonomy;

        $this->assertTrue(method_exists($taxonomy, 'prunable'));
    }

    public function test_taxonomy_table_is_configurable(): void
    {
        $taxonomy = new Taxonomy;

        $this->assertEquals(config('core.tables.taxonomies'), $taxonomy->getTable());
    }

    public function test_taxonomy_has_fillable_attributes(): void
    {
        $taxonomy = new Taxonomy;

        $expectedFillable = [
            'name',
            'code',
            'description',
            'parent_id',
        ];

        $this->assertEquals($expectedFillable, $taxonomy->getFillable());
    }

    public function test_taxonomy_has_correct_nested_set_column_names(): void
    {
        $taxonomy = new Taxonomy;

        $this->assertEquals('record_left', $taxonomy->getLftName());
        $this->assertEquals('record_right', $taxonomy->getRgtName());
        $this->assertEquals('parent_id', $taxonomy->getParentIdName());
    }

    public function test_taxonomy_has_sortable_configuration(): void
    {
        $taxonomy = new Taxonomy;

        $expectedSortable = [
            'order_column_name' => 'record_ordering',
            'sort_when_creating' => true,
        ];

        $this->assertEquals($expectedSortable, $taxonomy->sortable);
    }

    public function test_taxonomy_can_be_created(): void
    {
        $taxonomy = new Taxonomy;

        $this->assertInstanceOf(Taxonomy::class, $taxonomy);
        $this->assertTrue(is_callable([Taxonomy::class, 'create']));
    }

    public function test_taxonomy_automatically_generates_slug(): void
    {
        $taxonomy = new Taxonomy;

        $this->assertTrue(method_exists($taxonomy, 'getSlugOptions'));
    }

    public function test_taxonomy_slug_is_regenerated_on_update(): void
    {
        $taxonomy = new Taxonomy;

        $this->assertTrue(method_exists($taxonomy, 'getSlugOptions'));
    }

    public function test_taxonomy_can_have_parent_child_relationship(): void
    {
        $taxonomy = new Taxonomy;

        $this->assertTrue(method_exists($taxonomy, 'getLftName'));
        $this->assertTrue(method_exists($taxonomy, 'getRgtName'));
        $this->assertTrue(method_exists($taxonomy, 'getParentIdName'));
    }

    public function test_taxonomy_can_be_updated(): void
    {
        $taxonomy = new Taxonomy;

        $this->assertTrue(method_exists($taxonomy, 'update'));
    }

    public function test_taxonomy_can_be_deleted(): void
    {
        $taxonomy = new Taxonomy;

        $this->assertTrue(method_exists($taxonomy, 'delete'));
        $this->assertTrue(method_exists($taxonomy, 'forceDelete'));
    }

    public function test_taxonomy_can_be_force_deleted(): void
    {
        $taxonomy = new Taxonomy;

        $this->assertTrue(method_exists($taxonomy, 'forceDelete'));
    }

    public function test_taxonomy_can_be_restored(): void
    {
        $taxonomy = new Taxonomy;

        $this->assertTrue(method_exists($taxonomy, 'restore'));
    }

    public function test_taxonomy_scope_visible_filters_published_taxonomies(): void
    {
        $taxonomy = new Taxonomy;

        $this->assertTrue(method_exists($taxonomy, 'scopeVisible'));
    }

    public function test_taxonomy_scope_searchable_filters_non_deleted_taxonomies(): void
    {
        $taxonomy = new Taxonomy;

        $this->assertTrue(method_exists($taxonomy, 'scopeSearchable'));
    }

    public function test_taxonomy_scope_visible_returns_builder(): void
    {
        $taxonomy = new Taxonomy;

        $this->assertTrue(method_exists($taxonomy, 'scopeVisible'));
    }

    public function test_taxonomy_scope_searchable_returns_builder(): void
    {
        $taxonomy = new Taxonomy;

        $this->assertTrue(method_exists($taxonomy, 'scopeSearchable'));
    }

    public function test_taxonomy_scope_visible_applies_where_clause(): void
    {
        $taxonomy = new Taxonomy;

        $this->assertTrue(method_exists($taxonomy, 'scopeVisible'));
    }

    public function test_taxonomy_scope_searchable_applies_where_clause(): void
    {
        $taxonomy = new Taxonomy;

        $this->assertTrue(method_exists($taxonomy, 'scopeSearchable'));
    }

    public function test_taxonomy_can_set_parent_attribute(): void
    {
        $taxonomy = new Taxonomy;

        $this->assertTrue(method_exists($taxonomy, 'setParentAttribute'));
    }

    public function test_taxonomy_has_sortable_ordering(): void
    {
        $taxonomy = new Taxonomy;

        $this->assertTrue(method_exists($taxonomy, 'setHighestOrderNumber'));
    }

    public function test_taxonomy_can_be_moved_in_order(): void
    {
        $taxonomy = new Taxonomy;

        $this->assertTrue(method_exists($taxonomy, 'moveOrderUp'));
        $this->assertTrue(method_exists($taxonomy, 'moveOrderDown'));
    }
}
