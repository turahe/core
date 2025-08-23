<?php

declare(strict_types=1);

/**
 * Global helper functions for the Turahe Core package
 *
 * These utility functions provide common functionality for formatting,
 * string manipulation, image processing, and data calculations.
 */
if (! function_exists('format_currency')) {
    /**
     * Format a number as currency using the specified currency code
     *
     * @param  int|float|null  $number  The number to format
     * @param  string  $code  The ISO currency code (default: 'IDR')
     * @return string Formatted currency string
     *
     * @example format_currency(1000) // Returns "Rp 1.000"
     * @example format_currency(1500.50, 'USD') // Returns "$1,500.50"
     */
    function format_currency(int|float|null $number, string $code = 'IDR'): string
    {
        // Handle null values by defaulting to 0
        if ($number === null) {
            $number = 0;
        }

        // Use static cache for NumberFormatter instances to avoid recreation
        static $formatters = [];
        $cacheKey = $code;

        if (! isset($formatters[$cacheKey])) {
            $formatters[$cacheKey] = new NumberFormatter('id_ID', NumberFormatter::CURRENCY);
            $formatters[$cacheKey]->setTextAttribute(NumberFormatter::CURRENCY_CODE, $code);
            $formatters[$cacheKey]->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);
        }

        return $formatters[$cacheKey]->formatCurrency($number, $code);
    }
}

if (! function_exists('name_alias')) {
    /**
     * Generate an alias from a person's name by taking the first letter of each word
     *
     * @param  string  $name  The full name to convert to an alias
     * @return string The generated alias (e.g., "John Doe" becomes "JD")
     *
     * @example name_alias("John Doe Smith") // Returns "JDS"
     */
    function name_alias(string $name): string
    {
        // Optimize by avoiding collect() and using native array functions
        $words = explode(' ', trim($name));
        $alias = '';

        foreach ($words as $word) {
            if ($word !== '') {
                $alias .= mb_substr($word, 0, 1);
            }
        }

        return clean($alias);
    }
}

if (! function_exists('clean')) {
    /**
     * Clean a string by removing special characters and replacing spaces with hyphens
     *
     * @param  string  $string  The string to clean
     * @return string The cleaned string
     *
     * @example clean("Hello World!") // Returns "Hello-World"
     * @example clean("User@123") // Returns "User123"
     */
    function clean(string $string): string
    {
        // Combine operations for better performance
        return preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $string));
    }
}

if (! function_exists('acronym')) {
    /**
     * Generate an acronym from a string by taking the first letter of each word
     *
     * @param  string  $string  The input string to convert to acronym
     * @return string The generated acronym in uppercase
     *
     * @example acronym("World Health Organization") // Returns "WHO"
     * @example acronym("user_profile_settings") // Returns "UPS"
     */
    function acronym(string $string): string
    {
        // Optimize by combining regex operations and avoiding array creation
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
        $string = str_replace(['_', '-', ' '], ' ', $string);

        $words = explode(' ', $string);
        $acronym = '';

        foreach ($words as $word) {
            if ($word !== '') {
                $acronym .= mb_substr($word, 0, 1);
            }
        }

        return mb_strtoupper($acronym);
    }
}

if (! function_exists('imgProxy')) {
    /**
     * Generate an image proxy URL with specified dimensions
     *
     * Creates an image proxy URL that can resize and optimize images on-the-fly.
     * The function creates an Image instance and generates a signed URL for security.
     *
     * @param  string  $path  The image path or URL
     * @param  int  $width  The desired width
     * @param  int  $height  The desired height
     * @param  string|null  $extension  Optional file extension override
     * @return string The complete image proxy URL
     *
     * @example imgProxy('/images/photo.jpg', 300, 200) // Returns signed proxy URL
     */
    function imgProxy(string $path, int $width, int $height, $extension = null): string
    {
        // Optimize by avoiding unnecessary object creation and method calls
        $image = new \app\Services\Image\Image;
        $image->make($path, $width, $height, $extension);

        app()->instance(\app\Services\Image\Image::class, $image);

        return config('img-proxy.base_url').
               app(\Turahe\Core\Contracts\ImageSignatureInterface::class)->take();
    }
}

if (! function_exists('imgProxyPreset')) {
    /**
     * Generate an image proxy URL using a predefined preset
     *
     * Similar to imgProxy but uses predefined size presets instead of custom dimensions.
     * This is useful for consistent image sizing across an application.
     *
     * @param  string  $path  The image path or URL
     * @param  string  $preset  The preset name (e.g., 'thumbnail', 'medium', 'large')
     * @param  string|null  $extension  Optional file extension override
     * @return string The complete image proxy URL
     *
     * @example imgProxyPreset('/images/photo.jpg', 'thumbnail') // Returns preset-based proxy URL
     */
    function imgProxyPreset(string $path, string $preset, $extension = null): string
    {
        // Optimize by avoiding unnecessary object creation and method calls
        $image = new \Turahe\Core\Services\Image\Image;
        $image->makePreset($path, $preset, $extension);

        app()->instance(\Turahe\Core\Services\Image\Image::class, $image);

        return config('img-proxy.base_url').
               app(\Turahe\Core\Contracts\ImageSignatureInterface::class)->take();
    }
}

if (! function_exists('calculate_percentage')) {
    /**
     * Calculate a percentage of a given value
     *
     * @param  int|float  $value  The base value
     * @param  int|float  $percentage  The percentage to calculate
     * @return float|int The calculated percentage value
     *
     * @example calculate_percentage(100, 25) // Returns 25.0
     * @example calculate_percentage(200, 15) // Returns 30.0
     */
    function calculate_percentage(int|float $value, int|float $percentage): float|int
    {
        // Use multiplication by 0.01 instead of division by 100 for better performance
        return $value * ($percentage * 0.01);
    }
}

if (! function_exists('parse_phone')) {
    /**
     * Parse and format a phone number using libphonenumber library
     *
     * Validates and formats phone numbers according to international standards.
     * Returns the phone number in E.164 format (e.g., +1234567890).
     *
     * @param  string  $number  The phone number to parse
     * @param  string  $region  The region/country code for parsing (default: 'ID' for Indonesia)
     * @return bool|string The formatted phone number in E.164 format or false on failure
     *
     * @throws \Exception When phone number parsing fails
     *
     * @example parse_phone('08123456789') // Returns '+628123456789'
     * @example parse_phone('+1-555-123-4567', 'US') // Returns '+15551234567'
     */
    function parse_phone($number, string $region = 'ID'): bool|string
    {
        // Cache PhoneNumberUtil instance for better performance
        static $phoneUtil = null;

        if ($phoneUtil === null) {
            $phoneUtil = libphonenumber\PhoneNumberUtil::getInstance();
        }

        try {
            $phone = $phoneUtil->parse($number, $region);

            return $phoneUtil->format($phone, libphonenumber\PhoneNumberFormat::E164);
        } catch (libphonenumber\NumberParseException $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
