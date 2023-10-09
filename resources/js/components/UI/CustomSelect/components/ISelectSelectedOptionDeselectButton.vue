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
  <button
    type="button"
    :disabled="disabled"
    @click.prevent.stop="deselect(option)"
    class="ml-1 text-neutral-400 focus:outline-none hover:text-neutral-600 dark:text-neutral-200 dark:hover:text-neutral-400"
    :title="`Deselect ${label}`"
    :aria-label="`Deselect ${label}`"
  >
    <Icon icon="X" class="h-4 w-4" />
  </button>
</template>
<script setup>
const props = defineProps({
  option: { required: true },
  deselect: { type: Function, required: true },
  disabled: Boolean,
  label: { type: [String, Number], required: true },
})
</script>
