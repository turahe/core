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
  <div class="relative">
    <div
      v-if="field.inputGroupPrepend"
      class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
    >
      <Icon
        v-if="field.icon"
        :icon="field.icon"
        class="h-5 w-5 text-neutral-500 dark:text-neutral-300"
      />
      <span
        v-else
        class="text-sm text-neutral-500 dark:text-neutral-300"
        v-text="field.inputGroupPrepend"
      />
    </div>

    <slot></slot>

    <div
      class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3"
      v-if="field.inputGroupAppend"
    >
      <Icon
        v-if="field.icon"
        :icon="field.icon"
        class="h-5 w-5 text-neutral-500 dark:text-neutral-300"
      />
      <span
        v-else
        class="text-sm text-neutral-500 dark:text-neutral-300"
        v-text="field.inputGroupAppend"
      />
    </div>
  </div>
</template>
<script setup>
defineProps(['field'])
</script>
