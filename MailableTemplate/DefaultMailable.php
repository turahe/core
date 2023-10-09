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

namespace Modules\Core\MailableTemplate;

class DefaultMailable
{
    /**
     * Create new default mail template
     *
     * @param  string  $message
     */
    public function __construct(protected string $html_message, protected string $subject, protected ?string $text_message = null)
    {
    }

    /**
     * Get the mailable default HTML message
     */
    public function htmlMessage(): string
    {
        return $this->html_message;
    }

    /**
     * Get the mailable default text message
     */
    public function textMessage(): ?string
    {
        return $this->text_message;
    }

    /**
     * Get the mailable default subject
     */
    public function subject(): string
    {
        return $this->subject;
    }
}
