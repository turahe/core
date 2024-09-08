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

namespace Turahe\Core\Tests\Unit\Resources;

use Turahe\Core\Tests\TestCase;
use Turahe\Contacts\Models\Contact;
use Turahe\Core\Export\Exceptions\InvalidExportTypeException;
use Turahe\Core\Facades\Fields;
use Turahe\Core\Facades\Innoclapps;
use Turahe\Core\Fields\DateTime;
use Turahe\Core\Fields\Text;
use Turahe\Core\Resource\Export;

class ExportTest extends TestCase
{
    public function test_it_uses_resource_name_as_export_filename()
    {
        $export = $this->createExportInstance();

        $this->assertSame('contacts', $export->fileName());
    }

    public function test_export_has_headings()
    {
        Fields::replace('contacts', [
            Text::make('first_name', 'First name'),
            Text::make('last_name', 'Last name'),
        ]);

        $export = $this->createExportInstance();

        $this->assertEquals(['First name', 'Last name'], $export->headings());
    }

    public function test_it_adds_the_application_timezone_in_date_headings()
    {
        Fields::replace('contacts', [
            DateTime::make('created_at', 'Created At'),
        ]);

        $export = $this->createExportInstance();

        $this->assertStringContainsString(config('app.timezone'), $export->headings()[0]);
    }

    public function test_it_can_specify_export_type()
    {
        $this->signIn();
        $export = $this->createExportInstance();

        $download = $export->download('xls');

        $this->assertSame('xls', $download->getFile()->getExtension());
    }

    public function test_it_uses_default_export_type_when_not_provided()
    {
        $this->signIn();
        $export = $this->createExportInstance();

        $this->assertSame(Export::DEFAULT_TYPE, $export->download()->getFile()->getExtension());
    }

    public function test_cannot_perform_export_with_invalid_type()
    {
        $this->expectException(InvalidExportTypeException::class);
        $export = $this->createExportInstance();

        $export->download('invalid');
    }

    public function test_it_excludes_fields_from_export()
    {
        Fields::replace('contacts', [
            Text::make('first_name', 'First name'),
            Text::make('last_name', 'Last name')->excludeFromExport(),
        ]);

        $export = $this->createExportInstance();

        $this->assertCount(1, $export->resolveFields());
    }

    public function test_it_creates_the_export_collection()
    {
        $this->signIn();

        Contact::factory()->count(2)->create();

        $export = $this->createExportInstance();

        $this->assertCount(2, $export->collection());
    }

    public function test_export_queries_are_properly_executed_in_chunks()
    {
        $this->signIn();

        Contact::factory()->count(2)->create();
        $defaultChunkSize = Export::$chunkSize;
        Export::$chunkSize = 1;

        $export = $this->createExportInstance();

        $this->assertCount(2, $export->collection());

        Export::$chunkSize = $defaultChunkSize;
    }

    protected function createExportInstance()
    {
        $resource = Innoclapps::resourceByName('contacts');

        return new Export($resource, $resource->newQuery());
    }
}
