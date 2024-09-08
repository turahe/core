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

namespace Turahe\Core\MailableTemplate;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionMethod;
use Turahe\Core\Facades\Innoclapps;
use Turahe\Core\Models\MailableTemplate as MailableTemplateModel;

class MailableTemplatesManager
{
    /**
     * Custom registered mailables.
     */
    protected array $mailables = [];

    /**
     * Database templates cache.
     */
    protected ?Collection $dbTemplates = null;

    /**
     * Collected mailable templates cache.
     */
    protected ?Collection $collected = null;

    /**
     * Indicates whether templates should be auto discovered.
     */
    protected static bool $autoDiscovery = true;

    /**
     * The path for auto discovery.
     */
    protected string $path;

    /**
     * Initialize new Mailables instance.
     */
    public function __construct(protected Filesystem $filesystem, ?string $path = null, protected $namespace = 'App\\Mail\\')
    {
        $this->path = $path ?: app_path('Mail');
    }

    /**
     * Register new mailable class.
     */
    public function register(string|array $mailable): static
    {
        $this->mailables = array_merge($this->mailables, (array) $mailable);

        $this->flushCache();

        return $this;
    }

    /**
     * Set that the templates won't be auto discovered.
     */
    public static function dontDiscover(): void
    {
        static::autoDiscovery(false);
    }

    /**
     * Set auto discovery.
     */
    public static function autoDiscovery(bool $value): void
    {
        static::$autoDiscovery = $value;
    }

    /**
     * Get all available/registered mailables.
     */
    public function get(): Collection
    {
        return $this->collect()->filter(function ($mailable) {
            $reflection = new ReflectionClass($mailable);

            if ($reflection->isAbstract()) {
                return false;
            }

            return $reflection->isSubclassOf(MailableTemplate::class);
        })->values();
    }

    /**
     * Seed mailable(s)
     *
     * If $mailable is not passed will try to seed all mailables.
     */
    public function seed(string $locale, ?string $mailable = null): void
    {
        $mailables = $mailable ? [$mailable] : $this->get();

        foreach ($mailables as $mailable) {
            $mailable = new ReflectionMethod($mailable, 'seed');

            $mailable->invoke(null, $locale);
        }
    }

    /**
     * Check whether mailables requires seeding.
     */
    public function requiresSeeding(): bool
    {
        $dbTemplates = $this->getMailableTemplates();

        $totalAvailable = $this->get()->count();

        foreach (Innoclapps::locales() as $locale) {
            if ($dbTemplates->where('locale', $locale)->count() < $totalAvailable) {
                return true;
            }
        }

        // Check if the template in local does not exists
        return ! is_null($this->getHangingDatabaseTemplates());
    }

    /**
     * Get the database mailable templates.
     */
    public function getMailableTemplates(): Collection
    {
        return $this->dbTemplates ??= MailableTemplateModel::get();
    }

    /**
     * Get the database templates that are without local template.
     *
     * In this case, the local file template is deleted but the one in database is still hanging there.
     */
    protected function getHangingDatabaseTemplates(): ?Collection
    {
        $local = $this->get();

        $dbMailables = $this->getMailableTemplates()->unique('mailable');

        $removed = array_diff($dbMailables->pluck('mailable')->all(), $local->all());

        if (count($removed) > 0) {
            return $dbMailables->filter(function ($template) use ($removed) {
                return in_array($template->mailable, $removed);
            })->values();
        }

        return null;
    }

    /**
     * Check whether the mailables requires seeding
     * If requires seeding will seed them in database
     */
    public function seedIfRequired(): void
    {
        if (! $this->requiresSeeding()) {
            return;
        }

        if ($fileDeleted = $this->getHangingDatabaseTemplates()) {
            $fileDeleted->each->delete();
        }

        $locales = Innoclapps::locales();

        foreach ($locales as $locale) {
            $this->seed($locale);
        }
    }

    /**
     * Clean the cached mailables.
     */
    public function flushCache(): static
    {
        $this->collected = null;

        return $this;
    }

    /**
     * Flush the registered mailable templates.
     */
    public function flush(): static
    {
        static::flushCache();

        $this->mailables = [];

        return $this;
    }

    /**
     * Collect and get all the available mailables, including custom registered and auto discovered.
     */
    protected function collect(): Collection
    {
        if ($this->collected) {
            return $this->collected;
        }

        $discovered = [];

        if (static::$autoDiscovery === true && is_dir($this->path)) {
            $mailableTemplates = $this->filesystem->files($this->path);

            foreach ($mailableTemplates as $file) {
                $discovered[] = $this->namespace.$file->getFilenameWithoutExtension();
            }
        }

        return $this->collected = collect(array_merge($discovered, $this->mailables));
    }
}
