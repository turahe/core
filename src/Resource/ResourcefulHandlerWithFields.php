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

namespace Turahe\Core\Resource;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Turahe\Core\Fields\Field;
use Turahe\Core\Models\Model;
use Turahe\Core\Fields\MorphMany;
use Turahe\Core\Contracts\Services\CreateService;
use Turahe\Core\Contracts\Services\UpdateService;
use Turahe\Core\Resource\Events\ResourceRecordCreated;
use Turahe\Core\Resource\Events\ResourceRecordUpdated;
use Turahe\Core\Contracts\Resources\ResourcefulRequestHandler;
use Turahe\Core\Contracts\Fields\TracksMorphManyModelAttributes;
use Turahe\Core\Contracts\Fields\HandlesChangedMorphManyAttributes;

class ResourcefulHandlerWithFields extends ResourcefulHandler implements ResourcefulRequestHandler
{
    /**
     * Handle the resource store action.
     */
    public function store(): Model
    {
        [$attributes, $callbacks] = $this->getAttributes();

        $service = $this->resource()->service();

        if ($service instanceof CreateService) {
            $model = $service->create($attributes);
        } else {
            $model = tap($this->resource()->newModel()->fill($attributes))->save();
        }

        $model = $this->handleAssociatedResources($model);

        $model::withoutTouching(function () use ($model, $attributes) {
            foreach ($this->morphManyFields() as $relation => $values) {
                foreach ($values ?? [] as $attributes) {
                    $model->{$relation}()->create($attributes);
                }
            }
        });

        $callbacks->each->__invoke($model);

        ResourceRecordCreated::dispatch($model, $this->resource());

        return $model;
    }

    /**
     * Handle the resource update action.
     */
    public function update(Model $model): Model
    {
        [$attributes, $callbacks] = $this->getAttributes();

        $service = $this->resource()->service();

        if ($service instanceof UpdateService) {
            $model = $service->update($model, $attributes);
        } else {
            $model->fill($attributes)->save();
        }

        $model = $this->handleAssociatedResources($model);

        $this->syncMorphManyFields($model);

        $callbacks->each->__invoke($model);

        ResourceRecordUpdated::dispatch($model, $this->resource());

        return $model;
    }

    /**
     * Get the morph many fields
     *
     * @return \Turahe\Core\Fields\FieldsCollection
     */
    protected function morphManyFields()
    {
        return $this->request->authorizedFields()
            ->whereInstanceOf(MorphMany::class)
            ->reject(function ($field) {
                return $this->request->missing($field->requestAttribute());
            })
            ->mapWithKeys(function (Field $field) {
                return $field->storageAttributes($this->request, $field->requestAttribute());
            });
    }

    /**
     * Get the attributes for storage.
     */
    protected function getAttributes(): array
    {
        $parsed = $this->parseAttributes();

        $attributes = $parsed->reject(fn ($data) => is_callable($data['attributes']))
            ->mapWithKeys(function ($data, $attribute) {
                return $data['field'] ? $data['attributes'] : [$attribute => $data['value']];
            })->all();

        $callables = $parsed->filter(
            fn ($data) => is_callable($data['attributes'])
        )->map(fn ($data) => $data['attributes']);

        return [$attributes, $callables];
    }

    /**
     * Get the attributes for the request
     *
     * @return \Illuminate\Support\Collection
     */
    protected function parseAttributes()
    {
        return collect($this->request->all())
            ->mapWithKeys(function ($value, $attribute) {
                $field = $this->request->authorizedFields()->findByRequestAttribute($attribute);

                $attributes = $field ? $field->storageAttributes($this->request, $field->requestAttribute()) : null;

                return [
                    $attribute => [
                        'field'      => $field,
                        'value'      => $value,
                        'attributes' => $attributes,
                    ],
                ];
            });
    }

    /**
     * Sync the MorphMany fields
     *
     * @param  \Illuminate\Database\Eloquent\Model  $record
     * @return void
     */
    protected function syncMorphManyFields($record)
    {
        foreach ($this->morphManyFields() as $relation => $values) {
            $beforeUpdateAttributes = [];
            $afterUpdateAttributes = [];

            $tracksChanges = $record->{$relation}()->getModel() instanceof TracksMorphManyModelAttributes;
            $trackAttributes = $tracksChanges ? (array) $record->{$relation}()->getModel()->trackAttributes() : [];

            if (! $record->relationLoaded($relation)) {
                $record->load([$relation, $relation.'.'.Str::before($record->{$relation}()->getMorphType(), '_type')]);
            }

            foreach (($trackAttributes ? $record->{$relation} : []) as $morphMany) {
                $beforeUpdateAttributes[] = $morphMany->only($trackAttributes);
            }

            $this->syncMorphManyField((array) $values, $relation, $record);

            foreach (($trackAttributes ? $record->{$relation} : []) as $morphMany) {
                $afterUpdateAttributes[] = $morphMany->only($trackAttributes);
            }

            if ($record instanceof HandlesChangedMorphManyAttributes && $beforeUpdateAttributes != $afterUpdateAttributes) {
                $record->morphManyAtributesUpdated($relation, $afterUpdateAttributes, $beforeUpdateAttributes);
            }
        }
    }

    /**
     * Sync the morph many field
     *
     * @param  array  $values
     * @param  string  $relation
     * @param  Model  $record
     * @return void
     */
    protected function syncMorphManyField($values, $relation, $record)
    {
        foreach ($values as $attributes) {
            $delete = isset($attributes['_delete']);
            $fillable = Arr::except($attributes, ['_delete', '_track_by']);

            if ($delete) {
                $record->{$relation}->find($attributes['id'])->delete();
                $record->setRelation($relation, $record->{$relation}->except($attributes['id']));
            } elseif (isset($attributes['id'])) {
                tap($record->{$relation}->find($attributes['id'])->fill($fillable))->save();
            } else {
                $trackBy = $attributes['_track_by'] ?? $fillable;
                $model = $record->{$relation}->first(function ($item) use ($trackBy) {
                    foreach ($trackBy as $key => $value) {
                        if ($item[$key] === $value) {
                            return true;
                        }
                    }
                });

                if ($model) {
                    $model->fill($fillable)->save();
                } else {
                    $model = $record->{$relation}()->create($fillable);
                    $record->setRelation($relation, $record->{$relation}->push($model));
                }
            }
        }
    }
}
