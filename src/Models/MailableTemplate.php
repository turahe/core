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

namespace Turahe\Core\Models;

use Illuminate\Database\Eloquent\Builder;
use Turahe\Core\Facades\MailableTemplates;
use Turahe\Core\Resource\ResourcePlaceholders;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Turahe\Core\Media\HasAttributesWithPendingMedia;
use Turahe\Core\Placeholders\Placeholders as BasePlaceholders;

/**
 * Turahe\Core\Models\MailableTemplate
 *
 * @property int $id
 * @property string $name
 * @property string $subject
 * @property string $mailable
 * @property string $locale
 * @property string $html_template
 * @property string|null $text_template
 *
 * @method static Builder|MailableTemplate forLocale(string $locale, ?string $mailable = null)
 * @method static Builder|MailableTemplate forMailable(string $mailable)
 * @method static Builder|MailableTemplate newModelQuery()
 * @method static Builder|MailableTemplate newQuery()
 * @method static Builder|Model orderByNullsLast(string $column, string $direction = 'asc')
 * @method static Builder|MailableTemplate query()
 * @method static Builder|MailableTemplate whereHtmlTemplate($value)
 * @method static Builder|MailableTemplate whereId($value)
 * @method static Builder|MailableTemplate whereLocale($value)
 * @method static Builder|MailableTemplate whereMailable($value)
 * @method static Builder|MailableTemplate whereName($value)
 * @method static Builder|MailableTemplate whereSubject($value)
 * @method static Builder|MailableTemplate whereTextTemplate($value)
 * @method static Builder|Model withCommon()
 *
 * @mixin \Eloquent
 */
class MailableTemplate extends Model
{
    use HasAttributesWithPendingMedia;
    use HasUlids;

    /**
     * Indicates if the model has timestamps
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'subject', 'html_template', 'text_template', 'locale',
    ];

    /**
     * Boot the MailableTemplate model.
     */
    protected static function boot(): void
    {
        parent::boot();

        MailableTemplates::seedIfRequired();
    }

    /**
     * Get the mail template mailable class
     *
     * @return \Turahe\Core\MailableTemplate\MailableTemplate
     */
    public function mailable()
    {
        return resolve($this->mailable);
    }

    /**
     * Get mailable template HTMl layout
     */
    public function getHtmlLayout(): ?string
    {
        return null;
    }

    /**
     * Get mailable template text layout
     */
    public function getTextLayout(): ?string
    {
        return null;
    }

    /**
     * Get the mail template placeholders
     */
    public function getPlaceholders(): ResourcePlaceholders|BasePlaceholders
    {
        if (! class_exists($this->mailable)) {
            return new BasePlaceholders([]);
        }

        $reflection = new \ReflectionClass($this->mailable);

        /** @var \Turahe\Core\MailableTemplate\MailableTemplate */
        $mailable = $reflection->newInstanceWithoutConstructor();

        return $mailable->placeholders() ?: new BasePlaceholders([]);
    }

    /**
     * Get mail template subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Get html template
     *
     * @return string
     */
    public function getHtmlTemplate()
    {
        return $this->html_template;
    }

    /**
     * Get text template
     *
     * @return string
     */
    public function getTextTemplate()
    {
        return $this->text_template;
    }

    /**
     * Get the attributes that may contain pending media
     */
    public function attributesWithPendingMedia(): string
    {
        return 'html_template';
    }

    /**
     * Scope a query to only include templates of a given locale.
     */
    public function scopeForLocale(Builder $query, string $locale, ?string $mailable = null): void
    {
        // @todo
        MailableTemplates::seedIfRequired();

        $query->where('locale', $locale);

        if ($mailable) {
            $query->forMailable($mailable);
        }
    }

    /**
     * Scope a query to only include templates of a given mailable.
     */
    public function scopeForMailable(Builder $query, string $mailable): void
    {
        $query->where('mailable', $mailable);
    }
}
