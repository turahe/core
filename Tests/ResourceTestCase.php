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

namespace Turahe\Core\Tests;

use Database\Seeders\CustomFieldsSeeder;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use Turahe\Core\Contracts\Resources\AcceptsCustomFields;
use Turahe\Core\Facades\Fields;
use Turahe\Core\Facades\Innoclapps;
use Turahe\Core\Fields\Boolean;
use Turahe\Core\Fields\Date;
use Turahe\Core\Fields\DateTime;
use Turahe\Core\Fields\Email;
use Turahe\Core\Fields\Number;
use Turahe\Core\Fields\Numeric;
use Turahe\Core\Fields\Timezone;
use Turahe\Core\Models\Import;
use Turahe\Core\Models\Model;
use Turahe\Core\Resource\Http\ResourceRequest;
use Turahe\Core\Table\Column;
use Turahe\Core\Tests\Concerns\TestsCustomFields;
use Turahe\Core\Tests\Concerns\TestsImportAndExport;

class ResourceTestCase extends TestCase
{
    use TestsCustomFields, TestsImportAndExport;

    protected $resourceName;

    protected function setUp(): void
    {
        parent::setUp();

        if ($this->resource() instanceof AcceptsCustomFields &&
                str_contains($this->name(), 'custom_fields')) {
            $this->seedCustomFields();
        }
    }

    public function test_unauthenticated_user_cannot_access_resource_endpoints()
    {
        $this->getJson($this->indexEndpoint())->assertUnauthorized();
        $this->getJson($this->showEndpoint(1))->assertUnauthorized();
        $this->postJson($this->createEndpoint())->assertUnauthorized();
        $this->putJson($this->updateEndpoint(1))->assertUnauthorized();
        $this->deleteJson($this->deleteEndpoint(1))->assertUnauthorized();
    }

    protected function seedCustomFields()
    {
        $originalResource = CustomFieldsSeeder::$resourceName;

        CustomFieldsSeeder::$resourceName = $this->resourceName;
        $this->seed(CustomFieldsSeeder::class);
        CustomFieldsSeeder::$resourceName = $originalResource;
        Fields::flushLoadedCache();
        Model::clearGuardableCache();
    }

    protected function resource()
    {
        return Innoclapps::resourceByName($this->resourceName);
    }

    protected function tableName()
    {
        return $this->resource()->newModel()->getTable();
    }

    protected function factory()
    {
        return $this->resource()->newModel()->factory();
    }

    protected function endpoint()
    {
        return "/api/{$this->resourceName}";
    }

    protected function indexEndpoint()
    {
        return $this->endpoint();
    }

    protected function createEndpoint()
    {
        return $this->endpoint();
    }

    protected function updateEndpoint($record)
    {
        $id = is_int($record) ? $record : $record->getKey();

        return "{$this->endpoint()}/{$id}";
    }

    protected function showEndpoint($record)
    {
        $id = is_int($record) ? $record : $record->getKey();

        return "{$this->endpoint()}/{$id}";
    }

    protected function deleteEndpoint($record)
    {
        $id = is_int($record) ? $record : $record->getKey();

        return "{$this->endpoint()}/{$id}";
    }

    protected function forceDeleteEndpoint($record)
    {
        $id = is_int($record) ? $record : $record->getKey();

        return "/api/trashed/{$this->resourceName}/{$id}";
    }

    protected function actionEndpoint($action)
    {
        $uriKey = (is_string($action) ? $this->findAction($action) : $action)->uriKey();

        return "/{$this->endpoint()}/actions/{$uriKey}/run";
    }

    protected function importUploadEndpoint()
    {
        return "/api/{$this->resourceName}/import/upload";
    }

    protected function importEndpoint($import)
    {
        $id = is_int($import) ? $import : $import->getKey();

        return "/api/{$this->resourceName}/import/{$id}";
    }

    protected function tableEndpoint()
    {
        return "/api/{$this->resourceName}/table";
    }

    protected function performImportUpload($fakeFile)
    {
        $this->signIn();

        Storage::fake('local');

        $this->postJson($this->importUploadEndpoint(), [
            'file' => $fakeFile,
        ]);

        return Import::latest()->first();
    }

    protected function findAction($uriKey)
    {
        return collect($this->resource()->actions(app(ResourceRequest::class)))
            ->first(fn ($action) => $action->uriKey() == $uriKey);
    }

    protected function customFields()
    {
        return $this->resource()->getFields()->filter->isCustomField();
    }

    protected function findCustomField($fieldId)
    {
        return $this->customFields()->first(function ($field) use ($fieldId) {
            return $field->customField->field_id === $fieldId;
        })->customField;
    }

    protected function customFieldsLabels()
    {
        return $this->customFields()->pluck('label')->all();
    }

    protected function assertThatDatabaseHasCustomFieldsValues()
    {
        $this->assertDatabaseHas($this->tableName(), [
            'cf_custom_field_boolean' => '1',
            'cf_custom_field_date' => '2021-12-05 00:00:00',
            'cf_custom_field_datetime' => '2021-12-05 10:00:00',
            'cf_custom_field_work_email' => 'info@wach.id',
            'cf_custom_field_number' => '200',
            'cf_custom_field_numeric' => '1250',
            'cf_custom_field_radio' => $this->findCustomField('cf_custom_field_radio')->options->first()->getKey(),
            'cf_custom_field_select' => $this->findCustomField('cf_custom_field_select')->options->first()->getKey(),
            'cf_custom_field_text' => 'test-value',
            'cf_custom_field_textarea' => 'test-value',
            'cf_custom_field_timezone' => 'Europe/Berlin',
        ]);

        $this->assertDatabaseHas('model_has_custom_field_options', [
            'custom_field_id' => (string) $this->findCustomField('cf_custom_field_multiselect')->id,
            'option_id' => (string) $this->findCustomField('cf_custom_field_multiselect')->options->first()->getKey(),
        ]);

        $this->assertDatabaseHas('model_has_custom_field_options', [
            'custom_field_id' => (string) $this->findCustomField('cf_custom_field_checkbox')->id,
            'option_id' => (string) $this->findCustomField('cf_custom_field_checkbox')->options->first()->getKey(),
        ]);
    }

    public function assertThatResponseHasCustomFieldsValues($response)
    {
        $response->assertJson(function (AssertableJson $json) {
            $json->where('cf_custom_field_timezone', 'Europe/Berlin')
                ->where('cf_custom_field_textarea', 'test-value')
                ->where('cf_custom_field_text', 'test-value')
                ->where('cf_custom_field_select', $this->findCustomField('cf_custom_field_select')->options->first()->getKey())
                ->where('cf_custom_field_radio', $this->findCustomField('cf_custom_field_radio')->options->first()->getKey())
                ->where('cf_custom_field_numeric', 1250)
                ->where('cf_custom_field_number', 200)
                ->has('cf_custom_field_multiselect', 1)
                ->where('cf_custom_field_multiselect.0.id', $this->findCustomField('cf_custom_field_multiselect')->options->first()->getKey())
                ->where('cf_custom_field_work_email', 'info@wach.id')
                ->where('cf_custom_field_datetime', Carbon::parse('2021-12-05 10:00:00')->toJSON())
                ->where('cf_custom_field_date', Carbon::parse('2021-12-05')->toJSON())
                ->where('cf_custom_field_boolean', true)
                ->has('cf_custom_field_multiselect', 1)
                ->where('cf_custom_field_checkbox.0.id', $this->findCustomField('cf_custom_field_checkbox')->options->first()->getKey())
                ->etc();
        });
    }

    protected function customFieldsPayload()
    {
        return $this->customFields()
            ->mapWithKeys(function ($field) {
                $value = 'test-value';

                if ($field->isMultiOptionable()) {
                    $value = [$field->customField->options->first()->getKey()];
                } elseif ($field->isOptionable()) {
                    $value = $field->customField->options->first()->getKey();
                } elseif ($field instanceof Date) {
                    $value = '2021-12-05';
                } elseif ($field instanceof DateTime) {
                    $value = '2021-12-05 10:00:00';
                } elseif ($field instanceof Timezone) {
                    $value = 'Europe/Berlin';
                } elseif ($field instanceof Numeric) {
                    $value = 1250.000;
                } elseif ($field instanceof Number) {
                    $value = 200;
                } elseif ($field instanceof Email) {
                    $value = 'info@wach.id';
                } elseif ($field instanceof Boolean) {
                    $value = true;
                }

                return [$field->attribute => $value];
            })->all();
    }

    protected function performImportTest()
    {
        $import = $this->performImportUpload($this->createFakeImportFile());

        $this->postJson($this->importEndpoint($import), [
            'mappings' => $import->data['mappings'],
        ])->assertOk();

        $this->assertDatabaseCount($this->tableName(), 1);
    }

    protected function performImportWithCustomFieldsTest()
    {
        $this->signIn();
        $import = $this->performImportUpload($this->createFakeImportFile());

        $this->postJson($this->importEndpoint($import), [
            'mappings' => $import->data['mappings'],
        ])->assertOk();

        $this->assertDatabaseCount($this->tableName(), 1);
        $this->assertThatDatabaseHasCustomFieldsValues();
    }

    protected function performImportWithDuplicateTest($overrides)
    {
        $import = $this->performImportUpload($this->createFakeImportFile(
            [$this->createImportHeader(), $this->createImportRow($overrides)]
        ));

        $this->postJson($this->importEndpoint($import), [
            'mappings' => $import->data['mappings'],
        ])->assertOk();

        $this->assertDatabaseCount($this->tableName(), 1);
    }

    protected function createFakeImportFile($rows = null)
    {
        $rows = $rows ?: [$this->createImportHeader(), $this->createImportRow()];

        $tmpfile = tmpfile();

        foreach ($rows as $row) {
            fputcsv($tmpfile, $row);
        }

        return tap(new File('test.csv', $tmpfile), function ($file) use ($tmpfile) {
            $file->sizeToReport = fstat($tmpfile)['size'];
        });
    }

    protected function createImportHeader()
    {
        return $this->resource()->importable()->resolveFields()->map(function ($field) {
            return $field->label;
        })->all();
    }

    protected function createImportRow($overrides = [])
    {
        return $this->resource()->importable()->resolveFields()->mapWithKeys(function ($field) {
            return [$field->attribute => $field->sampleValueForImport()];
        })->merge($this->customFieldsPayload())->mapWithKeys(function ($value, $attribute) use ($overrides) {
            if (array_key_exists($attribute, $overrides)) {
                $value = $overrides[$attribute];
            }

            return [$attribute => $value];
        })->values()->map(function ($value) {
            if (is_array($value)) {
                return implode(',', $value);
            }

            return $value;
        })->all();
    }

    protected function performExportTest()
    {
        $this->signIn();

        $this->factory()->count(2)->create();

        try {
            $response = $this->postJson($this->endpoint().'/export', [
                'type' => 'csv',
                'period' => 'last_7_days',
            ])->assertOk()->assertDownload();

            $csvArray = $this->csvToArray($response->getFile()->getPathname());
            $this->assertCount(2, $csvArray);
            $export = $this->resource()->exportable($this->resource()->newQuery());
            $fields = $export->resolveFields();

            $records = $this->resource()->displayQuery()->latest()->get();

            foreach ($records as $key => $contact) {
                foreach ($fields as $field) {
                    if ($field instanceof Numeric) {
                        $this->assertEquals(
                            (float) $field->resolveForExport($contact),
                            (float) $csvArray[$key][$export->heading($field)]
                        );
                    } else {
                        $this->assertEquals(
                            $field->resolveForExport($contact),
                            $csvArray[$key][$export->heading($field)]
                        );
                    }
                }
            }
        } finally {
            if (is_file($response->getFile()->getPathname())) {
                unlink($response->getFile()->getPathname());
            }
        }
    }

    protected function performTestTableLoad()
    {
        $this->signIn();

        $this->factory()->count(5)->create();

        $this->getJson($this->tableEndpoint())
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total', 'all_time_total',
                ],
                'links' => ['first', 'last', 'prev', 'next'],
            ]);
    }

    protected function performTestTableCanLoadWithAllFields()
    {
        $this->signIn();

        $this->resource()->resolveFields()->filter->isApplicableForIndex()
            ->each(function ($field) {
                $field->tapIndexColumn(function (Column $column) {
                    $column->hidden(false);
                });
            });

        $attributes = $this->resource()->resolveFields()
            ->filter->isApplicableForIndex()
            ->map(fn ($field) => $field->resolveIndexColumn())
            ->filter()
            ->map(fn ($column) => $column->attribute)
            ->all();

        $this->factory()->count(5)->create();

        $this->getJson($this->tableEndpoint())
            ->assertOk()
            ->assertJsonCount(5, 'data')->assertJsonStructure([
                'data' => [$attributes],
            ]);
    }

    protected function createRequestForTable($user = null)
    {
        $user = $user ?: $this->createUser();
        $request = app(ResourceRequest::class);
        $request->setUserResolver(fn () => $user);

        return $request;
    }
}
