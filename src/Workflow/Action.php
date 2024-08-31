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

namespace Turahe\Core\Workflow;

use Closure;
use JsonSerializable;
use Turahe\Core\Facades\Innoclapps;
use Turahe\Core\Contracts\Workflow\ModelTrigger;

abstract class Action implements JsonSerializable
{
    /**
     * The data intended for the action
     *
     * @var object|null
     */
    protected $data;

    /**
     * The trigger the action is composed from
     *
     * @var \Turahe\Core\Workflow\Trigger|null
     */
    protected $trigger;

    /**
     * Provide the action name
     */
    abstract public static function name(): string;

    /**
     * Run the trigger
     *
     * @return mixed
     */
    abstract public function run();

    /**
     * Action available fields
     */
    public function fields(): array
    {
        return [];
    }

    /**
     * Check whether the action can be executed
     */
    public static function allowedForExecution(): bool
    {
        return Innoclapps::importStatus() === false;
    }

    /**
     * Check whether the action is triggered via model
     */
    public function viaModelTrigger(): bool
    {
        return $this->trigger() instanceof ModelTrigger;
    }

    /**
     * Get the action trigger
     *
     * @return \Turahe\Core\Workflow\Trigger|\Turahe\Core\Contracts\Workflow\ModelTrigger|\Turahe\Core\Contracts\Workflow\EventTrigger|null
     */
    public function trigger(): ?Trigger
    {
        return $this->trigger;
    }

    /**
     * Set the action trigger
     */
    public function setTrigger(Trigger $trigger): static
    {
        $this->trigger = $trigger;

        return $this;
    }

    /**
     * Register execution callback
     */
    public function executing(Closure $callback): static
    {
        $trigger = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['class'];

        $trigger::registerActionExecutingEvent(get_called_class(), $callback);

        return $this;
    }

    /**
     * Get the action identifier
     */
    public static function identifier(): string
    {
        return get_called_class();
    }

    /**
     * Set the trigger data
     *
     * @param  array  $data
     * @return $self
     */
    public function setData($data): static
    {
        $this->data = (object) $data;

        return $this;
    }

    /**
     * Determine if an attribute exists on data.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->data->{$key});
    }

    /**
     * Dynamically get properties from the data.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->data->{$key};
    }

    /**
     * Dynamically set properties to the data.
     *
     * @param  string  $name
     * @param  mixed  $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->data->{$name} = $value;
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return [
            'identifier' => static::identifier(),
            'name'       => static::name(),
            'fields'     => $this->fields(),
        ];
    }
}
