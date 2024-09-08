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

namespace Turahe\Core\Updater;

use Illuminate\Filesystem\Filesystem;

final class Release
{
    use DownloadsFiles;

    /**
     * @var \Turahe\Core\Updater\ZipArchive
     */
    protected $archive;

    /**
     * Initialize new Relase instance.
     */
    public function __construct(protected string $version, protected Filesystem $filesystem) {}

    /**
     * Get the release version.
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Set the release version.
     */
    public function setVersion(string $version): static
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get the release archive.
     */
    public function archive(): ZipArchive
    {
        return $this->archive ??= new ZipArchive($this->getStoragePath());
    }
}
