<?php

namespace Turahe\Core\Common\Mail\Headers;

class AddressHeader extends Header
{
    /**
     * @var array
     */
    protected $addresses;

    /**
     * Initialize header
     *
     * @param  string  $name  The header name
     */
    public function __construct(string $name, array|string $value, ?string $personName = null)
    {
        parent::__construct($name, null);

        $this->parseValue($value, $personName);
    }

    /**
     * Get all addresses
     */
    public function getAll(): array
    {
        return $this->addresses;
    }

    /**
     * Get header email address
     */
    public function getAddress(): string
    {
        return $this->addresses[0]['address'];
    }

    /**
     * Get the address person name
     */
    public function getPersonName(): ?string
    {
        return $this->addresses[0]['name'];
    }

    /**
     * Parse the header value
     *
     * @return void
     */
    protected function parseValue(array|string $value, ?string $name)
    {
        if (! is_array($value)) {
            if (str_contains($value, ',')) {
                $value = explode(',', $value);
            } else {
                // Not parsed header passed
                if (str_contains($value, '<')) {
                    $value = [$value];
                } else {
                    $value = [$value => $name];
                }
            }
        }

        foreach ($value as $email => $name) {
            if (is_int($email)) {
                [$email, $name] = $this->parseHeader($name);
            }

            $this->addresses[] = [
                'address' => trim($email),
                'name' => is_null($name) ? null : trim($name),
            ];
        }
    }

    /**
     * Parse address header
     */
    protected function parseHeader(string $header): array
    {
        $name = preg_replace('/(.*)<(.*)>/', '\\1', $header);
        $name = trim(str_replace('"', '', $name));

        $email = trim(preg_replace('/(.*)<(.*)>/', '\\2', $header));

        return [$email, $name];
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getAll();
    }
}
