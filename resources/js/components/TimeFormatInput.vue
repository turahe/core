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
  <IFormSelect
    :id="inputId"
    :modelValue="modelValue"
    @change="handleChange($event)"
  >
    <option v-for="format in formats" :key="format" :value="format">
      {{ format }} [<span v-once v-text="formatForDisplay(format)"></span>]
    </option>
  </IFormSelect>
</template>
<script setup>
import { onMounted } from 'vue'

const formats = Innoclapps.config('time_formats')
const emit = defineEmits(['update:modelValue'])

const props = defineProps({
  modelValue: String,
  inputId: { type: String, default: 'time_format' },
})

function handleChange(value) {
  emit('update:modelValue', value)
}

function formatForDisplay(value) {
  return moment().formatPHP(value)
}

onMounted(() => handleChange(props.modelValue))
</script>
