<?php

declare(strict_types=1);
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

namespace Turahe\Core\Google\Services;

use Google\Client;
use Google\Service\Exception as GoogleServiceException;
use Google\Service\Gmail\BatchDeleteMessagesRequest;
use Google\Service\Gmail\BatchModifyMessagesRequest;
use Illuminate\Support\Collection;
use Turahe\Core\Google\Services\Message\Mail;
use Turahe\Core\Google\Services\Message\SendMail;

class Message extends Service
{
    /**
     * Indicates whether to preload the messages and it's attachments
     */
    protected bool|string $preload = false;

    /**
     * Request params
     */
    protected array $params = [];

    /**
     * @link https://developers.google.com/gmail/api/guides/batch
     */
    protected int $batchSize = 50;

    /**
     * Message constructor.
     */
    public function __construct(Client $client)
    {
        parent::__construct($client, \Google\Service\Gmail::class);
    }

    /**
     * Create new SendMail instance.
     */
    public function sendMail(): SendMail
    {
        return new SendMail($this->client);
    }

    /**
     * Preload the messages or preload them mTurahe\Coreed in a given class.
     */
    public function preload(bool|string $state = true): static
    {
        $this->preload = $state;

        return $this;
    }

    /**
     * Retrieve messages
     *
     * @return \Turahe\Core\Google\Services\MessageCollection
     */
    public function all(?string $pageToken = null)
    {
        if (! is_null($pageToken)) {
            $this->params['pageToken'] = $pageToken;
        }

        /** @var \Google\Service\Gmail */
        $service = $this->service;

        /**
         * List of messages. Note that each message resource contains only an id and a threadId.
         * Additional message details can be fetched using the messages.get method.
         */
        $response = $service->users_messages->listUsersMessages('me', $this->params);

        $messages = (new MessageCollection)->setMessageService($this)
            ->setNextPageToken($response->getNextPageToken())
            ->setResultSizeEstimate($response->getResultSizeEstimate());

        // If preload, create a batch requests for the messages
        if ($this->preload) {
            return $messages->concat(
                $this->batchRequest($response->getMessages())
            )
                ->when(
                    is_string($this->preload),
                    fn ($collection) => $collection->mapInto($this->preload)
                );
        }

        return $messages->concat($response->getMessages())
            ->map(fn ($message) => new Mail($this->client, $message))
            ->when(
                is_string($this->preload),
                fn ($collection) => $collection->mapInto($this->preload)
            );

        /**
         * If preloaded, load the attachments too
         * UPDATE: Do not preload, takes too much memory
         */

        // if ($this->preload) {
        //     $this->loadAttachments($messages);
        // }
    }

    /**
     * Get message by id
     */
    public function get(string $id): Mail
    {
        /** @var \Google\Service\Gmail */
        $service = $this->service;

        /** @var \Google\Service\Gmail\Message */
        $message = $service->users_messages->get('me', $id);

        return new Mail($this->client, $message);
    }

    /**
     * Creates a batch request to get all emails in a single call
     *
     * @return \Illuminate\Support\Collection
     */
    public function batchRequest(array|Collection $messages)
    {
        $this->client->setUseBatch(true);

        $result = new Collection;

        if (! $messages instanceof Collection) {
            $messages = new Collection($messages);
        }

        $messages->chunk($this->batchSize)->each(function ($messages) use (&$result): void {
            /** @var \Google\Service\Gmail */
            $service = $this->service;

            $batch = $service->createBatch();

            foreach ($messages as $key => $message) {
                /** @var \Psr\Http\Message\RequestInterface */
                $request = $service->users_messages->get('me', $message->getId());

                $batch->add($request, $key + 1);
            }

            $result = $result->when($batch->execute(), function ($collection, $messages) {
                (new Collection($messages))->reject(
                    fn ($message) => $message instanceof GoogleServiceException
                )
                    ->each(function ($message) use (&$collection): void {
                        $collection->push(
                            new Mail($this->client, $message)
                        );
                    });

                return $collection;
            });

            $batch = null;
        });

        $this->client->setUseBatch(false);

        return $result;
    }

    /**
     * Batch move messages to a given folder
     */
    public function batchModify(array $messages, array $removeLabelIds = [], array $addLabelIds = []): bool
    {
        /** @var \Google\Service\Gmail */
        $service = $this->service;

        $request = new BatchModifyMessagesRequest;

        $request->setRemoveLabelIds($removeLabelIds);
        $request->setAddLabelIds($addLabelIds);
        $request->setIds($messages);

        $service->users_messages->batchModify('me', $request);

        return true;
    }

    /**
     * Batch delete messages
     */
    public function batchDelete(array $messages): void
    {
        /** @var \Google\Service\Gmail */
        $service = $this->service;

        $request = new BatchDeleteMessagesRequest;

        $request->setIds($messages);

        $service->users_messages->batchDelete('me', $request);
    }

    /**
     * Limit the messages coming from the query
     *
     * @return static
     */
    public function take(int $number)
    {
        $this->params['maxResults'] = abs($number);

        return $this;
    }

    /**
     * Whether to include the spam and the trash messages
     *
     * @return static
     */
    public function includeSpamTrash(bool $boolean = true)
    {
        $this->params['includeSpamTrash'] = $boolean;

        return $this;
    }

    /**
     * Filter messages only with specific label ids
     *
     * @return static
     */
    public function withLabels(array|string $ids)
    {
        $this->params['labelIds'] = (array) $ids;

        return $this;
    }

    /**
     * Filter messages by subject
     *
     *
     * @return static
     */
    public function subject(string $query)
    {
        $this->addQuery("[{$query}]");

        return $this;
    }

    /**
     * Filter to get only emails to a specific email address
     *
     *
     * @return static
     */
    public function to(string $email)
    {
        $this->addQuery("to:{$email}");

        return $this;
    }

    /**
     * Filter to get only emails from a specific email address
     *
     *
     * @return static
     */
    public function from(string $email)
    {
        $this->addQuery("from:{$email}");

        return $this;
    }

    /**
     * Filter to get only emails after a specific date
     *
     *
     * @return static
     */
    public function after(string|int $date)
    {
        $this->addQuery("after:{$date}");

        return $this;
    }

    /**
     * Filter to get only emails before a specific date
     *
     *
     * @return static
     */
    public function before(string $date)
    {
        $this->addQuery("before:{$date}");

        return $this;
    }

    /**
     * Filters emails by label
     *
     * Example:
     * starred, inbox, spam, chats, sent, draft, trash
     *
     *
     * @return static
     */
    public function in(string $box)
    {
        $this->addQuery("in:{$box}");

        return $this;
    }

    /**
     * Filter emails with attachment
     *
     * @return static
     */
    public function hasAttachment()
    {
        $this->addQuery('has:attachment');

        return $this;
    }

    /**
     * Eager load the messages attachments
     *
     * @param  \Illuminate\Support\Collection  $messages
     * @return static
     */
    public function loadAttachments(&$messages)
    {
        $attachments = $this->prepareAttachmentsForBatch($messages);

        $this->client->setUseBatch(true);

        $attachments->chunk($this->batchSize)->each(function ($attachments) use (&$messages): void {
            /** @var \Google\Service\Gmail */
            $service = $this->service;

            $batch = $service->createBatch();

            foreach ($attachments as $data) {
                /** @var \Psr\Http\Message\RequestInterface */
                $request = $service->users_messages_attachments->get('me', $data->message_id, $data->attachment_id);

                $batch->add($request, $data->attachment_key.'-'.$data->message_key);
            }

            $response = $batch->execute();

            $this->handleAttachmentsBatchResponse($response, $messages);

            $response = null;
            $batch = null;
        });

        $this->client->setUseBatch(false);

        return $this;
    }

    /**
     * Prepare the attachments data for batch request
     *
     * @return \Illuminate\Support\Collection
     */
    protected function prepareAttachmentsForBatch(Collection $messages)
    {
        $attachments = new Collection;

        foreach ($messages as $messageKey => $message) {
            foreach ($message->getAttachments() as $attachmentKey => $attachment) {
                $attachments->push((object) [
                    'attachment_id' => $attachment->getId(),
                    'message_id' => $message->getId(),
                    'message_key' => $messageKey,
                    'attachment_key' => $attachmentKey,
                ]);
            }
        }

        return $attachments;
    }

    /**
     * Handles the attachments batch response
     *
     * @param  \Illuminate\Support\Collection  $messages
     */
    protected function handleAttachmentsBatchResponse(array $response, &$messages): void
    {
        foreach ($response as $key => $attachment) {
            // e.q. response-0-2
            $keyPieces = explode('-', $key);

            $attachmentKey = $keyPieces[1];
            $messageKey = $keyPieces[2];

            $messages->get($messageKey)
                ->getAttachments()
                ->get($attachmentKey)
                ->setContent($attachment->getData());
        }
    }

    /**
     * Add query to params
     *
     * @return Message
     */
    protected function addQuery(string $query)
    {
        if (isset($this->params['q'])) {
            $this->params['q'] .= ' '.$query;
        } else {
            $this->params['q'] = $query;
        }

        return $this;
    }
}
