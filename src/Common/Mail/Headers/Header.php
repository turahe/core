<?php

namespace Turahe\Core\Common\Mail\Headers;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;

class Header implements Arrayable
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string|array|Carbon
     */
    protected $value;

    /**
     * Initialize header
     */
    public function __construct(string $name, array|string|null $value = null)
    {
        $this->name = strtolower(trim($name));
        $this->value = is_string($value) ? trim($value) : $value;
    }

    /**
     * Get the header name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the header value
     *
     * @return Carbon|array|string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->getName(),
            'value' => $this->getValue(),
        ];
    }
}
