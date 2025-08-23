<?php

declare(strict_types=1);

namespace Turahe\Core\Tests\Unit;

use Turahe\Core\Tests\TestCase;

class HelpersTest extends TestCase
{
    public function test_format_currency_with_valid_number(): void
    {
        $result = format_currency(1000000);

        $this->assertIsString($result);
        $this->assertStringContainsString('Rp', $result);
        $this->assertStringContainsString('1.000.000', $result);
    }

    public function test_format_currency_with_null_value(): void
    {
        $result = format_currency(null);

        $this->assertIsString($result);
        $this->assertStringContainsString('Rp', $result);
        $this->assertStringContainsString('0', $result);
    }

    public function test_format_currency_with_float_value(): void
    {
        $result = format_currency(1234.56);

        $this->assertIsString($result);
        $this->assertStringContainsString('Rp', $result);
        $this->assertStringContainsString('1.235', $result); // Rounded to 0 decimal places
    }

    public function test_format_currency_with_custom_currency_code(): void
    {
        $result = format_currency(1000, 'USD');

        $this->assertIsString($result);
        $this->assertStringContainsString('$', $result);
        $this->assertStringContainsString('1.000', $result);
    }

    public function test_name_alias_with_single_name(): void
    {
        $result = name_alias('John');

        $this->assertEquals('J', $result);
    }

    public function test_name_alias_with_full_name(): void
    {
        $result = name_alias('John Doe');

        $this->assertEquals('JD', $result);
    }

    public function test_name_alias_with_multiple_names(): void
    {
        $result = name_alias('John Michael Doe');

        $this->assertEquals('JMD', $result);
    }

    public function test_name_alias_with_extra_spaces(): void
    {
        $result = name_alias('  John   Doe  ');

        $this->assertEquals('JD', $result);
    }

    public function test_name_alias_with_special_characters(): void
    {
        $result = name_alias('John-Doe');

        $this->assertEquals('J', $result);
    }

    public function test_clean_with_simple_string(): void
    {
        $result = clean('Hello World');

        $this->assertEquals('Hello-World', $result);
    }

    public function test_clean_with_special_characters(): void
    {
        $result = clean('Hello@World#123!');

        $this->assertEquals('HelloWorld123', $result);
    }

    public function test_clean_with_numbers(): void
    {
        $result = clean('Test123');

        $this->assertEquals('Test123', $result);
    }

    public function test_clean_with_hyphens(): void
    {
        $result = clean('test-string');

        $this->assertEquals('test-string', $result);
    }

    public function test_clean_with_multiple_spaces(): void
    {
        $result = clean('Hello   World');

        $this->assertEquals('Hello---World', $result);
    }

    public function test_acronym_with_simple_string(): void
    {
        $result = acronym('Hello World');

        $this->assertEquals('H', $result);
    }

    public function test_acronym_with_underscores(): void
    {
        $result = acronym('hello_world');

        $this->assertEquals('H', $result);
    }

    public function test_acronym_with_hyphens(): void
    {
        $result = acronym('hello-world');

        $this->assertEquals('HW', $result);
    }

    public function test_acronym_with_mixed_separators(): void
    {
        $result = acronym('hello_world-test');

        $this->assertEquals('HT', $result);
    }

    public function test_acronym_with_special_characters(): void
    {
        $result = acronym('Hello@World#123!');

        $this->assertEquals('H', $result);
    }

    public function test_acronym_with_numbers(): void
    {
        $result = acronym('Test123');

        $this->assertEquals('T', $result);
    }

    public function test_acronym_with_single_word(): void
    {
        $result = acronym('Hello');

        $this->assertEquals('H', $result);
    }

    public function test_img_proxy_function_exists(): void
    {
        $this->assertTrue(function_exists('imgProxy'));
    }

    public function test_img_proxy_preset_function_exists(): void
    {
        $this->assertTrue(function_exists('imgProxyPreset'));
    }

    public function test_calculate_percentage_with_integers(): void
    {
        $result = calculate_percentage(100, 25);

        $this->assertEquals(25, $result);
    }

    public function test_calculate_percentage_with_floats(): void
    {
        $result = calculate_percentage(200.5, 10);

        $this->assertEquals(20.05, $result);
    }

    public function test_calculate_percentage_with_zero_percentage(): void
    {
        $result = calculate_percentage(100, 0);

        $this->assertEquals(0, $result);
    }

    public function test_calculate_percentage_with_100_percentage(): void
    {
        $result = calculate_percentage(100, 100);

        $this->assertEquals(100, $result);
    }

    public function test_calculate_percentage_with_large_numbers(): void
    {
        $result = calculate_percentage(1000000, 15);

        $this->assertEquals(150000, $result);
    }

    public function test_parse_phone_with_valid_indonesian_number(): void
    {
        $result = parse_phone('08123456789');

        $this->assertIsString($result);
        $this->assertStringStartsWith('+62', $result);
    }

    public function test_parse_phone_with_international_format(): void
    {
        $result = parse_phone('+628123456789');

        $this->assertIsString($result);
        $this->assertStringStartsWith('+62', $result);
    }

    public function test_parse_phone_with_different_region(): void
    {
        $result = parse_phone('1234567890', 'US');

        $this->assertIsString($result);
        $this->assertStringStartsWith('+1', $result);
    }

    public function test_parse_phone_with_invalid_number(): void
    {
        $this->expectException(\Exception::class);

        parse_phone('invalid-phone-number');
    }

    public function test_parse_phone_with_empty_string(): void
    {
        $this->expectException(\Exception::class);

        parse_phone('');
    }
}
