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
  <div class="text-center">
    <svg
      v-once
      class="mx-auto h-12 w-12 text-neutral-400"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
      aria-hidden="true"
    >
      <path
        vector-effect="non-scaling-stroke"
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"
      />
    </svg>
    <h3 class="mt-2 text-sm font-medium text-neutral-800 dark:text-white">
      {{ title }}
    </h3>
    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-300">
      {{ description }}
    </p>
    <div class="mt-6">
      <IButton
        @click="handleClickEvent($event, 'click', to)"
        :icon="buttonIcon"
        :text="buttonText"
      />
      <IButton
        v-if="secondButtonText"
        class="ml-4"
        :variant="secondButtonVariant"
        @click="handleClickEvent($event, 'click2', secondButtonTo)"
        :icon="secondButtonIcon"
        :text="secondButtonText"
      />
    </div>
  </div>
</template>
<script setup>
import { useRouter } from 'vue-router'

const emit = defineEmits(['click', 'click2'])
const router = useRouter()

const props = defineProps({
  title: String,
  description: String,
  buttonText: String,
  buttonIcon: { default: 'Plus', type: String },
  to: [Object, String],
  secondButtonText: String,
  secondButtonIcon: { default: 'Plus', type: String },
  secondButtonVariant: { default: 'secondary', type: String },
  secondButtonTo: [Object, String],
})

function handleClickEvent(e, type, to) {
  if (to) {
    router.push(to)
  } else {
    emit(type, e)
  }
}
</script>
