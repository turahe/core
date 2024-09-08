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

namespace Turahe\Core\Tests\Feature\MailableTemplate;

use Illuminate\Support\Facades\File;
use Tests\Fixtures\SampleMailTemplate;
use Turahe\Core\Tests\TestCase;
use Turahe\Core\Facades\Innoclapps;
use Turahe\Core\Facades\MailableTemplates;
use Turahe\Core\Models\MailableTemplate;

class MailableControllerTest extends TestCase
{
    public function test_unauthenticated_user_cannot_access_mailable_templates_endpoints()
    {
        $this->getJson('/api/mailables')->assertUnauthorized();
        $this->getJson('/api/mailables/en/locale')->assertUnauthorized();
        $this->getJson('/api/mailables/1')->assertUnauthorized();
        $this->putJson('/api/mailables/1')->assertUnauthorized();
    }

    public function test_unauthorized_user_cannot_access_mailable_template_endpoints()
    {
        $this->asRegularUser()->signIn();

        $this->getJson('/api/mailables')->assertForbidden();
        $this->getJson('/api/mailables/en/locale')->assertForbidden();
        $this->getJson('/api/mailables/1')->assertForbidden();
        $this->putJson('/api/mailables/1')->assertForbidden();
    }

    public function test_user_can_retrieve_all_mailable_templates()
    {
        MailableTemplates::flush()->dontDiscover();

        $this->signIn();

        MailableTemplates::register(SampleMailTemplate::class)->seedIfRequired();

        $this->getJson('/api/mailables')
            ->assertJsonCount(count(Innoclapps::locales()))
            ->assertJsonPath('0.name', SampleMailTemplate::name());
    }

    public function test_user_can_retrieve_mailable_templates_by_locale()
    {
        MailableTemplates::flush()->dontDiscover();

        $this->signIn();

        MailableTemplates::register(SampleMailTemplate::class)->seedIfRequired();

        $this->getJson('/api/mailables/en/locale')->assertJsonCount(1)->assertJsonPath('0.name', SampleMailTemplate::name());
    }

    public function test_user_can_retrieve_mailable_template()
    {
        MailableTemplates::dontDiscover();

        $this->signIn();

        MailableTemplates::flushCache()->register(SampleMailTemplate::class)->seedIfRequired();

        $template = MailableTemplate::forMailable(SampleMailTemplate::class)->forLocale('en')->first();

        $this->getJson('/api/mailables/'.$template->id)->assertJson(['name' => SampleMailTemplate::name()]);
    }

    public function test_user_can_update_mailable_template()
    {
        MailableTemplates::dontDiscover();

        $this->signIn();

        MailableTemplates::flushCache()->register(SampleMailTemplate::class)->seedIfRequired();

        $template = MailableTemplate::forMailable(SampleMailTemplate::class)->forLocale('en')->first();

        $this->putJson('/api/mailables/'.$template->id, $data = [
            'subject' => 'Changed Subject',
            'html_template' => 'Changed HTML Template',
            'text_template' => 'Changed Text Template',
        ])->assertJson($data);
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
