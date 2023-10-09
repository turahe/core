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
  <TabGroup :selected-index="modelValue" @change="handleChangeEvent">
    <slot></slot>
  </TabGroup>
</template>

<script setup>
import { TabGroup } from '@headlessui/vue'

const emit = defineEmits(['update:modelValue'])

const props = defineProps({ modelValue: Number })

function handleChangeEvent(index) {
  emit('update:modelValue', index)
}
</script>
