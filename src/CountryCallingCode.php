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

namespace Turahe\Core;

use Illuminate\Database\Eloquent\Collection;
use Turahe\Core\Facades\Innoclapps;
use Turahe\Core\Models\Country;
use Turahe\Core\Resource\Import\Import;

class CountryCallingCode
{
    protected static ?Collection $countries = null;

    /**
     * Guess phone prefix
     */
    public static function guess(): ?string
    {
        if (Innoclapps::isImportInProgress() && $countryId = Import::$currentRequest->country_id) {
            $country = static::findCountry((int) $countryId);
        }

        return ($country ?? static::getCompanyCountry())?->calling_code;
    }

    /**
     * Get calling code from the given country
     */
    public static function fromCountry(int $countryId): ?string
    {
        return static::findCountry($countryId)?->calling_code;
    }

    /**
     * Get the company country
     */
    public static function getCompanyCountry(): ?Country
    {
        if ($countryId = settings('company_country_id')) {
            return static::findCountry((int) $countryId);
        }

        return null;
    }

    /**
     * Check whether the given number starts with any calling code
     */
    public static function startsWithAny(string $number): bool
    {
        static::loadCountriesInCache();

        return (bool) static::$countries->first(
            fn ($country) => str_starts_with($number, '+'.$country->calling_code)
        );
    }

    /**
     * Get random calling code
     */
    public static function random(): string
    {
        static::loadCountriesInCache();

        return '+'.static::$countries->random()->calling_code;
    }

    /**
     * Find country by given ID
     */
    protected static function findCountry(int $countryId): ?Country
    {
        static::loadCountriesInCache();

        return static::$countries->find($countryId);
    }

    /**
     * Load the counties in cache
     */
    protected static function loadCountriesInCache(): void
    {
        if (! static::$countries) {
            static::$countries = Country::get(['id', 'calling_code']);
        }
    }
}
