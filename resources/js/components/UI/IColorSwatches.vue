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
  <div class="inline-flex flex-wrap">
    <button
      v-for="color in localWatches"
      :key="color"
      type="button"
      @click="selectColor(color)"
      :class="{ 'ring ring-offset-1': color === modelValueLowerCase }"
      class="mb-2 mr-1 flex h-8 w-8 items-center justify-center rounded"
      :style="{
        backgroundColor: color,
      }"
    >
      <Icon
        v-if="color === modelValueLowerCase"
        icon="Check"
        class="h-5 w-5"
        :style="{ color: getContrast(color) }"
      />
    </button>
    <IFormInput
      class="mr-1 h-8 w-8 rounded p-0"
      :class="{ 'border-primary-500 ring-primary-500': isCustomColorSelected }"
      type="color"
      :rounded="false"
      :model-value="isCustomColorSelected ? modelValueLowerCase : null"
      @input="customColorInputEventHandler"
    />
    <button
      type="button"
      class="mb-2 mr-1 flex h-8 w-8 items-center justify-center rounded border border-neutral-300 bg-neutral-100 hover:bg-neutral-300"
      v-show="allowRemove && modelValue"
      @click="removeRequested"
    >
      <Icon icon="X" class="h-5 w-5" />
    </button>
  </div>
</template>
<script setup>
import { computed } from 'vue'
import { getContrast } from '@/utils'
import debounce from 'lodash/debounce'
import map from 'lodash/map'

const emit = defineEmits(['update:modelValue', 'input', 'remove-requested'])

const props = defineProps({
  modelValue: String,
  allowRemove: { default: true, type: Boolean },
  allowCustom: { default: true, type: Boolean },
  swatches: Array,
})

const localWatches = computed(() =>
  map(props.swatches, color => color.toLowerCase())
)

const modelValueLowerCase = computed(() =>
  !props.modelValue ? null : props.modelValue.toLowerCase()
)

const isCustomColorSelected = computed(() => {
  if (!props.modelValue || !props.allowCustom) {
    return false
  }

  return (
    localWatches.value.filter(color => color === modelValueLowerCase.value)
      .length === 0
  )
})

function selectColor(value) {
  emit('update:modelValue', value)
  emit('input', value)
}

function removeRequested() {
  selectColor(null)
  emit('remove-requested')
}

const customColorInputEventHandler = debounce(function (value) {
  selectColor(value)
}, 500)
</script>
