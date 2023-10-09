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

namespace Modules\Core\Tests\Feature\Filters;

use Tests\TestCase;
use Modules\Core\Filters\Date;
use Tests\Fixtures\EventTable;
use Modules\Core\Table\TableSettings;
use Illuminate\Support\Facades\Request;
use Modules\Core\Tests\Concerns\TestsFilters;
use Modules\Core\QueryBuilder\Exceptions\FieldValueMustBeArrayException;

class FiltersTest extends TestCase
{
    use TestsFilters;

    protected static $filter;

    public function test_user_cannot_see_filters_that_is_not_authorized_to_see()
    {
        $user = $this->signIn();
        Request::setUserResolver(fn () => $user);

        $table = new EventTable;
        $settings = new TableSettings($table, $user);

        $this->assertCount(1, $settings->toArray()['rules']);
    }

    public function test_throw_an_exception_when_rule_between_operator_value_is_not_array()
    {
        $this->expectException(FieldValueMustBeArrayException::class);

        static::$filter = Date::class;

        $this->perform('dummy-attribute', 'between', 'string-value');

        $this->perform($criteria);
    }

    public function test_throw_an_exception_when_rule_not_between_operator_value_is_not_array()
    {
        $this->expectException(FieldValueMustBeArrayException::class);

        static::$filter = Date::class;

        $this->perform('dummy-attribute', 'not_between', 'string-value');

        $this->perform($criteria);
    }

    public function test_throw_an_exception_when_rule_in_operator_value_is_not_array()
    {
        $this->expectException(FieldValueMustBeArrayException::class);

        static::$filter = Date::class;

        $this->perform('dummy-attribute', 'in', 'string-value');

        $this->perform($criteria);
    }

    public function test_throw_an_exception_when_rule_not_in_operator_value_is_not_array()
    {
        $this->expectException(FieldValueMustBeArrayException::class);

        static::$filter = Date::class;

        $this->perform('dummy-attribute', 'not_in', 'string-value');

        $this->perform($criteria);
    }
}
