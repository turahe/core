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
  <select
    ref="selectRef"
    :id="id"
    :name="name"
    :autofocus="autofocus"
    :placeholder="placeholder"
    :tabindex="tabindex"
    :disabled="disabled"
    :required="required"
    :multiple="multiple"
    :class="[
      'form-select dark:bg-neutral-700 dark:text-white',
      {
        'form-select-sm': size === 'sm',
        'form-select-lg': size === 'lg',
        rounded: rounded && size === 'sm',
        'rounded-md': rounded && size !== 'sm',
        'border-neutral-300 dark:border-neutral-500': bordered,
        'border-transparent': !bordered,
      },
    ]"
    :value="modelValue"
    @blur="blurHandler"
    @focus="focusHandler"
    @input="inputHandler"
    @change="changeHandler"
  >
    <slot></slot>
  </select>
</template>
<script setup>
import { ref } from 'vue'
import htmlInputProps from './htmlInputProps'

const emit = defineEmits([
  'update:modelValue',
  'focus',
  'blur',
  'input',
  'change',
])

const props = defineProps({
  modelValue: {},
  placeholder: String,
  multiple: Boolean,
  rounded: { default: true, type: Boolean },
  bordered: { default: true, type: Boolean },
  size: {
    type: [String, Boolean],
    default: '',
    validator(value) {
      return ['sm', 'lg', '', true, false].includes(value)
    },
  },
  ...htmlInputProps,
})

const selectRef = ref(null)

function changeHandler(e) {
  emit('update:modelValue', e.target.value)
  emit('change', e.target.value)
}

function inputHandler(e) {
  emit('input', e.target.value)
}

function blurHandler(e) {
  emit('blur', e)
}

function focusHandler(e) {
  emit('focus', e)
}

function blur() {
  selectRef.value.blur()
}

function focus(options) {
  selectRef.value.focus(options)
}

defineExpose({ blur, focus })
</script>
