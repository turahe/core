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

namespace Turahe\Core\Workflow\Actions;

use Closure;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Turahe\Core\Fields\Text;
use Turahe\Core\Resource\Http\ResourceRequest;
use Turahe\Core\Workflow\Action;

class WebhookAction extends Action implements ShouldQueue
{
    /**
     * Action name
     */
    public static function name(): string
    {
        return __('core::workflow.actions.webhook');
    }

    /**
     * Run the trigger
     *
     * @return array
     */
    public function run()
    {
        Http::withoutVerifying()
            ->withHeaders($this->getHeaders())
            ->post('https://'.$this->url, $payload = $this->getPayload())
            ->onError(function ($error) {
                Log::debug('Webhook action failed with status: '.$error->status().', reason: '.$error->reason());
            });

        return $payload;
    }

    /**
     * Get the webhook request headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return array_merge(with([], function ($headers) {
            if ($this->header_name) {
                $headers[$this->header_name] = $this->header_value;
            }

            return $headers;
        }), [
            'User-Agent' => 'Concord CRM',
        ]);
    }

    /**
     * Action available fields
     */
    public function fields(): array
    {
        return [
            Text::make('url')->inputType('url')
                ->inputGroupPrepend('https://')
                ->withMeta(['attributes' => ['class' => '!pl-16']])
                ->help(__('core::workflow.actions.webhook_url_info'))
                ->helpDisplay('text')
                ->rules(['required', function (string $attribute, mixed $value, Closure $fail) {
                    if (Str::startsWith($value, ['https://', 'http://'])) {
                        $fail('core::workflow.validation.invalid_webhook_url')->translate();
                    }
                }]),

            Text::make('header_name')->withMeta([
                'attributes' => [
                    'placeholder' => __('core::workflow.fields.with_header_name'),
                ],
            ]),

            Text::make('header_value')->withMeta([
                'attributes' => [
                    'placeholder' => __('core::workflow.fields.with_header_value'),
                ],
            ]),
        ];
    }

    /**
     * Set the trigger data
     *
     * @param  array  $data
     */
    public function setData($data): static
    {
        parent::setData($data);

        // We will set the payload while setting up the data for the action
        // this helps properly serialize the action when in the queue and keep
        // as the $data property is serialized for the action, after the action
        // is unserialized, we will still have the data from the previous request
        if ($this->viaModelTrigger()) {
            $this->setPayload(json_decode(
                json_encode(
                    $this->resource->createJsonResource(
                        $this->resource->displayQuery()->find($this->model->getKey()),
                        true,
                        // Not needed but for consistency, to use the ResourceRequest class
                        app(ResourceRequest::class)->setResource($this->resource->name())
                    )
                ),
                true
            ));
        }

        return $this;
    }

    /**
     * Get the request payload data
     *
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Set the request payload data
     *
     * @param  array  $data
     */
    public function setPayload($data)
    {
        $this->payload = (array) $data;

        return $this;
    }
}
