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

namespace Turahe\Core\Tests\Unit;

use Illuminate\Support\Facades\File;
use Turahe\Core\Facades\MailableTemplates;
use Turahe\Core\Models\MailableTemplate;
use Turahe\Core\Tests\TestCase;

class MailableTest extends TestCase
{
    public function test_mailable_templates_are_seeded_when_new_locale_exist()
    {
        File::ensureDirectoryExists(lang_path('en_TEST'));

        MailableTemplates::seedIfRequired();

        $total = count(MailableTemplates::get());
        $this->assertCount($total, MailableTemplate::forLocale('en_TEST')->get());
    }

    public function test_mailable_templates_are_seeded_for_all_locales()
    {
        File::ensureDirectoryExists(lang_path('en_TEST'));
        File::ensureDirectoryExists(lang_path('fr_TEST'));

        MailableTemplates::seedIfRequired();

        $total = count(MailableTemplates::get());
        $this->assertCount($total, MailableTemplate::forLocale('en_TEST')->get());
        $this->assertCount($total, MailableTemplate::forLocale('fr_TEST')->get());
    }

    protected function tearDown(): void
    {
        foreach (['en_TEST', 'fr_TEST'] as $locale) {
            $path = lang_path($locale);

            if (is_dir($path)) {
                File::deepCleanDirectory($path);
            }
        }

        parent::tearDown();
    }
}
