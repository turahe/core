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
  <div
    :class="[
      'bg-neutral-50 dark:bg-neutral-700',
      condensed ? 'px-6 py-3' : 'px-4 py-5 sm:px-6',
    ]"
  >
    <slot></slot>
  </div>
</template>
<script setup>
const props = defineProps({ condensed: { type: Boolean, default: false } })
</script>
