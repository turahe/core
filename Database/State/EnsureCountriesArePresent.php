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

namespace Modules\Core\Database\State;

use Illuminate\Support\Facades\DB;

class EnsureCountriesArePresent
{
    public function __invoke(): void
    {
        if ($this->present()) {
            return;
        }

        $countries = \Countries::getList();

        foreach ($countries as $countryId => $country) {
            DB::table(config('countries.table_name'))->insert([
                'id'           => $countryId,
                'capital'      => $country['capital'] ?? null,
                'citizenship'  => $country['citizenship'] ?? null,
                'country_code' => $country['country-code'],
                //'currency' => $country['currency'] ?? null,
                'currency_code' => $country['currency_code'] ?? null,
                //'currency_sub_unit' => $country['currency_sub_unit'] ?? null,
                //'currency_decimals' => $country['currency_decimals'] ?? null,
                'full_name'       => $country['full_name'] ?? null,
                'iso_3166_2'      => $country['iso_3166_2'],
                'iso_3166_3'      => $country['iso_3166_3'],
                'name'            => $country['name'],
                'region_code'     => $country['region-code'],
                'sub_region_code' => $country['sub-region-code'],
                'eea'             => (bool) $country['eea'],
                'calling_code'    => $country['calling_code'],
                //'currency_symbol' => $country['currency_symbol'] ?? null,
                //'flag' => ss$country['flag'] ?? null,
            ]);
        }
    }

    private function present(): bool
    {
        return DB::table(config('countries.table_name'))->count() > 0;
    }
}
