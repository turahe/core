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
  <ICard
    :header="$t('core::settings.security.security')"
    no-body
    :overlay="!componentReady"
  >
    <ul class="divide-y divide-neutral-200 dark:divide-neutral-700">
      <li class="px-4 py-4 sm:px-6">
        <div
          class="space-x-0 space-y-3 md:flex md:items-center md:justify-between md:space-y-0 lg:space-x-3"
        >
          <div>
            <h5
              class="font-medium leading-relaxed text-neutral-700 dark:text-neutral-100"
              v-t="'core::settings.security.disable_password_forgot'"
            />
            <p
              class="break-words text-sm text-neutral-600 dark:text-neutral-300"
              v-t="'core::settings.security.disable_password_forgot_info'"
            />
          </div>
          <div>
            <IFormToggle
              :value="true"
              :unchecked-value="false"
              @change="submit"
              v-model="form.disable_password_forgot"
            />
          </div>
        </div>
      </li>
      <li class="px-4 py-4 sm:px-6">
        <div
          class="space-x-0 space-y-3 md:flex md:items-center md:justify-between md:space-y-0 lg:space-x-3"
        >
          <div>
            <h5
              class="font-medium leading-relaxed text-neutral-700 dark:text-neutral-100"
              v-t="'core::settings.security.block_bad_visitors'"
            />
            <p
              class="break-words text-sm text-neutral-600 dark:text-neutral-300"
              v-t="'core::settings.security.block_bad_visitors_info'"
            />
          </div>
          <div>
            <IFormToggle
              :value="true"
              :unchecked-value="false"
              @change="submit"
              v-model="form.block_bad_visitors"
            />
          </div>
        </div>
      </li>
    </ul>
  </ICard>
</template>
<script setup>
import { useSettings } from './../useSettings'
const { form, submit, isReady: componentReady } = useSettings()
</script>
