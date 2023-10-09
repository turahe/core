<!--
  - This source code is the proprietary and confidential information of
  - Nur Wachid. You may not disclose, copy, distribute,
  -  or use this code without the express written permission of
  - Nur Wachid.
  -
  - Copyright (c) 2023.
  -
  -
  -->

<template>
  <form
    @submit.prevent="saveReCaptchaSettings"
    @input="form.errors.clear($event.target.name)"
  >
    <ICard
      :header="$t('core::settings.recaptcha.recaptcha')"
      :overlay="!componentReady"
    >
      <template #actions>
        <a
          href="https://www.google.com/recaptcha/admin"
          class="link inline-flex items-center"
          target="_blank"
          rel="noopener noreferrer"
        >
          v2
          <Icon icon="ExternalLink" class="ml-1 h-4 w-4" />
        </a>
      </template>
      <div class="lg:flex lg:space-x-4">
        <IFormGroup
          class="w-full"
          :label="$t('core::settings.recaptcha.site_key')"
          label-for="recaptcha_site_key"
        >
          <IFormInput
            v-model="form.recaptcha_site_key"
            id="recaptcha_site_key"
          />
        </IFormGroup>
        <IFormGroup
          class="w-full"
          :label="$t('core::settings.recaptcha.secret_key')"
          label-for="recaptcha_secret_key"
        >
          <IFormInput
            v-model="form.recaptcha_secret_key"
            id="recaptcha_secret_key"
          />
        </IFormGroup>
      </div>

      <IFormGroup
        :description="$t('core::settings.recaptcha.ignored_ips_info')"
        :label="$t('core::settings.recaptcha.ignored_ips')"
        label-for="recaptcha_ignored_ips"
      >
        <IFormTextarea
          v-model="form.recaptcha_ignored_ips"
          id="recaptcha_ignored_ips"
        />
      </IFormGroup>

      <div
        class="mt-4 rounded-lg border border-primary-100 bg-gradient-to-tr from-primary-50/40 from-25% to-primary-100 px-10 py-6 dark:border-neutral-800 dark:bg-gradient-to-tl dark:from-neutral-600 dark:to-neutral-900 md:flex md:items-center"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 24 24"
          class="h-14 w-14 fill-primary-700 dark:fill-neutral-200"
        >
          <path
            d="M11.5 10.9c-4 0-7.9.9-11.5 2.8h22.9c-3.6-1.9-7.4-2.8-11.4-2.8zM16.7 14.8c-1.7 0-3.1 1.1-3.6 2.6-1-.3-1.9-.3-2.8-.1-.6-1.4-1.9-2.4-3.6-2.4-2.1 0-3.8 1.7-3.8 3.8 0 2.1 1.7 3.8 3.8 3.8s3.8-1.7 3.8-3.8v-.4c.8-.2 1.6-.1 2.4.2v.3c0 2.1 1.7 3.8 3.8 3.8 2.1 0 3.8-1.7 3.8-3.8 0-2.2-1.7-4-3.8-4zm-7.2 3.9c0 1.6-1.3 2.8-2.8 2.8-1.6 0-2.8-1.3-2.8-2.8s1.3-2.8 2.8-2.8c1.2 0 2.2.7 2.6 1.7.1.3.2.6.2 1.1 0-.1 0-.1 0 0zm7.2 2.8c-1.6 0-2.8-1.3-2.8-2.8s1.3-2.8 2.8-2.8 2.8 1.3 2.8 2.8-1.3 2.8-2.8 2.8zM16.7 2 9.9 3.1 6.6 2 4.4 8.7h14.5z"
          ></path>
        </svg>
        <div class="mt-5 md:ml-8 md:mt-0">
          <h4
            class="-mb-1 text-lg font-semibold text-primary-800 dark:text-neutral-100"
            v-t="'core::settings.recaptcha.dont_get_locked'"
          />
          <p
            class="text-primary-700 dark:text-neutral-300 md:max-w-xl"
            v-t="'core::settings.recaptcha.ensure_recaptcha_works'"
          />
        </div>
      </div>

      <template #footer>
        <IButton
          type="submit"
          :disabled="form.busy"
          :text="$t('core::app.save')"
        />
      </template>
    </ICard>
  </form>
</template>
<script setup>
import { useSettings } from './../useSettings'
const { form, submit, isReady: componentReady } = useSettings()

function saveReCaptchaSettings() {
  submit(form => {
    // Update the configuration for components that are using Innoclapps.appConfig.reCaptcha.configured
    // e.q. on web forms spam protection option
    Innoclapps.appConfig.reCaptcha.configured =
      form.recaptcha_secret_key != '' && form.recaptcha_site_key != ''
  })
}
</script>
