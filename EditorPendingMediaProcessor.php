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

namespace Modules\Core;

use Stringable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Modules\Core\Models\Media;
use KubAT\PhpSimple\HtmlDomParser;
use Modules\Core\Models\PendingMedia;
use Illuminate\Database\Eloquent\Builder;

class EditorPendingMediaProcessor
{
    /**
     * The media directory where the pending media
     * will be moved after save/create
     */
    protected static string $mediaDir = 'editor';

    protected static string $verifyMediaRegex = '/[\da-f]{8}-[\da-f]{4}-[\da-f]{4}-[\da-f]{4}-[\da-f]{12}/m';

    /**
     * Process editor fields by given new and original content
     *
     * @param  string|\Stringable  $newContent
     * @param  string|\Stringable  $originalContent
     */
    public function process($newContent, $originalContent): void
    {
        if ($newContent instanceof Stringable) {
            $newContent = (string) $newContent;
        }

        if ($originalContent instanceof Stringable) {
            $originalContent = (string) $originalContent;
        }

        // First store the current media tokens
        $tokens = $this->getMediaTokensFromContent($newContent);

        // After that only process the new media
        $this->processNewMedia($tokens);

        // Finaly removed any removed media
        if ($deletedTokens = $this->getRemovedMedia($originalContent, $tokens)) {
            Media::whereIn('token', $deletedTokens)->get()->each->delete();
        }
    }

    /**
     * Process editor pending media via given model
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array|string  $field
     */
    public function processViaModel($model, array|string $fields): void
    {
        foreach (Arr::wrap($fields) as $field) {
            $value = $model->{$field};

            if ($value instanceof Stringable) {
                $value = (string) $value;
            }

            // First store the current media tokens
            $tokens = $this->getMediaTokensFromContent($value);

            // After that only process the new media
            $this->processNewMedia($tokens);

            // Check if it's update, if yes, get the removed media tokens and delete them
            if (! $model->wasRecentlyCreated) {
                $originalValue = $model->getOriginal($field);

                if ($value instanceof Stringable) {
                    $originalValue = (string) $originalValue;
                }

                if ($deletedTokens = $this->getRemovedMedia($originalValue, $tokens)) {
                    Media::whereIn('token', $deletedTokens)->get()->each->delete();
                }
            }
        }
    }

    /**
     * Delete all media via model
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    public function deleteAllViaModel($model, array|string $fields): void
    {
        foreach (Arr::wrap($fields) as $field) {
            $this->deleteAllByContent($model->{$field});
        }
    }

    /**
     * Delete media by given content
     */
    public function deleteAllByContent($content): void
    {
        if ($deletedTokens = $this->getMediaTokensFromContent($content)) {
            Media::whereIn('token', $deletedTokens)->get()->each->delete();
        }
    }

    /**
     * Process the new media by given tokens
     *
     * @param  array  $tokens
     */
    protected function processNewMedia($tokens): void
    {
        // Handle all pending medias and move them to the appropriate
        // directory and also delete the pending record from the pending table after move
        // From the current, we will get only the pending which are not yet processed
        PendingMedia::with('attachment')
            ->whereHas('attachment', fn (Builder $query) => $query->whereIn('token', $tokens))
            ->get()
            ->each(function ($pending) {
                $pending->attachment->move(static::$mediaDir, Str::random(40));
                $pending->delete();
            });
    }

    /**
     * Get the removed media from the editor content
     *
     * @param  string|\Stringable|array|null  $originalContent
     * @param  string|\Stringable|array  $newContent
     */
    protected function getRemovedMedia($originalContent, $newContent): array
    {
        if ($newContent instanceof Stringable) {
            $newContent = (string) $newContent;
        }

        if ($originalContent instanceof Stringable) {
            $originalContent = (string) $originalContent;
        }

        if (is_null($originalContent)) {
            return [];
        }

        return array_diff(
            is_string($originalContent) ? $this->getMediaTokensFromContent($originalContent) : $originalContent,
            is_string($newContent) ? $this->getMediaTokensFromContent($newContent) : $newContent
        );
    }

    /**
     * Get the media current token via the content
     *
     * @param  string|\Stringable  $content
     */
    protected function getMediaTokensFromContent($content): array
    {
        if ($content instanceof Stringable) {
            $content = (string) $content;
        }

        return array_merge(
            $this->getMediaTokensFromImagesAndVideos($content),
            $this->getMediaTokensInlineBackgroundImages($content),
        );
    }

    protected function getMediaTokensFromImagesAndVideos(?string $content): array
    {
        $sources = [];

        if (! $dom = HtmlDomParser::str_get_html($content)) {
            return $sources;
        }

        // Process images and videos
        foreach ($dom->find('img,source') as $element) {
            if ($src = $element->getAttribute('src')) {
                // Check if is really media by checking the uuid in the image or video src
                preg_match(static::$verifyMediaRegex, $src, $matches);

                if (count($matches) === 1) {
                    $sources[] = $matches[0];
                }
            }
        }

        return $sources;
    }

    protected function getMediaTokensInlineBackgroundImages(?string $content): array
    {
        $sources = [];

        if (! $content) {
            return $sources;
        }

        $bgImageMediaRegex = '/background\-image:(?: {1,}|)url(?: {1,}|)\([\'|"](.*)[\'|"]\)/';

        preg_match_all($bgImageMediaRegex, html_entity_decode($content, ENT_QUOTES), $bgImages, PREG_SET_ORDER, 0);

        foreach ($bgImages as $match) {
            if (preg_match(static::$verifyMediaRegex, $match[1], $matches)) {
                $sources[] = $matches[0];
            }
        }

        return $sources;
    }
}
