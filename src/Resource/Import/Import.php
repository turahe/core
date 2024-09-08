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

namespace Turahe\Core\Resource\Import;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Validator as LaravelValidator;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use Turahe\Core\Facades\Innoclapps;
use Turahe\Core\Fields\Field;
use Turahe\Core\Fields\FieldsCollection;
use Turahe\Core\Models\Import as ImportModel;
use Turahe\Core\Models\Model;
use Turahe\Core\Resource\Http\ImportRequest;
use Turahe\Core\Resource\Http\ResourceRequest;
use Turahe\Core\Resource\Resource;
use Turahe\Core\Rules\UniqueResourceRule;
use Turahe\Users\Models\User;

class Import extends DefaultValueBinder implements SkipsEmptyRows, ToArray, WithChunkReading, WithCustomValueBinder, WithEvents, WithHeadingRow, WithMapping
{
    use ProvidesImportableFields, RegistersEventListeners;

    /**
     * Count of imported records for the current import
     */
    protected int $imported = 0;

    /**
     * Count of skipped records for the current import
     */
    protected int $skipped = 0;

    /**
     * Count of duplicate records for the current import
     */
    protected int $duplicates = 0;

    /**
     * The file read chunk size
     */
    protected int $chunkSize = 1000;

    /**
     * The maximum rows per import limit
     */
    protected static int $maxRows;

    /**
     * Instance of the import model
     */
    protected ?ImportModel $import = null;

    /**
     * Duplicates lookup callback
     *
     * @var callable|null
     */
    protected $lookupForDuplicatesUsing;

    /**
     * Perform callback on after save
     *
     * @var callable|null
     */
    protected $afterSaveCalback;

    /**
     * The current request that is in the loop for importing
     */
    public static ?ImportRequest $currentRequest = null;

    /**
     * Cached mappings
     */
    protected ?Collection $mappings = null;

    /**
     * @var Failure[]
     */
    protected array $failures = [];

    /**
     * Columns with fields mappings
     */
    protected array $columnsMappings = [];

    /**
     * Create new Import instance
     */
    public function __construct(protected Resource $resource)
    {
        static::$maxRows = (int) config('core.import.max_rows');
    }

    /**
     * Start the import process
     */
    public function perform(ImportModel $import): void
    {
        $this->import = $import;
        $this->imported = $import->imported ?: 0;
        $this->duplicates = $import->duplicates ?: 0;

        try {
            $import->fill(['status' => 'in-progress'])->save();

            $this->performExcelImport($import->file_path, $import::disk());

            if ($import->skip_file_path) {
                $import->removeFile($import->skip_file_path);
            }

            if ($this->skipped > 0) {
                $skipFilePath = $this->createSkipFile($import);
            }

            $import->fill([
                'skip_file_path' => $skipFilePath ?? null,
                'status' => 'finished',
                'imported' => $this->imported,
                'skipped' => $this->skipped,
                'duplicates' => $this->duplicates,
            ])->save();
        } catch (\Exception $e) {
            $import->fill(['status' => 'mapping'])->save();

            throw $e;
        }
    }

    /**
     * Initiate new import from the given file and start mapping the fields.
     */
    public function upload(UploadedFile $file, User $user): ImportModel
    {
        $path = $this->storeFile($file, $model = new ImportModel);

        $model->fill([
            'file_path' => $path,
            'resource_name' => $this->resource->name(),
            'user_id' => $user->getKey(),
            'status' => 'mapping',
            'imported' => 0,
            'duplicates' => 0,
            'skipped' => 0,
            'data' => [
                'mappings' => $this->createMappings($path),
            ],
        ])->save();

        return $model;
    }

    /**
     * Upload new fixed skip file
     */
    public function uploadViaSkipFile(UploadedFile $file, ImportModel $model): void
    {
        $model->removeFile($model->file_path);
        $path = $this->storeFile($file, $model);

        $model->fill([
            'file_path' => $path,
            'status' => 'mapping',
            'data' => array_merge($model->data, [
                'mappings' => $this->createMappings($path),
            ]),
        ])->save();
    }

    /**
     * Create mappings for the given path
     */
    protected function createMappings(string $path): array
    {
        return (new HeadingsMapper(
            $path,
            $this->resolveFields(),
            ImportModel::disk(),
        ))->map();
    }

    /**
     * Create skip file
     */
    protected function createSkipFile(ImportModel $import): string
    {
        $generator = new SkipFileGenerator(
            $import,
            $this->failures(),
            $this->mappings()
        );

        return $generator->store();
    }

    /**
     * Store the imported file
     *
     * @return string|false
     */
    protected function storeFile(UploadedFile $file, ImportModel $import): string|bool
    {
        return $file->storeAs($import->storagePath(), $file->getClientOriginalName(), $import::disk());
    }

    /**
     * Add callback for duplicates validation
     */
    public function lookupForDuplicatesUsing(callable $callback): static
    {
        $this->lookupForDuplicatesUsing = $callback;

        return $this;
    }

    /**
     * Add callback for after save
     */
    public function afterSave(callable $callback): static
    {
        $this->afterSaveCalback = $callback;

        return $this;
    }

    /**
     * Provide the resource fields
     */
    public function fields(): FieldsCollection
    {
        return $this->resource->resolveFields();
    }

    /**
     * Handle the import and validation
     *
     * @return void
     */
    public function array(array $rows)
    {
        $this->createRequestsCollection($rows)->each(function ($request) {
            try {
                static::$currentRequest = $request;

                $this->validate($request);

                $this->save($request);

                static::$currentRequest = null;
            } catch (RowSkippedException) {
            }
        });
    }

    /**
     * Validate the given request
     */
    protected function validate(ImportRequest $request): ?bool
    {
        try {
            $validator = $this->createValidator($request->all());

            $request->runValidationCallbacks($validator);

            $validator->setData($request->all());

            $validator->validate();
        } catch (ValidationException $e) {
            $failures = [];

            foreach ($e->errors() as $attribute => $messages) {
                $failures[] = $this->newFailureInstance($request, $attribute, $messages);
            }

            $this->onFailure(...$failures);

            throw new RowSkippedException(...$failures);
        }

        return true;
    }

    /**
     * Create new failure instance
     */
    protected function newFailureInstance(ImportRequest $request, string $attribute, array|string $messages): Failure
    {
        return new Failure(
            $request->rowNumber,
            $attribute,
            (array) $messages,
            array_merge($request->all(), ['_original' => $request->original()])
        );
    }

    /**
     * Create requests collection
     */
    protected function createRequestsCollection(array $rows): LazyCollection
    {
        // We will create lazy collection because of the weight of the requests.
        // However, is it needed as they are not stored in a variable?
        return LazyCollection::make(function () use ($rows) {
            $i = 0;
            $count = count($rows);

            /** @var \Turahe\Core\Resource\Http\ImportRequest */
            $sample = app(ImportRequest::class);

            while ($i < $count) {
                $request = (clone $sample)
                    ->setFields($this->resolveFields())
                    ->replace($rows[$i])
                    ->setOriginal($rows[$i]);

                $request->rowNumber = $i;

                yield $request;

                $i++;
            }
        });
    }

    /**
     * Hydrate the given request with data.
     */
    protected function hydrateRequest(ImportRequest $request): ImportRequest
    {
        foreach ($this->resolveFields() as $field) {
            // We will check if the actual field is found in the csv row
            // if not, we will just remove from the request to prevent any issues
            if ($request->missing($field->attribute)) {
                $request->replace($request->except($field->attribute));

                continue;
            }

            $value = $request->input($field->attribute);

            if ($attributes = $field->resolveForImport(
                is_string($value) ? trim($value) : $value,
                $request->all(),
                $request->original()
            )) {
                $request->merge($attributes);
            }
        }

        return $request->replace(
            collect($request->all())->filter()->all()
        );
    }

    /**
     * Handle row validation failure
     */
    protected function onFailure(Failure ...$failures)
    {
        $this->skipped++;
        $this->failures = array_merge($this->failures, $failures);
    }

    /**
     * @return Failure[]|\Illuminate\Support\Collection
     */
    public function failures(): Collection
    {
        return new Collection($this->failures);
    }

    /**
     * Get all of the mappings intended for the current import
     */
    protected function mappings(): Collection
    {
        return $this->mappings ??= collect($this->import->data['mappings'])
            ->reject(function ($column) {
                return $column['skip'] || ! $column['attribute'];
            });
    }

    /**
     * @param  mixed  $row
     */
    public function map($row): array
    {
        return $this->mappings()->reduce(function ($carry, $column) use ($row) {
            $carry[$column['attribute']] = $row[$column['original']];

            return $carry;
        }, []);
    }

    /**
     * Handle the model save for the given request
     */
    protected function save(ImportRequest&ResourceRequest $request): void
    {
        $request = $this->hydrateRequest($request);

        if ($record = $this->searchForDuplicateRecord($request)) {
            if ($record->usesSoftDeletes() && $record->trashed()) {
                $record->restore();
            }

            $record = $this->updateRecord($record, $request);
        } else {
            $record = $this->createRecord($request);
        }

        if ($this->afterSaveCalback) {
            call_user_func_array($this->afterSaveCalback, [$record, $request]);
        }
    }

    /**
     * Create record
     */
    protected function createRecord(ResourceRequest $request): Model
    {
        return tap($request->resource()
            ->setModel(null)
            ->resourcefulHandler($request)
            ->store(), function ($record) {
                $this->imported++;
            });
    }

    /**
     * Update record
     */
    protected function updateRecord(Model $record, ResourceRequest $request): Model
    {
        return tap($request->resource()
            ->setModel($record)
            ->resourcefulHandler($request->setRecord($record))
            ->update($record), function ($record) {
                $this->duplicates++;
            });
    }

    /**
     * Perform excel import
     */
    protected function performExcelImport(string $filePath, string $disk): void
    {
        Innoclapps::setImportStatus('in-progress');

        try {
            Excel::import($this, $filePath, $disk, \Maatwebsite\Excel\Excel::CSV);
        } finally {
            Innoclapps::setImportStatus(false);
        }
    }

    /**
     * Try to find duplicate record from the request
     */
    protected function searchForDuplicateRecord(ImportRequest $request): ?Model
    {
        // First, we need to check duplicates based on any unique custom fields
        // because the fields consist of a unique index which does not allow duplicates inserting
        // in this case, we must make sure to update them instead of try to create the record
        if ($record = $request->findRecordFromUniqueCustomFields()) {
            return $record;
        }

        if (is_callable($this->lookupForDuplicatesUsing)) {
            return call_user_func($this->lookupForDuplicatesUsing, $request);
        }

        return null;
    }

    /**
     * Prepare the validator for the given data
     */
    protected function createValidator(array $data): LaravelValidator
    {
        return Validator::make(
            $data,
            $this->rules(),
            $this->customValidationMessages(),
            $this->customValidationAttributes()
        );
    }

    /**
     * Provide custom error messages for import
     */
    public function customValidationMessages(): array
    {
        return $this->resolveFields()->map(function (Field $field) {
            return $field->prepareValidationMessages();
        })->filter()
            ->collapse()
            ->mapWithKeys(function ($message, $attribute) {
                return [$attribute => $message];
            })
            ->all();
    }

    /**
     * Provide custom attributes for the validation rules
     */
    public function customValidationAttributes(): array
    {
        return $this->resolveFields()->mapWithKeys(function (Field $field) {
            return [$field->attribute => Str::lower(strip_tags($field->label))];
        })->all();
    }

    /**
     * Provide the import validation rules
     */
    public function rules(): array
    {
        $formatted = [];

        foreach ($this->resolveFields() as $field) {
            $rules = $field->getImportRules();
            $attributes = array_keys($rules);

            foreach ($attributes as $attribute) {
                $formatted[$attribute] = collect($rules[$attribute])->reject(
                    fn ($rule) => $rule instanceof UniqueResourceRule && $rule->skipOnImport
                );
            }
        }

        return $formatted;
    }

    /**
     * Check whether any import is in progress.
     */
    public static function isImportInProgress(): bool
    {
        return ImportModel::inProgress()->count() > 0;
    }

    /**
     * Before import event handler
     */
    public static function beforeImport(BeforeImport $event)
    {
        // Subtract the heading row
        if (($event->getReader()->getTotalRows()['Worksheet'] - 1) > static::$maxRows) {
            throw new RowsExceededException('The maximum rows ('.static::$maxRows.') allowed in import file may have exceeded. Consider splitting the import data in multiple files.');
        }

        // Disable the query log to reduce memory usage.
        if (! app()->isProduction()) {
            DB::disableQueryLog();
        }

        Innoclapps::disableNotifications();
    }

    /**
     * After import event handler
     */
    public static function afterImport(AfterImport $event)
    {
        Innoclapps::enableNotifications();
    }

    /**
     * Value binder handler
     *
     * @param  mixed  $value
     * @return mixed
     */
    public function bindValue(Cell $cell, $value)
    {
        $column = $cell->getColumn();
        $rowIdx = $cell->getRow();

        // The first row is always the headings, no special values are needed here, in this case
        // we will use the first row to map the columns with the fields e.q. A => Field
        if ($rowIdx === 1) {
            if ($mapping = $this->mappings()->where('original', $value)->first()) {
                $this->columnsMappings[$column] = $this->resolveFields()->find(
                    $mapping['attribute']
                );
            }

            // Let's make the headings to be always string without any formatting
            // So they are consistent over the application
            $cell->setValueExplicit($value, DataType::TYPE_STRING);

            return true;
        } elseif ($rowIdx > 1) {
            // In this stage it's safe to assume that when the row is > 1, here are the actual values
            // will check if any field has defined custom import value data type and will bind it to the cell
            $field = $this->columnsMappings[$column] ?? null;

            if ($field && method_exists($field, 'importValueDataType')) {
                $cell->setValueExplicit($value, $field->importValueDataType());

                return true;
            }
        }

        // default behavior
        return parent::bindValue($cell, $value);
    }

    public function chunkSize(): int
    {
        return $this->chunkSize;
    }
}
