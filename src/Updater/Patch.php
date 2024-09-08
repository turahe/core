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

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use JsonSerializable;
use Turahe\Core\Models\Patch as PatchModel;

final class Patch implements Arrayable, JsonSerializable
{
    use DownloadsFiles;

    /**
     * @var \Turahe\Core\Updater\ZipArchive
     */
    protected $archive;

    /**
     * Initialize new Relase instance.
     */
    public function __construct(protected object $patch) {}

    /**
     * Check whether the patch is applied.
     */
    public function isApplied(): bool
    {
        return ! is_null(PatchModel::where('token', $this->token())->first());
    }

    /**
     * Mark patch as applied.
     */
    public function markAsApplied(): bool
    {
        (new PatchModel([
            'token' => $this->token(),
            'version' => $this->version(),
        ]))->save();

        return true;
    }

    /**
     * Get the patch token.
     */
    public function token(): string
    {
        return $this->patch->token;
    }

    /**
     * Get the patch description.
     */
    public function description(): string
    {
        return $this->patch->description;
    }

    /**
     * Get the patch date.
     */
    public function date(): Carbon
    {
        return $this->patch->date;
    }

    /**
     * Get the patch version.
     */
    public function version(): string
    {
        return $this->patch->version;
    }

    /**
     * Get the release archive.
     */
    public function archive(): ZipArchive
    {
        return $this->archive ??= new ZipArchive($this->getStoragePath());
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'description' => $this->description(),
            'date' => $this->date()->toJSON(),
            'token' => $this->token(),
            'isApplied' => $this->isApplied(),
        ];
    }

    /**
     * jsonSerialize
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
