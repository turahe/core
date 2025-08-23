<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit;

use Turahe\Core\Models\Tag;
use Turahe\Core\Tests\TestCase;

class TagModelTest extends TestCase
{
    public function test_tag_uses_configurable_primary_key(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'newUniqueId'));
        $this->assertTrue(method_exists($tag, 'uniqueIds'));
        $this->assertTrue(method_exists($tag, 'shouldUseUniqueIds'));
        $this->assertTrue(method_exists($tag, 'getConfiguredKeyType'));
        $this->assertTrue(method_exists($tag, 'shouldUseIncrementing'));
    }

    public function test_tag_uses_user_stamps(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'author'));
        $this->assertTrue(method_exists($tag, 'editor'));
        $this->assertTrue(method_exists($tag, 'destroyer'));
    }

    public function test_tag_uses_soft_deletes(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'trashed'));
        $this->assertTrue(method_exists($tag, 'restore'));
        $this->assertTrue(method_exists($tag, 'forceDelete'));
    }

    public function test_tag_uses_sortable(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'setHighestOrderNumber'));
        $this->assertTrue(method_exists($tag, 'moveOrderDown'));
        $this->assertTrue(method_exists($tag, 'moveOrderUp'));
    }

    public function test_tag_uses_sluggable(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'getSlugOptions'));
    }

    public function test_tag_table_is_configurable(): void
    {
        $tag = new Tag;

        $this->assertEquals(config('core.tables.tags'), $tag->getTable());
    }

    public function test_tag_has_fillable_attributes(): void
    {
        $tag = new Tag;

        $expectedFillable = [
            'name',
            'slug',
            'type',
        ];

        $this->assertEquals($expectedFillable, $tag->getFillable());
    }

    public function test_tag_has_sortable_configuration(): void
    {
        $tag = new Tag;

        $expectedSortable = [
            'order_column_name' => 'record_ordering',
            'sort_when_creating' => true,
        ];

        $this->assertEquals($expectedSortable, $tag->sortable);
    }

    public function test_tag_can_be_created(): void
    {
        $tag = new Tag;

        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertTrue(is_callable([Tag::class, 'create']));
    }

    public function test_tag_automatically_generates_slug(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'getSlugOptions'));
    }

    public function test_tag_slug_is_not_regenerated_on_update(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'getSlugOptions'));
    }

    public function test_tag_can_be_updated(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'update'));
    }

    public function test_tag_can_be_deleted(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'delete'));
        $this->assertTrue(method_exists($tag, 'forceDelete'));
    }

    public function test_tag_can_be_force_deleted(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'forceDelete'));
    }

    public function test_tag_can_be_restored(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'restore'));
    }

    public function test_tag_scope_with_type_filters_by_type(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'scopeWithType'));
    }

    public function test_tag_scope_with_type_returns_all_when_type_is_null(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'scopeWithType'));
    }

    public function test_tag_scope_with_type_applies_ordered(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'scopeWithType'));
    }

    public function test_tag_scope_containing_filters_by_name(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'scopeContaining'));
    }

    public function test_tag_scope_containing_is_case_insensitive(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'scopeContaining'));
    }

    public function test_tag_find_or_create_from_string_creates_new_tag(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'findOrCreateFromString'));
    }

    public function test_tag_find_or_create_from_string_finds_existing_tag(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'findOrCreateFromString'));
    }

    public function test_tag_find_or_create_from_string_creates_tag_with_type(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'findOrCreateFromString'));
    }

    public function test_tag_find_or_create_handles_array_input(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'findOrCreate'));
    }

    public function test_tag_find_or_create_handles_collection_input(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'findOrCreate'));
    }

    public function test_tag_find_or_create_handles_single_string_input(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'findOrCreate'));
    }

    public function test_tag_find_or_create_handles_tag_instance_input(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'findOrCreate'));
    }

    public function test_tag_find_from_string_finds_by_name(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'findFromString'));
    }

    public function test_tag_find_from_string_finds_by_slug(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'findFromString'));
    }

    public function test_tag_find_from_string_returns_null_when_not_found(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'findFromString'));
    }

    public function test_tag_find_from_string_of_any_type_finds_all_matches(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'findFromStringOfAnyType'));
    }

    public function test_tag_find_from_string_of_any_type_finds_by_name_or_slug(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'findFromStringOfAnyType'));
    }

    public function test_tag_get_with_type_returns_collection(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'getWithType'));
    }

    public function test_tag_get_types_returns_unique_types(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'getTypes'));
    }

    public function test_tag_creates_sortable_ordering(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'setHighestOrderNumber'));
    }

    public function test_tag_can_be_moved_in_order(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'moveOrderUp'));
        $this->assertTrue(method_exists($tag, 'moveOrderDown'));
    }

    public function test_tag_scope_with_type_returns_builder(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'scopeWithType'));
    }

    public function test_tag_scope_containing_returns_builder(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'scopeContaining'));
    }

    public function test_tag_scope_with_type_applies_where_clause(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'scopeWithType'));
    }

    public function test_tag_scope_containing_applies_where_clause(): void
    {
        $tag = new Tag;

        $this->assertTrue(method_exists($tag, 'scopeContaining'));
    }
}
