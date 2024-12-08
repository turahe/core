<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit;

use Turahe\Core\Models\Tag;
use Turahe\Core\Tests\TestCase;

class TagTest extends TestCase
{
    public function test_can_create_a_tag(): void
    {
        $tag = Tag::findOrCreateFromString('string');

        $this->assertCount(1, Tag::all());
        $this->assertNull($tag->type);
    }

    public function test_creates_sortable_tags(): void
    {
        $tag = Tag::findOrCreateFromString('string');
        $this->assertEquals(1, $tag->record_ordering);

        $tag = Tag::findOrCreateFromString('string2');
        $this->assertEquals(2, $tag->record_ordering);
    }

    public function test_automatically_generates_a_slug(): void
    {
        $tag = Tag::findOrCreateFromString('this is a tag');

        $this->assertEquals('this-is-a-tag', $tag->slug);
    }

    public function test_uses_str_slug_if_config_slugger_value_is_empty(): void
    {
        config()->set('tags.slugger', null);

        $tag = Tag::findOrCreateFromString('this is a tag');

        $this->assertEquals('this-is-a-tag', $tag->slug);
    }

    public function test_can_create_a_tag_with_a_type(): void
    {
        $tag = Tag::findOrCreate('string', 'myType');

        $this->assertEquals('myType', $tag->type);

    }

    public function test_provides_a_scope_to_get_all_tags_with_a_specific_type(): void
    {
        Tag::findOrCreate('tagA', 'firstType');
        Tag::findOrCreate('tagB', 'firstType');
        Tag::findOrCreate('tagC', 'secondType');
        Tag::findOrCreate('tagD', 'secondType');

        $this->assertEquals(['tagA', 'tagB'], Tag::withType('firstType')->pluck('name')->toArray());
        $this->assertEquals(['tagC', 'tagD'], Tag::withType('secondType')->pluck('name')->toArray());

    }

    public function test_provides_a_scope_to_get_all_tags_the_contain_a_certain_string(): void
    {
        Tag::findOrCreate('one');
        Tag::findOrCreate('another-one');
        Tag::findOrCreate('another-ONE-with-different-casing');
        Tag::findOrCreate('two');

        $this->assertEquals([
            'one',
            'another-one',
            'another-ONE-with-different-casing',
        ], Tag::containing('on')->pluck('name')->toArray());
        $this->assertEquals(['two'], Tag::containing('tw')->pluck('name')->toArray());

    }

    public function test_provides_a_method_to_get_all_tags_with_a_specific_type(): void
    {
        Tag::findOrCreate('tagA', 'firstType');
        Tag::findOrCreate('tagB', 'firstType');
        Tag::findOrCreate('tagC', 'secondType');
        Tag::findOrCreate('tagD', 'secondType');

        $this->assertEquals(['tagA', 'tagB'], Tag::getWithType('firstType')->pluck('name')->toArray());
        $this->assertEquals(['tagC', 'tagD'], Tag::getWithType('secondType')->pluck('name')->toArray());

    }

    public function test_will_not_create_a_tag_if_the_tag_already_exists(): void
    {
        Tag::findOrCreate('string');

        Tag::findOrCreate('string');

        $this->assertCount(1, Tag::all());

    }

    public function test_will_create_a_tag_if_a_tag_exists_with_the_same_name_but_a_different_type(): void
    {
        Tag::findOrCreate('string');

        Tag::findOrCreate('string', 'myType');

        $this->assertCount(2, Tag::all());

    }

    public function test_can_create_tags_using_an_array(): void
    {

        Tag::findOrCreate(['tag1', 'tag2', 'tag3']);

        $this->assertCount(3, Tag::all());
    }

    public function test_can_create_tags_using_a_collection(): void
    {
        Tag::findOrCreate(collect(['tag1', 'tag2', 'tag3']));

        $this->assertCount(3, Tag::all());

    }

    public function test_can_find_or_create_a_tag(): void
    {
        $tag = Tag::findOrCreate('string');

        $tag2 = Tag::findOrCreate($tag->name);

        $this->assertEquals('string', $tag2->name);

    }

    public function test_can_find_tags_from_a_string_with_any_type(): void
    {
        Tag::findOrCreate('tag1');

        Tag::findOrCreate('tag1', 'myType1');

        Tag::findOrCreate('tag1', 'myType2');

        $tags = Tag::findFromStringOfAnyType('tag1');

        $this->assertCount(3, $tags);

    }

    public function test_name_can_be_changed_by_setting_its_name_property_to_a_new_value(): void
    {
        $tag = Tag::findOrCreate('my tag');

        $tag->name = 'new name';

        $tag->save();

        $this->assertEquals('new name', $tag->name);

    }

    public function test_gets_all_tag_types(): void
    {

        Tag::findOrCreate('foo', 'type1');
        Tag::findOrCreate('bar', 'type1');
        Tag::findOrCreate('baz', 'type2');
        Tag::findOrCreate('qux', 'type2');

        $types = Tag::getTypes();

        $this->assertCount(2, $types);
        $this->assertEquals('type1', $types[0]);
        $this->assertEquals('type2', $types[1]);
    }
}
