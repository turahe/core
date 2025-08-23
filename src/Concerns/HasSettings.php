<?php

declare(strict_types=1);

namespace Turahe\Core\Concerns;

use ArrayAccess;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Turahe\Core\Models\Setting;

/**
 * HasSettings Trait
 * 
 * Provides flexible settings management for Eloquent models with caching support.
 * This trait allows models to store and retrieve key-value settings with validation,
 * default values, and automatic cache management.
 * 
 * Features:
 * - Dynamic settings storage using polymorphic relationships
 * - Configurable validation rules for settings
 * - Default values support
 * - Automatic cache invalidation
 * - Array-like access to settings
 * 
 * @package Turahe\Core\Concerns
 */
trait HasSettings
{
    /**
     * Boot the HasSettings trait
     * 
     * Sets up model event listeners for automatic cache management and cleanup.
     * - Clears settings cache when model is saved or deleted
     * - Automatically deletes related settings when model is deleted
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

    /**
     * Get the cache repository instance
     * 
     * @return CacheRepository
     */
    private function getCache(): CacheRepository
    {
        return app(CacheRepository::class);
    }

    /**
     * Get validation rules for settings
     * 
     * Returns the validation rules defined in the model's $settingsRules property.
     * If no rules are defined, returns an empty array.
     * 
     * @return array Validation rules for settings
     */
    public function getRules(): array
    {
        if (property_exists($this, 'settingsRules')) {
            return $this->settingsRules;
        }

        return [];
    }

    /**
     * Get default settings values
     * 
     * Returns the default settings defined in the model's $defaultSettings property.
     * If no defaults are defined, returns an empty array.
     * 
     * @return array Default settings values
     */
    public function getDefaultSettings(): array
    {
        if (property_exists($this, 'defaultSettings')
            && is_array($this->defaultSettings)) {
            return Arr::wrap($this->defaultSettings);
        }

        return [];
    }

    /**
     * Get the morphMany relationship for settings
     * 
     * @return MorphMany Relationship to the Setting model
     */
    public function settings(): MorphMany
    {
        return $this->morphMany(Setting::class, 'model');
    }

    /**
     * Get the cache key for this model's settings
     * 
     * Generates a unique cache key based on the model's morph class, primary key,
     * and last updated timestamp to ensure cache invalidation when data changes.
     * 
     * @return string Unique cache key for the model's settings
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
     * 
     * Generates a unique cache key for a specific setting key, including the model's
     * morph class, primary key, setting key, and last updated timestamp.
     * 
     * @param string $key The setting key to generate a cache key for
     * @return string Unique cache key for the specific setting
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
     * 
     * Removes all cached settings for this model instance. This method is called
     * automatically when the model is saved or deleted to ensure cache consistency.
     * 
     * Note: Only clears cache if caching is enabled in the core configuration.
     */
    public function clearSettingsCache(): void
    {
        if (!config('core.cache.enabled', true)) {
            return;
        }

        $cache = $this->getCache();
        $cacheKey = $this->getSettingsCacheKey();
        $cache->forget($cacheKey);
        
        // Clear individual setting caches by pattern
        $pattern = sprintf(
            'setting:%s:%s:*',
            $this->getMorphClass(),
            $this->getKey()
        );
        
        // Note: This is a simplified approach. In production, you might want to use
        // Redis SCAN command or maintain a list of cached keys for more efficient clearing
        $cache->forget($pattern);
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
                $this->getCache()->forget($this->getSettingCacheKey($key));
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
        // Cache config values to avoid repeated calls
        static $cacheEnabled = null;
        static $cacheTtl = null;
        
        if ($cacheEnabled === null) {
            $cacheEnabled = config('core.cache.enabled', true);
            $cacheTtl = config('core.cache.settings_ttl', 3600);
        }

        if (!$cacheEnabled) {
            return $this->transformSettingsToArray($this->settings);
        }

        $cacheKey = $this->getSettingsCacheKey();
        $cache = $this->getCache();
        
        return $cache->remember($cacheKey, $cacheTtl, function () {
            return $this->transformSettingsToArray($this->settings);
        });
    }

    /**
     * Transform settings collection to array with JSON decoding
     * 
     * @param \Illuminate\Database\Eloquent\Collection $settings
     * @return array
     */
    protected function transformSettingsToArray($settings): array
    {
        return $settings->keyBy('key')
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
     * @param string|null $key The setting key to retrieve
     * @return mixed The setting value or all settings if no key provided
     */
    public function getSetting(?string $key = null)
    {
        if ($key) {
            // Cache config values to avoid repeated calls
            static $cacheEnabled = null;
            static $cacheTtl = null;
            
            if ($cacheEnabled === null) {
                $cacheEnabled = config('core.cache.enabled', true);
                $cacheTtl = config('core.cache.settings_ttl', 3600);
            }

            if (!$cacheEnabled) {
                return $this->settings->where('key', $key)->first();
            }

            $cacheKey = $this->getSettingCacheKey($key);
            $cache = $this->getCache();
            
            return $cache->remember($cacheKey, $cacheTtl, function () use ($key) {
                return $this->settings->where('key', $key)->first();
            });
        }
        
        return $this->allSetting();
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
        // Optimize by avoiding double database/cache calls
        $setting = $this->getSetting($key);
        return $setting ? $setting->value : null;
    }

    public function updateSetting(string $key, string|array $value): self
    {
        // Cache config value to avoid repeated calls
        static $cacheEnabled = null;
        if ($cacheEnabled === null) {
            $cacheEnabled = config('core.cache.enabled', true);
        }

        if (is_array($value)) {
            $value = json_encode($value);
        }
        
        $this->settings()->updateOrCreate([
            'key' => $key,
            'value' => $value,
        ]);

        // Clear cache for this specific setting
        if ($cacheEnabled) {
            $this->getCache()->forget($this->getSettingCacheKey($key));
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
            $this->getCache()->forget($this->getSettingCacheKey($key));
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
