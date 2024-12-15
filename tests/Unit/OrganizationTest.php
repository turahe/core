<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Turahe\Core\Enums\OrganizationType;
use Turahe\Core\Models\Organization;
use Turahe\Core\Repositories\OrganizationRepository;
use Turahe\Core\Tests\Feature\Factories\OrganizationFactory;
use Turahe\Core\Tests\TestCase;

class OrganizationTest extends TestCase
{
    public function test_can_list_all_organizations(): void
    {
        $count = 5;
        OrganizationFactory::new()->count($count)->create();

        $orgRepo = new OrganizationRepository(new Organization);
        $organizations = $orgRepo->getOrganizations();

        $this->assertInstanceOf(Collection::class, $organizations);
        $this->assertCount($count, $organizations->all()); // +1 in the TestCase
    }

    public function test_cannot_get_the_organization(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $orgRepo = new OrganizationRepository(new Organization);
        $orgRepo->getOrganization('123');
        $orgRepo->getOrganizationByName('Organization Name');
        $orgRepo->getOrganizationBySlug('org-slug');

    }

    public function test_can_force_delete_the_organization(): void
    {
        $organization = OrganizationFactory::new()->create();

        $orgRepo = new OrganizationRepository($organization);
        $deleted = $orgRepo->deleteOrganization();

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('organizations', []);
    }

    public function test_can_delete_the_organization(): void
    {
        $organization = OrganizationFactory::new()->create();

        $orgRepo = new OrganizationRepository($organization);
        $deletedTrash = $orgRepo->trashOrganization();

        $this->assertTrue($deletedTrash);
        $this->assertSoftDeleted('organizations', ['id' => $organization->getKey()]);
    }

    public function test_can_update_the_organization(): void
    {
        $organizationFactory = OrganizationFactory::new()->create();

        $data = [
            'name' => $this->faker->company,
            'code' => $this->faker->randomNumber(9),
            'type' => OrganizationType::Organization,
        ];

        $orgRepo = new OrganizationRepository($organizationFactory);
        $updated = $orgRepo->updateOrganization($data);

        $org = $orgRepo->getOrganizationBySlug($organizationFactory->slug);

        $this->assertTrue($updated);
        $this->assertEquals($data['name'], $org->name);
        $this->assertEquals($data['code'], $org->code);
        $this->assertEquals($data['type'], $org->type);
    }

    public function test_can_create_a_organization(): void
    {
        $data = [
            'name' => $this->faker->company,
            'code' => $this->faker->randomNumber(9),
            'type' => OrganizationType::Organization,
        ];

        $orgRepo = new OrganizationRepository(new Organization);
        $org = $orgRepo->createOrganization($data);

        $this->assertInstanceOf(Organization::class, $org);
        $this->assertEquals($data['name'], $org->name);
        $this->assertEquals($data['code'], $org->code);
        $this->assertEquals($data['type'], $org->type);
    }

    public function test_errors_creating_the_organization(): void
    {
        $this->expectException(\Exception::class);

        $orgRepo = new OrganizationRepository(new Organization);
        $orgRepo->createOrganization([]);
    }
}
