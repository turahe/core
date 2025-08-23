<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit\Repositories;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Turahe\Core\Enums\OrganizationType;
use Turahe\Core\Models\Organization;
use Turahe\Core\Repositories\OrganizationRepository;
use Turahe\Core\Tests\TestCase;

class OrganizationRepositoryTest extends TestCase
{
    private OrganizationRepository $repository;

    private Organization $organization;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organization = new Organization;
        $this->repository = new OrganizationRepository($this->organization);
    }

    public function test_get_organizations_returns_collection(): void
    {
        // Create test organizations
        Organization::create([
            'name' => 'Organization 1',
            'slug' => 'org-1',
            'type' => 'COMPANY',
        ]);

        Organization::create([
            'name' => 'Organization 2',
            'slug' => 'org-2',
            'type' => 'ORGANIZATION',
        ]);

        $result = $this->repository->getOrganizations('created_at', 'desc');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertGreaterThan(0, $result->count());
    }

    public function test_get_organizations_with_except_parameter(): void
    {
        $org1 = Organization::create([
            'name' => 'Organization 1',
            'slug' => 'org-1',
            'type' => 'COMPANY',
        ]);

        $org2 = Organization::create([
            'name' => 'Organization 2',
            'slug' => 'org-2',
            'type' => 'ORGANIZATION',
        ]);

        $result = $this->repository->getOrganizations('created_at', 'desc', [$org1->id]);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(1, $result->count());
        $this->assertFalse($result->contains($org1->id));
        $this->assertTrue($result->contains($org2->id));
    }

    public function test_get_organization_by_id(): void
    {
        $org = Organization::create([
            'name' => 'Test Organization',
            'slug' => 'test-org',
            'type' => 'COMPANY',
        ]);

        $result = $this->repository->getOrganization($org->id);

        $this->assertInstanceOf(Organization::class, $result);
        $this->assertEquals($org->id, $result->id);
        $this->assertEquals('Test Organization', $result->name);
    }

    public function test_get_organization_by_id_throws_exception_for_nonexistent(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->getOrganization('nonexistent-id');
    }

    public function test_get_organization_by_name(): void
    {
        $org = Organization::create([
            'name' => 'Unique Organization',
            'slug' => 'unique-org',
            'type' => 'COMPANY',
        ]);

        $result = $this->repository->getOrganizationByName('Unique Organization');

        $this->assertInstanceOf(Organization::class, $result);
        $this->assertEquals('Unique Organization', $result->name);
        $this->assertEquals($org->id, $result->id);
    }

    public function test_get_organization_by_name_throws_exception_for_nonexistent(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->getOrganizationByName('Nonexistent Organization');
    }

    public function test_get_organization_by_slug(): void
    {
        $org = Organization::create([
            'name' => 'Slug Organization',
            'slug' => 'slug-org',
            'type' => 'COMPANY',
        ]);

        $result = $this->repository->getOrganizationBySlug('slug-org');

        $this->assertInstanceOf(Organization::class, $result);
        $this->assertEquals('slug-org', $result->slug);
        $this->assertEquals($org->id, $result->id);
    }

    public function test_get_organization_by_slug_throws_exception_for_nonexistent(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->getOrganizationBySlug('nonexistent-slug');
    }

    public function test_create_organization(): void
    {
        $attributes = [
            'name' => 'New Organization',
            'slug' => 'new-org',
            'type' => 'COMPANY',
        ];

        $result = $this->repository->createOrganization($attributes);

        $this->assertInstanceOf(Organization::class, $result);
        $this->assertEquals('New Organization', $result->name);
        $this->assertEquals('new-org', $result->slug);
        $this->assertEquals(OrganizationType::Company, $result->type);
        $this->assertDatabaseHas('organizations', [
            'name' => 'New Organization',
            'slug' => 'new-org',
            'type' => 'COMPANY',
        ]);
    }

    public function test_update_organization(): void
    {
        $org = Organization::create([
            'name' => 'Original Name',
            'slug' => 'original-slug',
            'type' => 'COMPANY',
        ]);

        $this->repository = new OrganizationRepository($org);

        $attributes = [
            'name' => 'Updated Name',
            'type' => 'ORGANIZATION',
        ];

        $result = $this->repository->updateOrganization($attributes);

        $this->assertTrue($result);
        $this->assertDatabaseHas('organizations', [
            'id' => $org->id,
            'name' => 'Updated Name',
            'type' => 'ORGANIZATION',
        ]);
    }

    public function test_delete_organization(): void
    {
        $org = Organization::create([
            'name' => 'To Delete',
            'slug' => 'to-delete',
            'type' => 'COMPANY',
        ]);

        $this->repository = new OrganizationRepository($org);

        $result = $this->repository->deleteOrganization();

        $this->assertTrue($result);
        $this->assertDatabaseMissing('organizations', ['id' => $org->id]);
    }

    public function test_trash_organization(): void
    {
        $org = Organization::create([
            'name' => 'To Trash',
            'slug' => 'to-trash',
            'type' => 'COMPANY',
        ]);

        $this->repository = new OrganizationRepository($org);

        $result = $this->repository->trashOrganization();

        $this->assertTrue($result);
        $this->assertSoftDeleted('organizations', ['id' => $org->id]);
    }

    public function test_get_organizations_with_custom_ordering(): void
    {
        Organization::create([
            'name' => 'A Organization',
            'slug' => 'a-org',
            'type' => 'COMPANY',
        ]);

        Organization::create([
            'name' => 'B Organization',
            'slug' => 'b-org',
            'type' => 'ORGANIZATION',
        ]);

        $result = $this->repository->getOrganizations('name', 'asc');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertGreaterThan(0, $result->count());

        // Verify ordering (first item should start with 'A')
        $firstOrg = $result->first();
        $this->assertStringStartsWith('A', $firstOrg->name);
    }
}
