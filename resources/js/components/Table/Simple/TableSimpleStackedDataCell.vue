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
  <p class="flex items-center font-normal">
    <span class="mr-1 font-medium text-neutral-800 dark:text-neutral-100">
      {{ field.label }}:
    </span>
    <slot v-bind="slotProps"></slot>
  </p>
</template>
<script setup>
import { computed } from 'vue'

const props = defineProps({
  field: { type: Object, required: true },
  item: { type: Object, required: true },
  formatter: { type: Function, required: true },
})

const slotProps = computed(() => ({
  row: props.item,
  column: props.field,
  formatted: formatted.value,
}))

const formatted = computed(() => props.formatter(props.item, props.field))
</script>
