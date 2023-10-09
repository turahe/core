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
  <component
    :is="
      typeof option === 'object'
        ? option.swatch_color
          ? TextBackground
          : 'span'
        : 'span'
    "
    :key="key"
    v-bind="attributes"
  >
    <slot name="option" v-bind="slotProps" :optionLabel="label"></slot>

    <DeselectButton
      v-if="multiple && !disabled"
      :deselect="deselect"
      :label="label"
      :option="option"
    />
  </component>
</template>
<script setup>
import { computed } from 'vue'
import DeselectButton from './ISelectSelectedOptionDeselectButton.vue'
import TextBackground from '~/Core/resources/js/components/TextBackground.vue'

const props = defineProps([
  'option',
  'getOptionLabel',
  'getOptionKey',
  'normalizeOptionForSlot',
  'multiple',
  'searching',
  'disabled',
  'deselect',
  'simple',
])
const attributes = computed(() => {
  let attributes = {
    class: 'dark:!text-white',
  }

  if (props.option.swatch_color) {
    attributes.color = props.option.swatch_color
  }

  if (props.multiple || props.option.swatch_color) {
    attributes.class += ' rounded-md px-2 inline-flex items-center'

    if (!props.option.swatch_color) {
      attributes.class += ' bg-neutral-100 dark:bg-neutral-500'
    }

    if (props.multiple) {
      attributes.class += ' mr-2'
    }
  }

  if ((props.disabled && !props.simple) || props.searching) {
    attributes.class += ' opacity-60 dark:opacity-80'
  }

  return attributes
})

const label = computed(() => props.getOptionLabel(props.option))
const key = computed(() => props.getOptionKey(props.option))
const slotProps = computed(() => props.normalizeOptionForSlot(props.option))
</script>
