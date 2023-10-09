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
  <ICard :header="$t('core::settings.tools.tools')" no-body>
    <ul class="divide-y divide-neutral-200 dark:divide-neutral-700">
      <li v-for="tool in tools" :key="tool" class="px-4 py-4 sm:px-6">
        <div
          class="flex flex-col space-y-2 sm:flex-row sm:items-center sm:space-y-0"
        >
          <div class="grow">
            <h5
              class="font-medium leading-relaxed text-neutral-900 dark:text-neutral-200"
              v-text="tool"
            />
            <span
              v-t="'core::settings.tools.' + tool"
              class="text-sm text-neutral-600 dark:text-neutral-300"
            />
          </div>
          <div class="shrink-0">
            <IButton
              variant="white"
              size="sm"
              @click="run(tool)"
              :loading="toolBeingExecuted === tool"
              :disabled="toolBeingExecuted !== null"
              :text="$t('core::settings.tools.run')"
            />
          </div>
        </div>
      </li>
    </ul>
  </ICard>
</template>
<script setup>
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

const tools = [
  'json-language',
  'clear-cache',
  'optimize',
  'storage-link',
  'migrate', // used in migrate.blade.php as well
  'seed-mailables',
]

const toolBeingExecuted = ref(null)

function run(tool) {
  toolBeingExecuted.value = tool
  Innoclapps.request()
    .get(`/tools/${tool}`)
    .then(() => {
      Innoclapps.success(t('core::settings.tools.executed'))
      setTimeout(() => window.location.reload(true), 1000)
    })
    .finally(() => (toolBeingExecuted.value = false))
}
</script>
