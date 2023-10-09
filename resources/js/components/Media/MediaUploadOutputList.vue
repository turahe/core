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
  <ul class="divide-y divide-neutral-200 dark:divide-neutral-700">
    <li
      v-for="(file, index) in files"
      :key="index + file.name"
      class="group flex items-center space-x-3 py-4"
    >
      <div class="shrink-0">
        <span
          :class="[
            file.xhr && file.xhr.status >= 400
              ? 'bg-danger-500 text-white'
              : '',
            file.xhr && file.xhr.status < 400
              ? 'bg-success-500 text-white'
              : '',
            !file.xhr ? 'bg-neutral-600 text-neutral-100' : '',
            'inline-flex h-10 w-10 items-center justify-center rounded-full text-sm',
          ]"
        >
          <Icon
            icon="X"
            v-if="file.xhr && file.xhr.status >= 400"
            class="h-5 w-5"
          />
          <Icon icon="Check" v-else-if="file.xhr" class="h-5 w-5" />
          <Icon icon="CloudArrowUp" v-else class="h-5 w-5" />
        </span>
      </div>

      <div class="min-w-0 flex-1 truncate">
        <p class="text-sm font-medium text-neutral-800 dark:text-white">
          {{ file.name }}
        </p>
        <p class="text-sm text-neutral-500 dark:text-neutral-300">
          {{ localizedDateTime(new Date()) }}
        </p>
      </div>
      <div class="block shrink-0 md:hidden md:group-hover:block">
        <div class="flex items-center space-x-1">
          <!-- Allow remove only when there is no request in progress or there is error -->
          <div v-show="!file.xhr || file.xhr.status >= 400">
            <IButtonIcon icon="X" @click="$emit('remove-requested', index)" />
          </div>
        </div>
      </div>
    </li>
  </ul>
</template>
<script setup>
import { useDates } from '~/Core/resources/js/composables/useDates'

defineEmits(['remove-requested'])

defineProps({
  files: { type: Array, required: true },
})

const { localizedDateTime } = useDates()
</script>
