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
  <ICustomSelect
    :input-id="inputId"
    :clearable="false"
    :modelValue="modelValue"
    @option:selected="handleChange($event)"
    :options="formats"
  >
    <template #option="option">
      {{ option.label + ' [' + formatLabel(option) + ']' }}
    </template>
  </ICustomSelect>
</template>
<script setup>
import { onMounted } from 'vue'

const emit = defineEmits(['update:modelValue'])

const props = defineProps({
  modelValue: String,
  inputId: { type: String, default: 'date_format' },
})

const formats = Innoclapps.config('date_formats')

function handleChange(value) {
  emit('update:modelValue', value)
}

function formatLabel(option) {
  return moment().formatPHP(option.label)
}

onMounted(() => {
  // Emit the initial value in case it's only taken from the configuration to fill the form
  handleChange(props.modelValue)
})
</script>
