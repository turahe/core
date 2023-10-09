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

namespace Modules\Core\Actions;

use JsonSerializable;
use Illuminate\Support\Str;
use Modules\Core\Authorizeable;
use Illuminate\Support\Collection;
use Modules\Core\Facades\Innoclapps;
use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Resource\Http\ResourceRequest;

abstract class Action implements JsonSerializable
{
    use Authorizeable;

    /**
     * Indicates that the action will be hidden on the index view.
     */
    public bool $hideOnIndex = false;

    /**
     * Indicates that the action will be hidden on the view/update view.
     */
    public bool $hideOnUpdate = false;

    /**
     * Indicates that the action does not have confirmation dialog.
     */
    public bool $withoutConfirmation = false;

    /**
     * Action name
     */
    protected ?string $name = null;

    /**
     * Determine if the action is executable for the given request.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    abstract public function authorizedToRun(ActionRequest $request, $model): bool;

    /**
     * Handle method that all actions must implement.
     *
     * @return mixed
     */
    public function handle(Collection $models, ActionFields $fields)
    {
        return [];
    }

    /**
     * Get the action fields.
     */
    public function fields(ResourceRequest $request): array
    {
        return [];
    }

    /**
     * Resolve action fields.
     *
     * @return \Illuminate\Support\Collection
     */
    public function resolveFields(ResourceRequest $request)
    {
        return collect($this->fields($request))->filter->authorizedToSee()->values();
    }

    /**
     * Run action based on the request data.
     *
     * @return mixed
     */
    public function run(ActionRequest $request, Builder $query)
    {
        $ids = $request->input('ids');
        $fields = $request->resolveFields();

        /**
         * Find all models and exclude any models that are not authorized to be handled in this action
         */
        $models = $this->filterForExecution(
            $this->findModelsForExecution($ids, $query),
            $request
        );

        /**
         * All models excluded? In this case, the user is probably not authorized to run the action
         */
        if ($models->count() === 0) {
            return static::error(__('users::user.not_authorized'));
        } elseif ($models->count() > (int) config('core.actions.disable_notifications_when_records_are_more_than')) {
            Innoclapps::disableNotifications();
        }

        $response = $this->handle($models, $fields);

        if (Innoclapps::notificationsDisabled()) {
            Innoclapps::enableNotifications();
        }

        if (! is_null($response)) {
            return $response;
        }

        return static::success(__('core::actions.run_successfully'));
    }

    /**
     * Toasted success alert.
     */
    public static function success(string $message): array
    {
        return ['success' => $message];
    }

    /**
     * Toasted info alert.
     */
    public static function info(string $message): array
    {
        return ['info' => $message];
    }

    /**
     * Toasted success alert.
     */
    public static function error(string $message): array
    {
        return ['error' => $message];
    }

    /**
     * Throw confetti on success.
     */
    public static function confetti(): array
    {
        return ['confetti' => true];
    }

    /**
     * Return an open new tab response from the action.
     */
    public static function openInNewTab(string $url): array
    {
        return ['openInNewTab' => $url];
    }

    /**
     * Filter models for exeuction.
     *
     * @param  \Illuminate\Support\Collection  $models
     * @return \Illuminate\Support\Collection
     */
    public function filterForExecution($models, ActionRequest $request)
    {
        return $models->filter(fn ($model) => $this->authorizedToRun($request, $model));
    }

    /**
     * Provide action human readable name.
     */
    public function name(): string
    {
        return $this->name ?: Str::title(Str::snake(get_called_class(), ' '));
    }

    /**
     * Set the action name.
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the URI key for the card.
     */
    public function uriKey(): string
    {
        return Str::kebab(class_basename(get_called_class()));
    }

    /**
     * Message shown when performing the action.
     */
    public function message(): string
    {
        return __('core::actions.confirmation_message');
    }

    /**
     * Set the action to not have confirmation dialog.
     */
    public function withoutConfirmation(): static
    {
        $this->withoutConfirmation = true;

        return $this;
    }

    /**
     * Set the action to be available only on index view.
     */
    public function onlyOnIndex(): static
    {
        $this->hideOnUpdate = true;
        $this->hideOnIndex = false;

        return $this;
    }

    /**
     * Set the action to be available only on update view.
     */
    public function onlyOnUpdate(): static
    {
        $this->hideOnUpdate = false;
        $this->hideOnIndex = true;

        return $this;
    }

    /**
     * Query the models for execution
     *
     * @param  array  $ids
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function findModelsForExecution($ids, Builder $query)
    {
        return $query->findMany($ids);
    }

    /**
     * jsonSerialize.
     */
    public function jsonSerialize(): array
    {
        return [
            'name'                => $this->name(),
            'message'             => $this->message(),
            'destroyable'         => $this instanceof DestroyableAction,
            'withoutConfirmation' => $this->withoutConfirmation,
            'fields'              => $this->resolveFields(app(ResourceRequest::class)),
            'hideOnIndex'         => $this->hideOnIndex,
            'hideOnUpdate'        => $this->hideOnUpdate,
            'uriKey'              => $this->uriKey(),
        ];
    }
}
