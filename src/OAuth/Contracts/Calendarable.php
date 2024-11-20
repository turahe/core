<?php

declare(strict_types=1);

namespace Turahe\Core\OAuth\Contracts;

interface Calendarable
{
    /**
     * Get the OAuth account calendars
     */
    public function getCalendars();
}
