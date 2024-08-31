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

namespace Turahe\Core\Changelog;

use Turahe\Core\Facades\ChangeLogger;

trait LogsPivotEvents
{
    /**
     * Check whether should log the pivot activity based on the pivot changed relation.
     */
    protected static function shouldLogPivotEvents(string $relationName): bool
    {
        return property_exists(static::class, 'logPivotEventsOn') && isset(static::$logPivotEventsOn[$relationName]);
    }

    /**
     * Log to the given associated relations that the related instance is trashed.
     */
    public function logRelatedIsTrashed(array $relations, array $langAttributes): void
    {
        foreach ($relations as $relationname) {
            $query = $this->{$relationname}();

            if ($query->getModel()->usesSoftDeletes()) {
                $query->withTrashed();
            }

            foreach ($query->get() as $relatedModel) {
                ChangeLogger::onModel($relatedModel, [
                    'icon' => 'Trash',
                    'lang' => $langAttributes,
                ])->log();
            }
        }
    }

    /**
     * Log to the given associated relations that the related instance is restored.
     */
    public function logToAssociatedRelationsThatRelatedInstanceIsRestored(array $relations): void
    {
        foreach ($relations as $relationname) {
            $query = $this->{$relationname}();

            if ($query->getModel()->usesSoftDeletes()) {
                $query->withTrashed();
            }

            foreach ($query->get() as $relatedModel) {
                ChangeLogger::onModel($relatedModel, [
                    'icon' => 'Trash',
                    'lang' => [
                        'key'   => 'core::timeline.association_restored',
                        'attrs' => ['associationDisplayName' => $relatedModel->display_name],
                    ],
                ])->log();
            }
        }
    }

    /**
     * Boot the events callbacks.
     */
    public static function bootLogsPivotEvents(): void
    {
        static::pivotAttached(function ($instance, $secondInstance, $relationName, $pivotIdsAttributes) {
            if (! static::shouldLogPivotEvents($relationName)) {
                return;
            }

            PivotAttached::log($instance, $pivotIdsAttributes, $relationName);

            // Log the reverse relation
            $instance->{$relationName}()->findMany($pivotIdsAttributes)
                ->each(function ($pivotModel) use ($instance, $relationName) {
                    PivotAttached::log(
                        $pivotModel,
                        [$instance->id],
                        static::$logPivotEventsOn[$relationName]
                    );
                });
        });

        static::pivotDetached(function ($instance, $secondInstance, $relationName, $pivotIdsAttributes) {
            if (! static::shouldLogPivotEvents($relationName)) {
                return;
            }

            if (! method_exists($instance, 'isForceDeleting') || ! $instance->isForceDeleting()) {
                PivotDetached::log($instance, $pivotIdsAttributes, $relationName);
            }

            $pivotModelQuery = $instance->{$relationName}()->getModel()->query();

            if ($pivotModelQuery->getModel()->usesSoftDeletes()) {
                $pivotModelQuery = $pivotModelQuery->withTrashed();
            }

            $pivotModels = $pivotModelQuery->findMany($pivotIdsAttributes);

            if ($instance->isForceDeleting()) {
                $pivotModels->each(function ($relatedModel) use ($instance) {
                    return ChangeLogger::onModel($relatedModel, [
                        'icon' => 'Trash',
                        'lang' => [
                            'key'   => 'core::timeline.association_permanently_deleted',
                            'attrs' => ['associationDisplayName' => $instance->display_name],
                        ],
                    ])->log();
                });

                return;
            }

            // Log the reverse relation
            $pivotModels->each(function ($pivotModel) use ($instance, $relationName) {
                PivotDetached::log(
                    $pivotModel,
                    [$instance->id],
                    static::$logPivotEventsOn[$relationName]
                );
            });
        });
    }
}
