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
  <div class="flex animate-pulse">
    <div class="flex-1 space-y-4">
      <div
        v-for="count in total"
        :key="count"
        class="h-9 rounded-md bg-neutral-200 dark:bg-neutral-700"
        :class="randomPlaceholderWidth()"
      />
    </div>
  </div>
</template>
<script setup>
defineProps({
  total: {
    default: 8,
    type: Number,
  },
})

const placeholderWidths = ['w-full', 'w-1/2', 'w-5/6', 'w-3/4']

function randomPlaceholderWidth() {
  return placeholderWidths[Math.floor(Math.random() * placeholderWidths.length)]
}
</script>
