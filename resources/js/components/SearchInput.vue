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
  <div class="relative rounded-md shadow-sm">
    <div
      class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
    >
      <Icon icon="SearchSolid" class="h-5 w-5 text-neutral-400" />
    </div>
    <IFormInput
      class="pl-10"
      v-bind="$attrs"
      type="search"
      :disabled="disabled"
      :modelValue="modelValue"
      :id="inputId"
      @click="$emit('click', $event)"
      @keydown.enter="emitEvent($event)"
      @input="emitEvent($event)"
      :placeholder="placeholder || $t('core::app.search')"
    />
  </div>
</template>
<script>
export default {
  inheritAttrs: false,
}
</script>
<script setup>
import debounce from 'lodash/debounce'

const emit = defineEmits(['click', 'input', 'update:modelValue'])

defineProps({
  modelValue: {},
  inputId: String,
  placeholder: String,
  disabled: {
    default: false,
  },
})

const emitEvent = debounce(function (value) {
  if (value instanceof KeyboardEvent) {
    value = value.target.value
  }

  emit('update:modelValue', value)
  emit('input', value)
}, 650)
</script>
