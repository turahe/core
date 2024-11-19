<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Feature\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use PHPUnit\Framework\Attributes\Test;
use Turahe\Core\Models\Tag;
use Turahe\Core\Tests\Models\DummyTag;
use Turahe\Core\Tests\TestCase;

class HasTagsTest extends TestCase
{
    protected $testModel;

    public function setUp(): void
    {
        parent::setUp();
        $this->testModel = DummyTag::create(['name' => 'test']);
    }

    #[Test]
    public function it_provides_a_tags_relation(): void
    {
        $this->assertInstanceOf(MorphToMany::class, $this->testModel->tags());
    }

    #[Test]
    public function it_can_attach_a_tag(): void
    {
        $this->testModel->attachTag('tagName');

        $this->assertCount(1, $this->testModel->tags);

        $this->assertEquals(['tagName'], $this->testModel->tags->pluck('name')->toArray());

    }

    #[Test]
    public function it_can_attach_a_tag_with_a_type(): void
    {

        $this->testModel->attachTag('tagName', 'testType');

        $this->assertCount(1, $this->testModel->tags);

        $this->assertEquals(['tagName'], $this->testModel->tags->pluck('name')->toArray());

        $this->assertEquals(['testType'], $this->testModel->tags->pluck('type')->toArray());
    }

    #[Test]
    public function it_can_attach_a_tag_multiple_times_without_creating_duplicate_entries(): void
    {
        $this->testModel->attachTag('tagName');
        $this->testModel->attachTag('tagName');

        $this->assertCount(1, $this->testModel->tags);
    }

    #[Test]
    public function it_can_use_a_tag_model_when_attaching_a_tag(): void
    {
        $tag = Tag::findOrCreate('tagName');

        $this->testModel->attachTag($tag);

        $this->assertEquals(['tagName'], $this->testModel->tags->pluck('name')->toArray());
    }

    #[Test]
    public function it_can_attach_a_tag_inside_a_static_create_method(): void
    {
        $testModel = DummyTag::create([
            'name' => 'test',
            'tags' => ['tag', 'tag2'],
        ]);

        $this->assertCount(2, $testModel->tags);
    }

    #[Test]
    public function it_can_attach_a_tag_via_the_tags_mutator(): void
    {
        $this->testModel->tags = 'tag1';

        $this->assertCount(1, $this->testModel->tags);

    }

    #[Test]
    public function it_can_attach_multiple_tags_via_the_tags_mutator(): void
    {
        $this->testModel->tags = ['tag1', 'tag2'];

        $this->assertCount(2, $this->testModel->tags);

    }

    #[Test]
    public function it_can_override_tags_via_the_tags_mutator(): void
    {
        $this->testModel->tags = ['tag1', 'tag2'];
        $this->testModel->tags = ['tag2', 'tag3', 'tag4'];

        $this->assertCount(3, $this->testModel->tags);

    }

    #[Test]
    public function it_can_attach_multiple_tags(): void
    {
        $this->testModel->attachTags(['test1', 'test2']);

        $this->assertCount(2, $this->testModel->tags);

    }

    #[Test]
    public function it_can_attach_multiple_tags_with_a_type(): void
    {
        $this->testModel->attachTags(['test1', 'test2'], 'testType');

        $this->assertCount(2, $this->testModel->tags->where('type', '=', 'testType')->toArray());
    }

    #[Test]
    public function it_can_attach_a_existing_tag(): void
    {
        $this->testModel->attachTag(Tag::findOrCreate('test'));

        $this->assertCount(1, $this->testModel->tags);
    }

    #[Test]
    public function it_can_detach_a_tag(): void
    {
        $this->testModel->attachTags(['test1', 'test2', 'test3']);

        $this->testModel->detachTag('test2');

        $this->assertEquals(['test1', 'test3'], $this->testModel->tags->pluck('name')->toArray());
    }

    #[Test]
    public function it_can_detach_a_tag_with_a_type(): void
    {
        $this->testModel->attachTags(['test1', 'test2'], 'testType');

        $this->testModel->detachTag('test2', 'testType');

        $this->assertEquals(['test1'], $this->testModel->tags->pluck('name')->toArray());

    }

    #[Test]
    public function it_can_detach_a_tag_with_a_type_and_not_affect_a_tag_without_a_type(): void
    {
        $this->testModel->attachTag('test1', 'testType');

        $this->testModel->attachTag('test1');

        $this->testModel->detachTag('test1', 'testType');

        $this->assertEquals(['test1'], $this->testModel->tags->pluck('name')->toArray());

        $this->assertNull($this->testModel->tags->where('name', '=', 'test1')->first()->type);
    }

    #[Test]
    public function it_can_detach_a_tag_with_a_type_while_leaving_another_of_a_different_type(): void
    {
        $this->testModel->attachTag('test1', 'testType');

        $this->testModel->attachTag('test1', 'otherType');

        $this->testModel->detachTag('test1', 'testType');

        $this->assertEquals(['test1'], $this->testModel->tags->pluck('name')->sort()->toArray());

        $this->assertSame('otherType', $this->testModel->tags->where('name', 'test1')->first()->type);
    }

    #[Test]
    public function it_can_detach_multiple_tags(): void
    {
        $this->testModel->attachTags(['test1', 'test2', 'test3']);

        $this->testModel->detachTags(['test1', 'test3']);

        $this->assertEquals(['test2'], $this->testModel->tags->pluck('name')->toArray());

    }

    #[Test]
    public function it_can_get_all_attached_tags_of_a_certain_type(): void
    {
        $this->testModel->tags()->attach(Tag::findOrCreate('test', 'type1'));
        $this->testModel->tags()->attach(Tag::findOrCreate('test2', 'type2'));

        $tagsOfType2 = $this->testModel->tagsWithType('type2');

        $this->assertCount(1, $tagsOfType2);
        $this->assertSame('type2', $tagsOfType2->first()->type);

    }

    #[Test]
    public function it_provides_a_scope_to_get_all_models_that_have_any_of_the_given_tags_2(): void
    {

        DummyTag::create([
            'name' => 'model1',
            'tags' => ['tagA'],
        ]);

        DummyTag::create([
            'name' => 'model2',
            'tags' => ['tagA', 'tagB'],
        ]);

        DummyTag::create([
            'name' => 'model3',
            'tags' => ['tagA', 'tagB', 'tagC'],
        ]);

        $testModels = DummyTag::withAnyTags(['tagB', 'tagC']);

        $this->assertEquals(['model2', 'model3'], $testModels->pluck('name')->toArray());
    }

    #[Test]
    public function it_provides_a_scope_to_get_all_models_that_have_a_given_tag(): void
    {
        DummyTag::create([
            'name' => 'model1',
            'tags' => ['tagA'],
        ]);

        DummyTag::create([
            'name' => 'model2',
            'tags' => ['tagA', 'tagB'],
        ]);

        DummyTag::create([
            'name' => 'model3',
            'tags' => ['tagA', 'tagB', 'tagC'],
        ]);

        $testModels = DummyTag::withAnyTags('tagB');

        $this->assertEquals(['model2', 'model3'], $testModels->pluck('name')->toArray());

        $testModels = DummyTag::withAllTags('tagB');

        $this->assertEquals(['model2', 'model3'], $testModels->pluck('name')->toArray());
    }

    #[Test]
    public function it_provides_a_scope_to_get_all_models_that_have_all_given_tags(): void
    {
        DummyTag::create([
            'name' => 'model1',
            'tags' => ['tagA'],
        ]);

        DummyTag::create([
            'name' => 'model2',
            'tags' => ['tagA', 'tagB'],
        ]);

        DummyTag::create([
            'name' => 'model3',
            'tags' => ['tagA', 'tagB', 'tagC'],
        ]);

        $testModels = DummyTag::withAllTags(['tagB', 'tagC']);

        $this->assertEquals(['model3'], $testModels->pluck('name')->toArray());
    }

    #[Test]
    public function it_provides_a_scope_to_get_all_models_that_do_not_have_any_of_the_given_tags(): void
    {

        DummyTag::create([
            'name' => 'model1',
            'tags' => ['tagA', 'tagB'],
        ]);

        DummyTag::create([
            'name' => 'model2',
            'tags' => ['tagA', 'tagB', 'tagC'],
        ]);

        $testModels = DummyTag::withoutTags(['tagC']);

        $this->assertEquals([$this->testModel->name, 'model1'], $testModels->pluck('name')->toArray());
    }

    #[Test]
    public function it_provides_a_scope_to_get_all_models_that_have_any_of_the_given_tag_instances(): void
    {
        $tag = Tag::findOrCreate('tagA', 'typeA');

        DummyTag::create(['name' => 'model1'])->attachTag($tag);

        $testModels = DummyTag::withAnyTags([$tag]);

        $this->assertEquals(['model1'], $testModels->pluck('name')->toArray());
    }

    #[Test]
    public function it_can_sync_a_single_tag(): void
    {
        $this->testModel->attachTags(['tag1', 'tag2', 'tag3']);

        $this->testModel->syncTags('tag3');

        $this->assertEquals(['tag3'], $this->testModel->tags->pluck('name')->toArray());
    }

    #[Test]
    public function it_can_sync_multiple_tags(): void
    {
        $this->testModel->attachTags(['tag1', 'tag2', 'tag3']);

        $this->testModel->syncTags(['tag3', 'tag4']);

        $this->assertEquals(['tag3', 'tag4'], $this->testModel->tags->pluck('name')->toArray());

    }

    #[Test]
    public function it_can_sync_multiple_tags_from_a_collection(): void
    {
        $this->testModel->attachTags(collect(['tag1', 'tag2', 'tag3']));

        $this->testModel->syncTags(collect(['tag3', 'tag4']));

        $this->assertEquals(['tag3', 'tag4'], $this->testModel->tags->pluck('name')->toArray());

    }

    #[Test]
    public function it_can_sync_tags_with_different_types(): void
    {
        $this->testModel->syncTagsWithType(['tagA1', 'tagA2', 'tagA3'], 'typeA');
        $this->testModel->syncTagsWithType(['tagB1', 'tagB2'], 'typeB');

        $tagsOfTypeA = $this->testModel->tagsWithType('typeA');
        $this->assertEquals(['tagA1', 'tagA2', 'tagA3'], $tagsOfTypeA->pluck('name')->toArray());

        $tagsOfTypeB = $this->testModel->tagsWithType('typeB');
        $this->assertEquals(['tagB1', 'tagB2'], $tagsOfTypeB->pluck('name')->toArray());

    }

    #[Test]
    public function it_can_sync_tags_without_a_type_and_not_affect_tags_with_a_type(): void
    {
        $this->testModel->syncTagsWithType(['test1', 'test2'], 'testType');

        $this->testModel->syncTagsWithType(['test3']);

        $this->assertEquals(['test3'], $this->testModel->tags->pluck('name')->toArray());

        //        $this->assertEquals('testType', $this->testModel->tags->where('name', 'test1')->first()->type);

        //        $this->assertEquals('testType', $this->testModel->tags->where('name', 'test2')->first()->type);

        $this->assertNull($this->testModel->tags->where('name', '=', 'test3')->first()->type);
    }

    #[Test]
    public function it_can_detach_tags_on_model_delete(): void
    {
        $this->testModel->attachTag('tagDeletable');

        $this->testModel->delete();

        $this->assertCount(0, $this->testModel->tags()->get());

    }

    #[Test]
    public function it_can_delete_models_without_tags(): void
    {
        $this->assertTrue($this->testModel->delete());
    }

    #[Test]
    public function it_can_sync_tags_with_same_name(): void
    {
        $this->testModel->syncTagsWithType(['tagA1', 'tagA1'], 'typeA');

        $tagsOfTypeA = $this->testModel->tagsWithType('typeA');
        $this->assertEquals(['tagA1'], $tagsOfTypeA->pluck('name')->toArray());
    }

    #[Test]
    public function it_can_check_if_it_has_a_tag(): void
    {
        $tag = Tag::findOrCreate('test-tag');
        $anotherTag = Tag::findOrCreate('another-tag');

        $this->testModel->attachTag($tag);

        $this->assertTrue($this->testModel->hasTag('test-tag'));
        $this->assertTrue($this->testModel->hasTag($tag->getKey()));
        $this->assertFalse($this->testModel->hasTag('non-existing-tag'));
        $this->assertFalse($this->testModel->hasTag($anotherTag->getKey()));
    }

    #[Test]
    public function it_can_check_if_it_has_a_tag_with_type(): void
    {
        $tag = Tag::findOrCreate('test-tag', 'type1');
        Tag::findOrCreate('test-tag', 'type2');

        $this->testModel->attachTag($tag);

        $this->assertTrue($this->testModel->hasTag('test-tag', 'type1'));
        $this->assertFalse($this->testModel->hasTag('test-tag', 'type2'));
    }
}
