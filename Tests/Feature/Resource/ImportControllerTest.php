<?php
/*
 * This source code is the proprietary and confidential information of
 * Nur Wachid. You may not disclose, copy, distribute,
 *  or use this code without the express written permission of
 * Nur Wachid.
 *
 * Copyright (c) 2022-2023.
 *
 *
 */

namespace Turahe\Core\Tests\Feature\Resource;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Turahe\Core\Tests\TestCase;
use Turahe\Core\Database\Seeders\CountriesSeeder;
use Turahe\Core\Models\Import;

class ImportControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_import_endpoints()
    {
        $this->getJson('/api/contacts/import')->assertUnauthorized();
        $this->postJson('/api/contacts/import/upload')->assertUnauthorized();
        $this->postJson('/api/contacts/import/FAKE_ID')->assertUnauthorized();
        $this->deleteJson('/api/contacts/import/FAKE_ID')->assertUnauthorized();
        $this->getJson('/api/contacts/import/sample')->assertUnauthorized();
    }

    public function test_user_can_retrieve_all_imports()
    {
        $user = $this->signIn();

        $this->createFakeImport();

        $this->getJson('/api/contacts/import')
            ->assertJsonCount(1)
            ->assertJson([
                [
                    'file_name' => 'file.csv',
                    'resource_name' => 'contacts',
                    'status' => 'mapping',
                    'imported' => 0,
                    'skipped' => 0,
                    'duplicates' => 0,
                    'user_id' => $user->id,
                ],
            ]);
    }

    public function test_user_can_upload_import_file()
    {
        $this->signIn();

        Storage::fake('local');

        $this->postJson('/api/contacts/import/upload', [
            'file' => $this->createFakeImportFile(),
        ])->assertJson([
            'file_name' => 'test.csv',
            'resource_name' => 'contacts',
            'status' => 'mapping',
            'mappings' => [
                [
                    'original' => 'First Name',
                    'detected_attribute' => 'first_name',
                    'attribute' => 'first_name',
                    'preview' => 'John, Jane',
                    'skip' => false,
                    'auto_detected' => true,
                ],
                [
                    'original' => 'E-Mail Address',
                    'detected_attribute' => 'email',
                    'attribute' => 'email',
                    'preview' => 'john@example.com, jane@example.com',
                    'skip' => false,
                    'auto_detected' => true,
                ],
                [
                    'original' => 'NonExistent Field',
                    'detected_attribute' => null,
                    'attribute' => null,
                    'preview' => '',
                    'skip' => true,
                    'auto_detected' => false,
                ],
            ],
        ])->assertJsonStructure(['fields']);

        $import = Import::first();

        Storage::assertExists($import->file_path);
    }

    public function test_it_updates_the_import_mappings_before_importing_the_data()
    {
        $this->signIn();

        Storage::fake('local');

        $this->postJson('/api/contacts/import/upload', [
            'file' => $this->createFakeImportFile(),
        ]);

        $import = Import::first();

        $this->postJson("/api/contacts/import/{$import->id}", [
            'mappings' => $mappings = [
                [
                    'original' => 'First Name',
                    'detected_attribute' => 'first_name',
                    'attribute' => 'last_name',
                    'preview' => 'John, Jane',
                    'skip' => false,
                    'auto_detected' => true,
                ],
                [
                    'original' => 'E-Mail Address',
                    'detected_attribute' => 'email',
                    'attribute' => 'first_name',
                    'preview' => 'john@example.com, jane@example.com',
                    'skip' => false,
                    'auto_detected' => true,
                ],
                [
                    'original' => 'NonExistent Field',
                    'detected_attribute' => null,
                    'attribute' => 'email',
                    'preview' => '',
                    'skip' => true,
                    'auto_detected' => false,
                ],
            ],
        ]);

        $this->assertEquals($mappings, $import->fresh()->data['mappings']);
    }

    public function test_import_requires_mappings()
    {
        $this->signIn();

        $import = $this->createFakeImport();

        $this->postJson("/api/contacts/import/{$import->id}", [
            'mappings' => [],
        ])->assertJsonValidationErrors('mappings');
    }

    public function test_import_requires_mappings_auto_detected_attribute()
    {
        $this->signIn();

        $import = $this->createFakeImport();

        $this->postJson("/api/contacts/import/{$import->id}", [
            'mappings' => [
                [
                    'original' => 'E-Mail Address',
                    'detected_attribute' => 'email',
                    'attribute' => 'email',
                    'skip' => false,
                ],
            ],
        ])->assertJsonValidationErrorFor('mappings.0.auto_detected');
    }

    public function test_import_requires_mappings_original_attribute()
    {
        $this->signIn();

        $import = $this->createFakeImport();

        $this->postJson("/api/contacts/import/{$import->id}", [
            'mappings' => [
                [
                    'detected_attribute' => 'email',
                    'attribute' => 'email',
                    'skip' => false,
                    'auto_detected' => true,
                ],
            ],
        ])->assertJsonValidationErrorFor('mappings.0.original');
    }

    public function test_import_requires_mappings_skip_attribute()
    {
        $this->signIn();

        $import = $this->createFakeImport();

        $this->postJson("/api/contacts/import/{$import->id}", [
            'mappings' => [
                [
                    'original' => 'E-Mail Address',
                    'detected_attribute' => 'email',
                    'attribute' => 'email',
                    'auto_detected' => true,
                ],
            ],
        ])->assertJsonValidationErrorFor('mappings.0.skip');
    }

    public function test_import_requires_detected_attribute_to_be_present_in_the_mappings()
    {
        $this->signIn();

        $import = $this->createFakeImport();

        $this->postJson("/api/contacts/import/{$import->id}", [
            'mappings' => [
                [
                    'original' => 'E-Mail Address',
                    'attribute' => 'email',
                    'skip' => false,
                    'auto_detected' => true,
                ],
            ],
        ])->assertJsonValidationErrorFor('mappings.0.detected_attribute');
    }

    public function test_user_can_upload_only_csv_file()
    {
        $this->signIn();

        $this->postJson('/api/contacts/import/upload', [
            'file' => UploadedFile::fake()->image('photo.jpg'),
        ])->assertJsonValidationErrors(['file']);
    }

    public function test_user_can_delete_import()
    {
        $this->signIn();

        $import = $this->createFakeImport();

        $this->deleteJson("/api/contacts/import/{$import->id}")->assertNoContent();
    }

    public function test_user_can_download_import_sample()
    {
        $this->seed(CountriesSeeder::class);

        $this->signIn();

        $this->getJson('/api/contacts/import/sample')->assertDownload('sample.csv');
    }

    protected function createFakeImport($attributes = [])
    {
        return tap(new Import(array_merge([
            'file_path' => 'fake/path/file.csv',
            'resource_name' => 'contacts',
            'status' => 'mapping',
            'imported' => 0,
            'skipped' => 0,
            'duplicates' => 0,
            'user_id' => 1,
            'completed_at' => null,
            'data' => ['mappings' => []],
        ], $attributes)))->save();
    }

    protected function createFakeImportFile()
    {
        $header = 'First Name,E-Mail Address,NonExistent Field';
        $row1 = 'John,john@example.com';
        $row2 = 'Jane,jane@example.com';
        $content = implode("\n", [$header, $row1, $row2]);

        return UploadedFile::fake()->createWithContent(
            'test.csv',
            $content
        );
    }
}
