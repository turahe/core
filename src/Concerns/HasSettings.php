<?php

declare(strict_types=1);

namespace Turahe\Core\Concerns;

use ArrayAccess;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Turahe\Core\Models\Setting;

trait HasSettings
{
    /**
     * Boot the HasSettings trait
     */
    protected static function bootHasSettings(): void
    {
        static::deleting(function ($model): void {
            $model->settings()->delete();
        });
    }

    /** @var \Illuminate\Database\Eloquent\Model */
    protected $model;

    public function getRules(): array
    {
        if (property_exists($this, 'settingsRules')) {
            return $this->settingsRules;
        }

        return [];
    }

    public function getDefaultSettings(): array
    {
        if (property_exists($this, 'defaultSettings')
            && is_array($this->defaultSettings)) {
            return Arr::wrap($this->defaultSettings);
        }

        return [];
    }

    public function settings(): MorphMany
    {
        return $this->morphMany(Setting::class, 'model');
    }

    /**
     * @throws ValidationException
     */
    public function setSetting(array $settings = [], string $group = 'default'): self
    {
        $this->validate($settings);

        foreach ($settings as $key => $value) {
            $this->settings()->updateOrCreate([
                'key' => $key,
            ], [
                'value' => $value,
                'group' => $group,
            ]);
        }

        return $this;
    }

    /**
     * Get nested merged array with all available keys
     */
    public function allSetting(): array
    {
        return $this->settings->keyBy('key')
            ->transform(fn ($item) => Str::isJson($item->value) ? json_decode($item->value, true) : $item->value)
            ->toArray();
    }

    /**
     * Check if model exist settings
     */
    public function existSetting(): bool
    {
        return count($this->settings) > 0;
    }

    /**
     * Check if model empty settings
     */
    public function emptySetting(): bool
    {
        return count($this->settings) <= 0;
    }

    /**
     * Check if model has settings
     */
    public function hasSetting(string $key): bool
    {
        return $this->settings->contains('key', $key);
    }

    /**
     * Get model settings
     *
     * @return array|ArrayAccess|mixed
     */
    public function getSetting(?string $key = null)
    {
        return $key ? $this->settings->where('key', $key)->first() : $this->all();
    }

    /**
     * Get all multiple setting model as array
     *
     * @param  null  $default
     */
    public function getMultiple(?iterable $paths = null, $default = null): array
    {
        $array = [];
        $allFlattened = $this->allFlattened();
        $settingsArray = [];
        foreach ($allFlattened as $key => $value) {
            Arr::set($settingsArray, $key, $value);
        }
        if (is_null($paths)) {
            return $settingsArray;
        }

        foreach ($paths as $path) {
            Arr::set($array, $path, Arr::get($settingsArray, $path, $default));
        }

        return $array;
    }

    /**
     * @return HasSettings|\Illuminate\Database\Eloquent\Model
     *
     * @throws ValidationException
     */
    public function set(string $path, string|array $value)
    {
        $settings = $this->settings->toArray();
        Arr::set($settings, $path, $value);

        return $this->setSetting($settings);
    }

    /**
     * Get value of settings
     */
    public function getSettingsValue(string $key): ?string
    {
        return $this->getSetting($key) ? $this->getSetting($key)->value : null;
    }

    public function updateSetting(string $key, string|array $value): self
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }
        $this->settings()->updateOrCreate([
            'key' => $key,
            'value' => $value,
        ]);

        return $this;
    }

    public function deleteSetting(?string $key = null): bool
    {
        if (is_null($key)) {
            return (bool) $this->settings()->delete();
        }

        return (bool) $this->settings()->where('key', $key)->delete();

    }

    /**
     * delete all setting
     */
    public function clear(): bool
    {
        return $this->deleteSetting();
    }

    /**
     * @return HasSettings|\Illuminate\Database\Eloquent\Model
     *
     * @throws ValidationException
     */
    public function setMultiple(iterable $values)
    {
        $settings = $this->settings->toArray();
        foreach ($values as $path => $value) {
            Arr::set($settings, $path, $value);
        }

        return $this->setSetting($settings);
    }

    /**
     * Delete multiple settings
     *
     * @return HasSettings
     *
     * @throws ValidationException
     */
    public function deleteMultiple(iterable $keys)
    {
        $settings = $this->allSetting();
        foreach ($keys as $key) {
            Arr::forget($settings, $key);
        }

        $this->setSetting($settings);

        return $this;
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validate(array $settings): array
    {
        return Validator::make(Arr::wrap($settings), Arr::wrap($this->getRules()))->validate();
    }
}
