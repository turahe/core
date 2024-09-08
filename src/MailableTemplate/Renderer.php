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

use Illuminate\Support\Str;
use Mustache_Engine;
use Turahe\Core\MailableTemplate\Exceptions\CannotRenderMailableTemplate;
use Turahe\Core\Placeholders\Placeholders;

class Renderer
{
    /**
     * Initialize new Renderer instance.
     */
    public function __construct(
        protected string $htmlTemplate,
        protected string $subject,
        protected Mustache_Engine $mustache,
        protected ?Placeholders $placeholders = null,
        protected ?string $htmlLayout = null,
        protected ?string $textTemplate = null,
        protected ?string $textLayout = null,
    ) {}

    /**
     * Render mail template HTML layout
     *
     * @return string|null
     */
    public function renderHtmlLayout()
    {
        $body = $this->mustache->render(
            $this->htmlTemplate,
            $this->placeholders?->parse(),
        );

        return $this->renderInLayout($body, $this->htmlLayout);
    }

    /**
     * Render mail template text layout
     *
     * @return string|null
     */
    public function renderTextLayout()
    {
        if (! $this->textTemplate) {
            return null;
        }

        $body = $this->mustache->render(
            $this->textTemplate,
            $this->placeholders?->parse('text')
        );

        return $this->renderInLayout($body, $this->textLayout);
    }

    /**
     * Render mail template subject
     *
     * @return string
     */
    public function renderSubject()
    {
        return $this->mustache->render(
            $this->subject,
            $this->placeholders?->parse('text')
        );
    }

    /**
     * Render mail template content in layout
     *
     * @return string
     *
     * @throws \Turahe\Core\MailableTemplate\Exceptions\CannotRenderMailableTemplate
     */
    protected function renderInLayout(string $body, ?string $layout)
    {
        $this->guardAgainstInvalidLayout($layout ??= '{{{ mailBody }}}');

        $data = array_merge(['mailBody' => $body], $this->placeholders?->parse());

        return $this->mustache->render($layout, $data);
    }

    /**
     * Guard layout body
     *
     * @return void
     *
     * @throws \Turahe\Core\MailableTemplate\Exceptions\CannotRenderMailableTemplate
     *
     * Ensures that body placeholder exists in the layout
     */
    protected function guardAgainstInvalidLayout(string $layout)
    {
        $bodyAble = [
            '{{{mailBody}}}',
            '{{{ mailBody }}}',
            '{{mailBody}}',
            '{{ mailBody }}',
            '{{ $mailBody }}',
            '{!! $mailBody !!}',
        ];

        if (! Str::contains($layout, $bodyAble)) {
            throw CannotRenderMailableTemplate::layoutDoesNotContainABodyPlaceHolder();
        }
    }
}
