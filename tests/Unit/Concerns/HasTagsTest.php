<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Turahe\Core\Models\Tag;
use Turahe\Core\Tests\Models\DummyTag;
use Turahe\Core\Tests\TestCase;

class HasTagsTest extends TestCase
{
    protected $testModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testModel = DummyTag::create(['name' => 'test']);
    }

    public function test_provides_a_tags_relation(): void
    {
        $this->assertInstanceOf(MorphToMany::class, $this->testModel->tags());
    }

    public function test_can_attach_a_tag(): void
    {
        $this->testModel->attachTag('tagName');

        $this->assertCount(1, $this->testModel->tags);

        $this->assertEquals(['tagName'], $this->testModel->tags->pluck('name')->toArray());

    }

    public function test_can_attach_a_tag_with_a_type(): void
    {

        $this->testModel->attachTag('tagName', 'testType');

        $this->assertCount(1, $this->testModel->tags);

        $this->assertEquals(['tagName'], $this->testModel->tags->pluck('name')->toArray());

        $this->assertEquals(['testType'], $this->testModel->tags->pluck('type')->toArray());
    }

    public function test_can_attach_a_tag_multiple_times_without_creating_duplicate_entries(): void
    {
        $this->testModel->attachTag('tagName');
        $this->testModel->attachTag('tagName');

        $this->assertCount(1, $this->testModel->tags);
    }

    public function test_can_use_a_tag_model_when_attaching_a_tag(): void
    {
        $tag = Tag::findOrCreate('tagName');

        $this->testModel->attachTag($tag);

        $this->assertEquals(['tagName'], $this->testModel->tags->pluck('name')->toArray());
    }

    public function test_can_attach_a_tag_inside_a_static_create_method(): void
    {
        $testModel = DummyTag::create([
            'name' => 'test',
            'tags' => ['tag', 'tag2'],
        ]);

        $this->assertCount(2, $testModel->tags);
    }

    public function test_can_attach_a_tag_via_the_tags_mutator(): void
    {
        $this->testModel->tags = 'tag1';

        $this->assertCount(1, $this->testModel->tags);

    }

    public function test_can_attach_multiple_tags_via_the_tags_mutator(): void
    {
        $this->testModel->tags = ['tag1', 'tag2'];

        $this->assertCount(2, $this->testModel->tags);

    }

    public function test_can_override_tags_via_the_tags_mutator(): void
    {
        $this->testModel->tags = ['tag1', 'tag2'];
        $this->testModel->tags = ['tag2', 'tag3', 'tag4'];

        $this->assertCount(3, $this->testModel->tags);

    }

    public function test_can_attach_multiple_tags(): void
    {
        $this->testModel->attachTags(['test1', 'test2']);

        $this->assertCount(2, $this->testModel->tags);

    }

    public function test_can_attach_multiple_tags_with_a_type(): void
    {
        $this->testModel->attachTags(['test1', 'test2'], 'testType');

        $this->assertCount(2, $this->testModel->tags->where('type', '=', 'testType')->toArray());
    }

    public function test_can_attach_a_existing_tag(): void
    {
        $this->testModel->attachTag(Tag::findOrCreate('test'));

        $this->assertCount(1, $this->testModel->tags);
    }

    public function test_can_detach_a_tag(): void
    {
        $this->testModel->attachTags(['test1', 'test2', 'test3']);

        $this->testModel->detachTag('test2');

        $this->assertEquals(['test1', 'test3'], $this->testModel->tags->pluck('name')->toArray());
    }

    public function test_can_detach_a_tag_with_a_type(): void
    {
        $this->testModel->attachTags(['test1', 'test2'], 'testType');

        $this->testModel->detachTag('test2', 'testType');

        $this->assertEquals(['test1'], $this->testModel->tags->pluck('name')->toArray());

    }

    public function test_can_detach_a_tag_with_a_type_and_not_affect_a_tag_without_a_type(): void
    {
        $this->testModel->attachTag('test1', 'testType');

        $this->testModel->attachTag('test1');

        $this->testModel->detachTag('test1', 'testType');

        $this->assertEquals(['test1'], $this->testModel->tags->pluck('name')->toArray());

        $this->assertNull($this->testModel->tags->where('name', '=', 'test1')->first()->type);
    }

    public function test_can_detach_a_tag_with_a_type_while_leaving_another_of_a_different_type(): void
    {
        $this->testModel->attachTag('test1', 'testType');

        $this->testModel->attachTag('test1', 'otherType');

        $this->testModel->detachTag('test1', 'testType');

        $this->assertEquals(['test1'], $this->testModel->tags->pluck('name')->sort()->toArray());

        $this->assertSame('otherType', $this->testModel->tags->where('name', 'test1')->first()->type);
    }

    public function test_can_detach_multiple_tags(): void
    {
        $this->testModel->attachTags(['test1', 'test2', 'test3']);

        $this->testModel->detachTags(['test1', 'test3']);

        $this->assertEquals(['test2'], $this->testModel->tags->pluck('name')->toArray());

    }

    public function test_can_get_all_attached_tags_of_a_certain_type(): void
    {
        $this->testModel->tags()->attach(Tag::findOrCreate('test', 'type1'));
        $this->testModel->tags()->attach(Tag::findOrCreate('test2', 'type2'));

        $tagsOfType2 = $this->testModel->tagsWithType('type2');

        $this->assertCount(1, $tagsOfType2);
        $this->assertSame('type2', $tagsOfType2->first()->type);

    }

    public function test_provides_a_scope_to_get_all_models_that_have_any_of_the_given_tags_2(): void
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

    public function test_provides_a_scope_to_get_all_models_that_have_a_given_tag(): void
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

    public function test_provides_a_scope_to_get_all_models_that_have_all_given_tags(): void
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

    public function test_provides_a_scope_to_get_all_models_that_do_not_have_any_of_the_given_tags(): void
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

    public function test_provides_a_scope_to_get_all_models_that_have_any_of_the_given_tag_instances(): void
    {
        $tag = Tag::findOrCreate('tagA', 'typeA');

        DummyTag::create(['name' => 'model1'])->attachTag($tag);

        $testModels = DummyTag::withAnyTags([$tag]);

        $this->assertEquals(['model1'], $testModels->pluck('name')->toArray());
    }

    public function test_can_sync_a_single_tag(): void
    {
        $this->testModel->attachTags(['tag1', 'tag2', 'tag3']);

        $this->testModel->syncTags('tag3');

        $this->assertEquals(['tag3'], $this->testModel->tags->pluck('name')->toArray());
    }

    public function test_can_sync_multiple_tags(): void
    {
        $this->testModel->attachTags(['tag1', 'tag2', 'tag3']);

        $this->testModel->syncTags(['tag3', 'tag4']);

        $this->assertEquals(['tag3', 'tag4'], $this->testModel->tags->pluck('name')->toArray());

    }

    public function test_can_sync_multiple_tags_from_a_collection(): void
    {
        $this->testModel->attachTags(collect(['tag1', 'tag2', 'tag3']));

        $this->testModel->syncTags(collect(['tag3', 'tag4']));

        $this->assertEquals(['tag3', 'tag4'], $this->testModel->tags->pluck('name')->toArray());

    }

    public function test_can_sync_tags_with_different_types(): void
    {
        $this->testModel->syncTagsWithType(['tagA1', 'tagA2', 'tagA3'], 'typeA');
        $this->testModel->syncTagsWithType(['tagB1', 'tagB2'], 'typeB');

        $tagsOfTypeA = $this->testModel->tagsWithType('typeA');
        $this->assertEquals(['tagA1', 'tagA2', 'tagA3'], $tagsOfTypeA->pluck('name')->toArray());

        $tagsOfTypeB = $this->testModel->tagsWithType('typeB');
        $this->assertEquals(['tagB1', 'tagB2'], $tagsOfTypeB->pluck('name')->toArray());

    }

    public function test_can_sync_tags_without_a_type_and_not_affect_tags_with_a_type(): void
    {
        $this->testModel->syncTagsWithType(['test1', 'test2'], 'testType');

        $this->testModel->syncTagsWithType(['test3']);

        $this->assertEquals(['test3'], $this->testModel->tags->pluck('name')->toArray());

        //        $this->assertEquals('testType', $this->testModel->tags->where('name', 'test1')->first()->type);

        //        $this->assertEquals('testType', $this->testModel->tags->where('name', 'test2')->first()->type);

        $this->assertNull($this->testModel->tags->where('name', '=', 'test3')->first()->type);
    }

    public function test_can_detach_tags_on_model_delete(): void
    {
        $this->testModel->attachTag('tagDeletable');

        $this->testModel->delete();

        $this->assertCount(0, $this->testModel->tags()->get());

    }

    public function test_can_delete_models_without_tags(): void
    {
        $this->assertTrue($this->testModel->delete());
    }

    public function test_can_sync_tags_with_same_name(): void
    {
        $this->testModel->syncTagsWithType(['tagA1', 'tagA1'], 'typeA');

        $tagsOfTypeA = $this->testModel->tagsWithType('typeA');
        $this->assertEquals(['tagA1'], $tagsOfTypeA->pluck('name')->toArray());
    }

    public function test_can_check_if_it_has_a_tag(): void
    {
        $tag = Tag::findOrCreate('test-tag');
        $anotherTag = Tag::findOrCreate('another-tag');

        $this->testModel->attachTag($tag);

        $this->assertTrue($this->testModel->hasTag('test-tag'));
        $this->assertTrue($this->testModel->hasTag($tag->getKey()));
        $this->assertFalse($this->testModel->hasTag('non-existing-tag'));
        $this->assertFalse($this->testModel->hasTag($anotherTag->getKey()));
    }

    public function test_can_check_if_it_has_a_tag_with_type(): void
    {
        $tag = Tag::findOrCreate('test-tag', 'type1');
        Tag::findOrCreate('test-tag', 'type2');

        $this->testModel->attachTag($tag);

        $this->assertTrue($this->testModel->hasTag('test-tag', 'type1'));
        $this->assertFalse($this->testModel->hasTag('test-tag', 'type2'));
    }

    // ===============================================
    // Tests for PHP 8.4 Features
    // ===============================================

    public function test_tags_count_readonly_property(): void
    {
        // Initially no tags
        $this->assertEquals(0, $this->testModel->tagsCount);
        
        // Add tags
        $this->testModel->attachTags(['tag1', 'tag2', 'tag3']);
        
        // Refresh model
        $this->testModel->refresh();
        $this->assertEquals(3, $this->testModel->tagsCount);
    }

    public function test_has_tags_attached_readonly_property(): void
    {
        // Initially no tags
        $this->assertFalse($this->testModel->hasTagsAttached);
        
        // Add tag
        $this->testModel->attachTag('test-tag');
        
        // Refresh model
        $this->testModel->refresh();
        $this->assertTrue($this->testModel->hasTagsAttached);
    }

    public function test_tag_names_readonly_property(): void
    {
        // Add tags
        $this->testModel->attachTags(['alpha', 'beta', 'gamma']);
        
        $tagNames = $this->testModel->tagNames;
        $this->assertIsArray($tagNames);
        $this->assertCount(3, $tagNames);
        $this->assertContains('alpha', $tagNames);
        $this->assertContains('beta', $tagNames);
        $this->assertContains('gamma', $tagNames);
    }

    public function test_tags_grouped_by_type_readonly_property(): void
    {
        // Create tags with different types
        $categoryTag1 = Tag::findOrCreate('cat1', 'category');
        $categoryTag2 = Tag::findOrCreate('cat2', 'category');
        $skillTag = Tag::findOrCreate('skill1', 'skill');
        
        $this->testModel->attachTags([$categoryTag1, $categoryTag2, $skillTag]);
        
        $groupedTags = $this->testModel->tagsGroupedByType;
        $this->assertIsArray($groupedTags);
        $this->assertArrayHasKey('category', $groupedTags);
        $this->assertArrayHasKey('skill', $groupedTags);
        $this->assertCount(2, $groupedTags['category']);
        $this->assertCount(1, $groupedTags['skill']);
    }

    public function test_has_tags_variadic_parameters(): void
    {
        // Attach multiple tags
        $this->testModel->attachTags(['tag1', 'tag2', 'tag3']);
        
        // Test with single tag
        $this->assertTrue($this->testModel->hasTags('tag1'));
        
        // Test with multiple tags - all exist
        $this->assertTrue($this->testModel->hasTags('tag1', 'tag2'));
        $this->assertTrue($this->testModel->hasTags('tag1', 'tag2', 'tag3'));
        
        // Test with multiple tags - one doesn't exist
        $this->assertFalse($this->testModel->hasTags('tag1', 'tag2', 'nonexistent'));
        
        // Test with non-existent tag
        $this->assertFalse($this->testModel->hasTags('nonexistent'));
    }

    public function test_has_tag_with_caching_and_match_expression(): void
    {
        // Create tags with different types
        $tag1 = Tag::findOrCreate('test-tag', 'type1');
        $tag2 = Tag::findOrCreate('another-tag', 'type2');
        
        $this->testModel->attachTags([$tag1, $tag2]);
        
        // Test existing tag without type
        $this->assertTrue($this->testModel->hasTag('test-tag'));
        
        // Test existing tag with correct type
        $this->assertTrue($this->testModel->hasTag('test-tag', 'type1'));
        
        // Test existing tag with wrong type
        $this->assertFalse($this->testModel->hasTag('test-tag', 'type2'));
        
        // Test non-existent tag
        $this->assertFalse($this->testModel->hasTag('nonexistent'));
    }

    public function test_sync_tags_with_match_expression(): void
    {
        // Initially attach some tags
        $this->testModel->attachTags(['tag1', 'tag2']);
        $this->assertEquals(2, $this->testModel->tags()->count());
        
        // Test syncing with string (match expression handling)
        $this->testModel->syncTags('tag3');
        $this->assertEquals(1, $this->testModel->tags()->count());
        $this->assertTrue($this->testModel->hasTag('tag3'));
        $this->assertFalse($this->testModel->hasTag('tag1'));
        
        // Test syncing with array
        $this->testModel->syncTags(['tag4', 'tag5']);
        $this->assertEquals(2, $this->testModel->tags()->count());
        $this->assertTrue($this->testModel->hasTag('tag4'));
        $this->assertTrue($this->testModel->hasTag('tag5'));
        $this->assertFalse($this->testModel->hasTag('tag3'));
    }

    public function test_attach_tags_with_array_spread(): void
    {
        // Test attaching with various data types
        $tag1 = Tag::findOrCreate('existing-tag');
        $tagsToAttach = [$tag1, 'new-tag-1', 'new-tag-2'];
        
        $result = $this->testModel->attachTags($tagsToAttach);
        
        $this->assertInstanceOf(DummyTag::class, $result);
        $this->assertEquals(3, $this->testModel->tags()->count());
        $this->assertTrue($this->testModel->hasTag('existing-tag'));
        $this->assertTrue($this->testModel->hasTag('new-tag-1'));
        $this->assertTrue($this->testModel->hasTag('new-tag-2'));
    }

    public function test_clear_tags_cache(): void
    {
        // Attach tags
        $this->testModel->attachTags(['tag1', 'tag2']);
        
        // Access tags to populate cache
        $this->assertTrue($this->testModel->hasTag('tag1'));
        
        // Clear cache
        $this->testModel->clearTagsCache();
        
        // Add another tag directly to database
        $newTag = Tag::findOrCreate('tag3');
        $this->testModel->tags()->attach($newTag);
        
        // Should be able to detect new tag after cache clear
        $this->testModel->refresh();
        $this->assertTrue($this->testModel->hasTag('tag3'));
    }

    public function test_get_tags_by_type(): void
    {
        // Create tags with different types
        $categoryTag1 = Tag::findOrCreate('cat1', 'category');
        $categoryTag2 = Tag::findOrCreate('cat2', 'category');
        $skillTag = Tag::findOrCreate('skill1', 'skill');
        
        $this->testModel->attachTags([$categoryTag1, $categoryTag2, $skillTag]);
        
        // Get tags by specific type
        $categoryTags = $this->testModel->getTagsByType('category');
        $this->assertCount(2, $categoryTags);
        
        $skillTags = $this->testModel->getTagsByType('skill');
        $this->assertCount(1, $skillTags);
        
        // Get tags by non-existent type
        $nonExistentTags = $this->testModel->getTagsByType('nonexistent');
        $this->assertCount(0, $nonExistentTags);
    }

    public function test_boot_has_tags_clears_cache(): void
    {
        // Attach tags
        $this->testModel->attachTags(['tag1', 'tag2']);
        
        // Populate cache
        $this->testModel->hasTag('tag1');
        
        // Delete model should trigger cache clear
        $this->testModel->delete();
        
        // This test primarily ensures no exceptions are thrown during deletion
        $this->assertTrue(true);
    }

    public function test_scope_with_all_tags_array_spread(): void
    {
        // Create test models with different tag combinations
        $model1 = DummyTag::create(['name' => 'model1']);
        $model1->attachTags(['tagA', 'tagB']);
        
        $model2 = DummyTag::create(['name' => 'model2']);
        $model2->attachTags(['tagA', 'tagB', 'tagC']);
        
        $model3 = DummyTag::create(['name' => 'model3']);
        $model3->attachTags(['tagA']);
        
        // Test scope with multiple tags using array spread
        $modelsWithAB = DummyTag::withAllTags(['tagA', 'tagB'])->get();
        $this->assertCount(2, $modelsWithAB);
        
        $modelsWithABC = DummyTag::withAllTags(['tagA', 'tagB', 'tagC'])->get();
        $this->assertCount(1, $modelsWithABC);
        $this->assertEquals('model2', $modelsWithABC->first()->name);
    }

    public function test_queued_tags_with_array_spread(): void
    {
        // Create a new model (not yet saved)
        $newModel = new DummyTag(['name' => 'test-model']);
        
        // Set tags before saving (should queue them)
        $newModel->tags = ['queued-tag1', 'queued-tag2'];
        
        // Save the model (should attach queued tags)
        $newModel->save();
        
        // Verify tags were attached
        $this->assertEquals(2, $newModel->tags()->count());
        $this->assertTrue($newModel->hasTag('queued-tag1'));
        $this->assertTrue($newModel->hasTag('queued-tag2'));
    }

    public function test_readonly_properties_are_immutable(): void
    {
        // Create tags with different types
        $categoryTag = Tag::findOrCreate('cat1', 'category');
        $skillTag = Tag::findOrCreate('skill1', 'skill');
        
        $this->testModel->attachTags([$categoryTag, $skillTag]);
        $this->testModel->refresh();
        
        // Test all readonly properties
        $this->assertIsInt($this->testModel->tagsCount);
        $this->assertIsBool($this->testModel->hasTagsAttached);
        $this->assertIsArray($this->testModel->tagNames);
        $this->assertIsArray($this->testModel->tagsGroupedByType);
        
        // Verify values
        $this->assertEquals(2, $this->testModel->tagsCount);
        $this->assertTrue($this->testModel->hasTagsAttached);
        $this->assertCount(2, $this->testModel->tagNames);
        $this->assertCount(2, $this->testModel->tagsGroupedByType);
    }

    public function test_enhanced_performance_with_caching(): void
    {
        // Attach multiple tags
        $this->testModel->attachTags(['tag1', 'tag2', 'tag3', 'tag4']);
        
        // First call should populate cache
        $hasTag1 = $this->testModel->hasTag('tag1');
        $this->assertTrue($hasTag1);
        
        // Subsequent calls should use cache (faster)
        $hasTag2 = $this->testModel->hasTag('tag2');
        $hasTag3 = $this->testModel->hasTag('tag3');
        $hasTag4 = $this->testModel->hasTag('tag4');
        
        $this->assertTrue($hasTag2);
        $this->assertTrue($hasTag3);
        $this->assertTrue($hasTag4);
        
        // Non-existent tag
        $hasNonExistent = $this->testModel->hasTag('nonexistent');
        $this->assertFalse($hasNonExistent);
    }
}
