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

namespace Turahe\Core;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Str;
use Turahe\Core\Contracts\HasNotificationsSettings;
use Turahe\Core\Facades\Innoclapps;
use Turahe\Core\MailableTemplate\MailableTemplate;
use Turahe\Users\Models\User;

class Notification extends BaseNotification
{
    use Queueable;

    /**
     * Get the notification's delivery channels.
     */
    public function via(HasNotificationsSettings $notifiable): array
    {
        // First, get the notifiable notifications settings
        $settings = $notifiable->getNotificationPreference(static::key());

        // Next we will check if the notifiable has notifications settings configured, if nothing configured
        // we will notify via all channels, by default notifications are enabled for all channels
        if (count($settings) === 0) {
            return static::availableChannels();
        }

        // Next, we will filter the channels the user specifically turned off notifications
        $except = array_keys(array_filter($settings, fn ($notify) => $notify === false));

        return array_values(array_diff(static::availableChannels(), $except));
    }

    /**
     * Get the mail representation of the notification.
     *
     * NOTE: When using database mail templates the locale, must be configured for the MailableTemplate
     */
    public function viaMailableTemplate(MailableTemplate $mailable, object $notifiable): MailableTemplate
    {
        if ($notifiable instanceof HasLocalePreference) {
            $mailable->locale($notifiable->preferredLocale());
        }

        // Automatically add the notifiable as "To"
        if (count($mailable->to) === 0 && $notifiable instanceof User) {
            $mailable->to($notifiable);
        }

        return $mailable;
    }

    /**
     * Determine if the notification should be sent.
     */
    public function shouldSend(HasNotificationsSettings $notifiable, string $channel): bool
    {
        if (Innoclapps::notificationsDisabled()) {
            return false;
        }

        // When the user turned off all notifications, only the broadcast will be available
        // In this case, we don't need to send any notification as the broadcast will broadcast invalid notification
        return ! ($channel === 'broadcast' && count($this->via($notifiable)) === 1);
    }

    /**
     * Provide the notification available delivery channels
     */
    public static function availableChannels(): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    /**
     * Get the notification unique key identifier
     */
    public static function key(): string
    {
        return Str::snake(class_basename(get_called_class()), '-');
    }

    /**
     * Get the displayable name of the notification
     */
    public static function name(): string
    {
        return Str::title(Str::snake(class_basename(get_called_class()), ' '));
    }

    /**
     * Get the notification description
     */
    public static function description(): ?string
    {
        return null;
    }

    /**
     * Define whether the notification is user-configurable
     */
    public static function configurable(): bool
    {
        return true;
    }
}
