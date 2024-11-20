<?php

namespace Turahe\Core\Common\Mail;

use Illuminate\Support\Arr;
use Symfony\Component\Mime\Email;

trait InteractsWithSymfonyMessage
{
    /**
     * Add the mail client headers to the symfony message
     *
     * @return static
     */
    protected function addHeadersToSymfonyMessage(Email $message)
    {
        foreach ($this->headers as $header) {
            $message->getHeaders()->addTextHeader($header['name'], $header['value']);
        }

        return $this;
    }

    /**
     * Add symfony message header
     */
    protected function addSymfonyMessageHeader(Email $message, string $name, string $value): static
    {
        $message->getHeaders()->addHeader($name, $value);

        return $this;
    }

    /**
     * Add symfony message In-Reply-To header
     */
    protected function addSymfonyMessageInReplyToHeader(Email $message, string $messageId): static
    {
        $this->addSymfonyMessageHeader($message, 'In-Reply-To', "<$messageId>");

        return $this;
    }

    /**
     * Add symfony message References header
     */
    protected function addSymfonyMessageReferencesHeader(Email $message, array|string $references): static
    {
        $value = array_map(fn ($id) => "<$id>", Arr::wrap($references));

        $this->addSymfonyMessageHeader($message, 'References', implode(',', $value));

        return $this;
    }

    /**
     * Add symfony message ID header
     */
    protected function addSymfonyMessageIdHeader(Email $message, string $name, string $value): static
    {
        $message->getHeaders()->addIdHeader($name, $value);

        return $this;
    }
}
