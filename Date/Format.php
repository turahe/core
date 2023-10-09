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

namespace Modules\Core\Date;

use Illuminate\Support\Facades\Auth;
use Modules\Core\Contracts\Localizeable;

class Format
{
    /**
     * Format a timestamp
     *
     * @param  mixed  $value
     * @param  string  $format
     * @return string
     */
    public function format($value, $format, ?Localizeable $user = null)
    {
        if (is_null($value)) {
            return null;
        }

        return Carbon::inUserTimezone($value, $user)->translatedFormat($format);
    }

    /**
     * Format date timestamp
     *
     * @param  mixed  $value
     * @return string
     */
    public function date($value, ?Localizeable $user = null)
    {
        return with($this->determineUser($user), function ($user) use ($value) {
            return $this->format(
                $value,
                $user ? $user->getLocalDateFormat() : config('core.date_format'),
                $user
            );
        });
    }

    /**
     * Format time timestamp
     *
     * @param  mixed  $value
     * @return string
     */
    public function time($value, ?Localizeable $user = null)
    {
        if (is_null($value)) {
            return null;
        }

        return with($this->determineUser($user), function ($user) use ($value) {
            return $this->format(
                $value,
                $user ? $user->getLocalTimeFormat() : config('core.time_format'),
                $user
            );
        });
    }

    /**
     * Format date time timestamp
     *
     * @param  mixed  $value
     * @return string
     */
    public function dateTime($value, ?Localizeable $user = null)
    {
        $date = $this->date($value, $user).' '.$this->time($value, $user);

        if (trim($date) === '') {
            return null;
        }

        return $date;
    }

    /**
     * Format in UTC when the date and time columns are separate
     *
     * @param  string  $date
     * @param  string  $time
     * @return string
     */
    public function separateDateAndTime($date, $time, ?Localizeable $user = null)
    {
        $dateInstance = Carbon::parse($date);

        return with($time ? Carbon::parse(
            $dateInstance->format('Y-m-d').' '.$time
        )->format('H:i') : null, function ($time) use ($dateInstance, $user) {
            return $time ? $this->dateTime(
                $dateInstance->format('Y-m-d').' '.$time.':00',
                $user
            ) : $this->date($dateInstance->format('Y-m-d'), $user);
        });
    }

    /**
     * Format date diff for humans
     *
     * @param $dateValue
     * @return string|null
     */
    public function diffForHumans($value, ?Localizeable $user = null)
    {
        if (is_null($value)) {
            return null;
        }

        return Carbon::inUserTimezone($value, $user)->diffForHumans();
    }

    /**
     * Determine the user based on the user param
     *
     * @param  mixed  $user
     */
    protected function determineUser(?Localizeable $user): ?Localizeable
    {
        return $user ?: Auth::user();
    }
}
