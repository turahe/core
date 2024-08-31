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

namespace Turahe\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Turahe\Core\Database\State\EnsureCountriesArePresent;

class CountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        call_user_func(new EnsureCountriesArePresent);
    }
}
