<?php

declare(strict_types=1);

namespace Turahe\Core\Concerns;

use ArrayAccess;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
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
            $model->clearSettingsCache();
        });

        static::saved(function ($model): void {
            $model->clearSettingsCache();
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
     * Get the cache key for this model's settings
     */
    protected function getSettingsCacheKey(): string
    {
        return sprintf(
            'settings:%s:%s:%s',
            $this->getMorphClass(),
            $this->getKey(),
            $this->updated_at?->timestamp ?? 0
        );
    }

    /**
     * Get the cache key for a specific setting
     */
    protected function getSettingCacheKey(string $key): string
    {
        return sprintf(
            'setting:%s:%s:%s:%s',
            $this->getMorphClass(),
            $this->getKey(),
            $key,
            $this->updated_at?->timestamp ?? 0
        );
    }

    /**
     * Clear all cached settings for this model
     */
    public function clearSettingsCache(): void
    {
        if (!config('core.cache.enabled', true)) {
            return;
        }

        $cacheKey = $this->getSettingsCacheKey();
        Cache::forget($cacheKey);
        
        // Clear individual setting caches by pattern
        $pattern = sprintf(
            'setting:%s:%s:*',
            $this->getMorphClass(),
            $this->getKey()
        );
        
        // Note: This is a simplified approach. In production, you might want to use
        // Redis SCAN command or maintain a list of cached keys for more efficient clearing
        Cache::forget($pattern);
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
            
            // Clear cache for this specific setting
            if (config('core.cache.enabled', true)) {
                Cache::forget($this->getSettingCacheKey($key));
            }
        }

        // Clear all settings cache
        $this->clearSettingsCache();

        return $this;
    }

    /**
     * Get nested merged array with all available keys
     */
    public function allSetting(): array
    {
        if (!config('core.cache.enabled', true)) {
            return $this->settings->keyBy('key')
                ->transform(fn ($item) => Str::isJson($item->value) ? json_decode($item->value, true) : $item->value)
                ->toArray();
        }

        $cacheKey = $this->getSettingsCacheKey();
        
        return Cache::remember($cacheKey, config('core.cache.settings_ttl', 3600), function () {
            return $this->settings->keyBy('key')
                ->transform(fn ($item) => Str::isJson($item->value) ? json_decode($item->value, true) : $item->value)
                ->toArray();
        });
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
        if ($key) {
            if (!config('core.cache.enabled', true)) {
                return $this->settings->where('key', $key)->first();
            }

            $cacheKey = $this->getSettingCacheKey($key);
            
            return Cache::remember($cacheKey, config('core.cache.settings_ttl', 3600), function () use ($key) {
                return $this->settings->where('key', $key)->first();
            });
        }
        
        return $this->all();
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

        // Clear cache for this specific setting
        if (config('core.cache.enabled', true)) {
            Cache::forget($this->getSettingCacheKey($key));
        }
        $this->clearSettingsCache();

        return $this;
    }

    public function deleteSetting(?string $key = null): bool
    {
        if (is_null($key)) {
            $result = (bool) $this->settings()->delete();
            $this->clearSettingsCache();
            return $result;
        }

        $result = (bool) $this->settings()->where('key', $key)->delete();
        
        // Clear cache for this specific setting
        if (config('core.cache.enabled', true)) {
            Cache::forget($this->getSettingCacheKey($key));
        }
        $this->clearSettingsCache();
        
        return $result;
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
