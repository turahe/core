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
  <textarea
    ref="textareaRef"
    :id="id"
    :name="name"
    :tabindex="tabindex"
    :autocomplete="autocomplete"
    :autofocus="autofocus"
    :required="required"
    :placeholder="placeholder"
    :pattern="pattern"
    :wrap="wrap"
    :minlength="minlength"
    :maxlength="maxlength"
    :rows="rows"
    :cols="cols"
    :disabled="disabled"
    v-model="value"
    @blur="blurHandler"
    @focus="focusHandler"
    @keyup="keyupHandler"
    @keydown="keydownHandler"
    @input="inputHandler"
    :class="{
      'resize-none overflow-y-hidden': resizeable,
      'border-neutral-300 dark:border-neutral-500': bordered,
      'border-transparent': !bordered,
    }"
    class="form-textarea rounded-md dark:bg-neutral-700 dark:text-white dark:placeholder-neutral-400"
  ></textarea>
</template>
<script setup>
import { onMounted, onBeforeUnmount, computed, nextTick, ref } from 'vue'
import { useTextInput } from './useTextInput'
import textInputProps from './textInputProps'

const emit = defineEmits([
  'update:modelValue',
  'focus',
  'blur',
  'input',
  'change',
  'keyup',
  'keydown',
])

const props = defineProps({
  rows: [String, Number],
  cols: [String, Number],
  wrap: { type: String, default: 'soft' },
  bordered: { type: Boolean, default: true },
  resizeable: { type: Boolean, default: true },
  // When resizeable
  minHeight: { default: 60, type: [String, Number] },
  ...textInputProps,
})

const textareaRef = ref(null)

const value = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value),
})

const {
  setRangeText,
  select,
  focus,
  click,
  blur,
  keydownHandler,
  keyupHandler,
  blurHandler,
  valueWhenFocus,
} = useTextInput(textareaRef, emit, value)

let timeoutClear = null

function resizeTextarea(event) {
  event.target.style.height = 'auto'
  event.target.style.height = event.target.scrollHeight + 'px'
}

function inputHandler(e) {
  if (props.resizeable) {
    resizeTextarea(e)
  }

  emit('input', e.target.value)
}

function focusHandler(e) {
  emit('focus', e)

  if (props.resizeable) {
    resizeTextarea(e)
  }

  valueWhenFocus.value = value.value
}

onMounted(() => {
  if (props.resizeable) {
    nextTick(() => {
      timeoutClear = setTimeout(() => {
        textareaRef.value.setAttribute(
          'style',
          'height:' +
            (textareaRef.value.scrollHeight || props.minHeight) +
            'px;'
        )
      }, 400)
    })
  }
})

onBeforeUnmount(() => {
  timeoutClear && clearTimeout(timeoutClear)
})

defineExpose({ setRangeText, select, focus, click, blur })
</script>
