<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit\Repositories;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Turahe\Core\Models\Tag;
use Turahe\Core\Repositories\TagRepository;
use Turahe\Core\Tests\TestCase;

class TagRepositoryTest extends TestCase
{
    private TagRepository $repository;
    private Tag $tag;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->tag = new Tag();
        $this->repository = new TagRepository($this->tag);
    }

    public function test_get_tags_returns_collection(): void
    {
        // Create test tags
        Tag::create([
            'name' => 'Tag 1',
            'slug' => 'tag-1'
        ]);
        
        Tag::create([
            'name' => 'Tag 2',
            'slug' => 'tag-2'
        ]);
        
        $result = $this->repository->getTags('created_at', 'desc');
        
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertGreaterThan(0, $result->count());
    }

    public function test_get_tags_with_except_parameter(): void
    {
        $tag1 = Tag::create([
            'name' => 'Tag 1',
            'slug' => 'tag-1'
        ]);
        
        $tag2 = Tag::create([
            'name' => 'Tag 2',
            'slug' => 'tag-2'
        ]);
        
        $result = $this->repository->getTags('created_at', 'desc', [$tag1->id]);
        
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(1, $result->count());
        $this->assertFalse($result->contains($tag1->id));
        $this->assertTrue($result->contains($tag2->id));
    }

    public function test_get_tag_by_id(): void
    {
        $tag = Tag::create([
            'name' => 'Test Tag',
            'slug' => 'test-tag'
        ]);
        
        $result = $this->repository->getTag($tag->id);
        
        $this->assertInstanceOf(Tag::class, $result);
        $this->assertEquals($tag->id, $result->id);
        $this->assertEquals('Test Tag', $result->name);
    }

    public function test_get_tag_by_id_throws_exception_for_nonexistent(): void
    {
        $this->expectException(ModelNotFoundException::class);
        
        $this->repository->getTag('nonexistent-id');
    }

    public function test_get_tag_by_name(): void
    {
        $tag = Tag::create([
            'name' => 'Unique Tag',
            'slug' => 'unique-tag'
        ]);
        
        $result = $this->repository->getTagByName('Unique Tag');
        
        $this->assertInstanceOf(Tag::class, $result);
        $this->assertEquals('Unique Tag', $result->name);
        $this->assertEquals($tag->id, $result->id);
    }

    public function test_get_tag_by_name_throws_exception_for_nonexistent(): void
    {
        $this->expectException(ModelNotFoundException::class);
        
        $this->repository->getTagByName('Nonexistent Tag');
    }

    public function test_get_tag_by_slug(): void
    {
        $tag = Tag::create([
            'name' => 'Slug Tag',
            'slug' => 'slug-tag'
        ]);
        
        $result = $this->repository->getTagBySlug('slug-tag');
        
        $this->assertInstanceOf(Tag::class, $result);
        $this->assertEquals('slug-tag', $result->slug);
        $this->assertEquals($tag->id, $result->id);
    }

    public function test_get_tag_by_slug_throws_exception_for_nonexistent(): void
    {
        $this->expectException(ModelNotFoundException::class);
        
        $this->repository->getTagBySlug('nonexistent-slug');
    }

    public function test_create_tag(): void
    {
        $attributes = [
            'name' => 'New Tag',
            'slug' => 'new-tag'
        ];
        
        $result = $this->repository->createTag($attributes);
        
        $this->assertInstanceOf(Tag::class, $result);
        $this->assertEquals('New Tag', $result->name);
        $this->assertEquals('new-tag', $result->slug);
        $this->assertDatabaseHas('tags', $attributes);
    }

    public function test_update_tag(): void
    {
        $tag = Tag::create([
            'name' => 'Original Name',
            'slug' => 'original-slug'
        ]);
        
        $this->repository = new TagRepository($tag);
        
        $attributes = [
            'name' => 'Updated Name'
        ];
        
        $result = $this->repository->updateTag($attributes);
        
        $this->assertTrue($result);
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'Updated Name'
        ]);
    }

    public function test_delete_tag(): void
    {
        $tag = Tag::create([
            'name' => 'To Delete',
            'slug' => 'to-delete'
        ]);
        
        $this->repository = new TagRepository($tag);
        
        $result = $this->repository->deleteTag();
        
        $this->assertTrue($result);
        $this->assertSoftDeleted('tags', ['id' => $tag->id]);
    }

    public function test_get_tags_with_custom_ordering(): void
    {
        Tag::create([
            'name' => 'A Tag',
            'slug' => 'a-tag'
        ]);
        
        Tag::create([
            'name' => 'B Tag',
            'slug' => 'b-tag'
        ]);
        
        $result = $this->repository->getTags('name', 'asc');
        
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertGreaterThan(0, $result->count());
        
        // Verify ordering (first item should start with 'A')
        $firstTag = $result->first();
        $this->assertStringStartsWith('A', $firstTag->name);
    }

    public function test_get_tags_with_descending_order(): void
    {
        Tag::create([
            'name' => 'First Tag',
            'slug' => 'first-tag'
        ]);
        
        Tag::create([
            'name' => 'Second Tag',
            'slug' => 'second-tag'
        ]);
        
        $result = $this->repository->getTags('created_at', 'desc');
        
        $this->assertInstanceOf(Collection::class, $result);
        $this->assertGreaterThan(0, $result->count());
    }
}
