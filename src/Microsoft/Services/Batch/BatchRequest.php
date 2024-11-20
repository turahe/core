<?php

declare(strict_types=1);
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

namespace Turahe\Core\Microsoft\Services\Batch;

use Illuminate\Contracts\Support\Arrayable;
use Microsoft\Graph\Model\Entity;

class BatchRequest implements Arrayable
{
    /**
     * @var string|int
     */
    protected $id;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var array|\Microsoft\Graph\Model\Entity
     */
    protected $body = [];

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var array
     */
    protected $dependsOn = [];

    /**
     * Initialize batch request
     */
    public function __construct(string $url, Entity|array $body = [])
    {
        $this->setUrl($url);
        $this->setBody($body);
    }

    /**
     * Set request id
     */
    public function setId(int|string $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get request id
     */
    public function getId(): int|string
    {
        return $this->id;
    }

    /**
     * Set request url
     */
    public function setUrl(string $url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get request url
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Set request method
     *
     * @return BatchRequest
     */
    public function setMethod(string $method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get request method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set request body
     *
     * @return BatchRequest
     */
    public function setBody(Entity|array $body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get request body
     */
    public function getBody(): Entity|array
    {
        if ($this->body instanceof Entity) {
            $this->body->setOdataType('microsoft.graph.'.class_basename($this->body));

            return $this->body->jsonSerialize();
        }

        return $this->body;
    }

    /**
     * Set request headers
     *
     * @return BatchRequest
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Add request header
     *
     * @return BatchRequest
     */
    public function addHeader(string $name, string $value)
    {
        $this->headers = array_merge($this->headers, [$name => $value]);

        return $this;
    }

    /**
     * Set request header
     */
    public function setHeader(string $key, string $value)
    {
        $this->headers = array_merge($this->headers, [$key => $value]);

        return $this;
    }

    /**
     * Get the request headers
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Checks whether there is headers for the request
     */
    public function hasHeaders(): bool
    {
        return count($this->getHeaders()) > 0;
    }

    /**
     * Checks whether there is body for the request
     */
    public function hasBody(): bool
    {
        return count($this->getBody()) > 0;
    }

    /**
     * Get the value of dependsOn
     *
     * @return array
     */
    public function getDependsOn()
    {
        return $this->dependsOn;
    }

    /**
     * Set the value of dependsOn
     *
     *
     * @return static
     */
    public function setDependsOn(array $dependsOn)
    {
        $this->dependsOn = $dependsOn;

        return $this;
    }

    /**
     * Mark the request as JSON
     *
     * @return static
     */
    public function asJson()
    {
        return $this->setHeaders(['Content-Type' => 'Turahe\MailClientlication/json']);
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        $payload = [
            'id' => $this->getId(),
            'method' => $this->getMethod(),
            'url' => $this->getUrl(),
        ];

        if ($this->hasBody()) {
            $payload['body'] = $this->getBody();
        }

        if ($this->hasHeaders()) {
            $payload['headers'] = $this->getHeaders();
        }

        if (count($this->getDependsOn()) > 0) {
            $payload['dependsOn'] = $this->getDependsOn();
        }

        return $payload;
    }
}
