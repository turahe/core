<?php

declare(strict_types=1);

if (! function_exists('format_currency')) {
    function format_currency(int|float|null $number, string $code = 'IDR'): string
    {
        if (is_null($number)) {
            $number = 0;
        }

        if (\Turahe\Master\Models\Currency::where('iso_code', $code)->exists()) {
            $fmt = new NumberFormatter('id_ID', NumberFormatter::CURRENCY);
            $fmt->setTextAttribute(NumberFormatter::CURRENCY_CODE, $code);
            $fmt->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);

            return $fmt->formatCurrency($number, $code);
        }

        throw new InvalidArgumentException('format currency only accept iso code only. Input was:'.$code);
        //        return config('app.currency'). ' '.number_format($number, 2, ',', '.');
    }
}
if (! function_exists('name_alias')) {
    function name_alias($name): string
    {
        $alias = trim(collect(explode(' ', $name))->map(fn ($segment) => mb_substr($segment, 0, 1))->join(''));

        return clean($alias);
    }
}

if (! function_exists('clean')) {
    /**
     * @return array|string|string[]|null
     */
    function clean($string)
    {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }
}

if (! function_exists('acronym')) {
    function acronym(string $string): string
    {
        $letters = [];
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
        $string = str_replace(['_', '-', ' '], ' ', $string);
        $words = explode(' ', $string);

        foreach ($words as $word) {
            $word = (mb_substr($word, 0, 1));
            $letters[] = $word;
        }

        return mb_strtoupper(implode($letters));
    }

}

if (! function_exists('imgProxy')) {

    /**
     * @param  null  $extension
     */
    function imgProxy(string $path, int $width, int $height, $extension = null): string
    {
        app()->instance(\app\Services\Image\Image::class, (new \app\Services\Image\Image)->make($path, $width, $height, $extension));

        return config('img-proxy.base_url').
            app(\Turahe\Core\Contracts\ImageSignatureInterface::class)->take();
    }
}

if (! function_exists('imgProxyPreset')) {

    function imgProxyPreset(string $path, string $preset, $extension = null): string
    {
        app()->instance(\Turahe\Core\Services\Image\Image::class, (new \Turahe\Core\Services\Image\Image)->makePreset($path, $preset, $extension));

        return config('img-proxy.base_url').
            app(\Turahe\Core\Contracts\ImageSignatureInterface::class)->take();
    }
}

if (! function_exists('calculate_percentage')) {
    function calculate_percentage(int|float $value, int|float $percentage): float|int
    {
        return ($value * $percentage) / 100;
    }
}

if (! function_exists('parse_phone')) {
    function parse_phone($number, string $region = 'ID'): bool|string
    {
        $phoneUtil = libphonenumber\PhoneNumberUtil::getInstance();

        try {
            $phone = $phoneUtil->parse($number, $region);

            return $phoneUtil->format($phone, libphonenumber\PhoneNumberFormat::E164);
        } catch (libphonenumber\NumberParseException $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
